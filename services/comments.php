<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services;

class comments
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\db */
	protected $config;

	/** @var \phpbb\content_visibility */
	protected $content_visibility;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \blitze\content\services\form\form */
	protected $form;

	/** @var \blitze\sitemaker\services\forum\data */
	protected $forum;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth							$auth				Auth object
	 * @param \phpbb\config\db							$config				Config object
	 * @param \phpbb\content_visibility					$content_visibility	Phpbb Content visibility object
	 * @param \phpbb\controller\helper					$helper				Helper object
	 * @param \phpbb\db\driver\driver_interface			$db					Database object
	 * @param \phpbb\pagination							$pagination			Pagination object
	 * @param \phpbb\request\request_interface			$request			Request object
	 * @param \phpbb\template\template					$template			Template object
	 * @param \phpbb\user								$user				User object
	 * @param \blitze\content\services\form\form		$form				Form object
	 * @param \blitze\sitemaker\services\forum\data		$forum				Forum Data object
	 * @param \blitze\content\services\topic			$topic				Content topic object
	 * @param string									$root_path			Path to the phpbb includes directory.
	 * @param string									$php_ext			php file extension
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\db $config, \phpbb\content_visibility $content_visibility, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\pagination $pagination, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\user $user, \blitze\content\services\form\form $form, \blitze\sitemaker\services\forum\data $forum, $root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->content_visibility = $content_visibility;
		$this->db = $db;
		$this->helper = $helper;
		$this->pagination = $pagination;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->form = $form;
		$this->forum = $forum;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Show comments for topic
	 * @param int $topic_id
	 * @param int $total_comments
	 * @param string $base_url
	 * @param array $topic_data
	 * @return void
	 */
	public function show($topic_id, $total_comments, $base_url, array $topic_data)
	{
		if ($total_comments)
		{
			$action = $this->request->variable('action', 'reply');
			$start = $this->request->variable('start', 0);
			$post_id = $this->request->variable('p', 0);

			$this->forum->query()
				->fetch_topic($topic_id)
				->build();
			$this->build_pagination($start, $topic_data['forum_id'], $topic_id, $post_id, $total_comments, $action, $base_url);

			$posts_data = $this->forum->get_post_data(false, array(), $this->config['posts_per_page'], $start, array(
				'WHERE' => 'p.post_id <> ' . (int) $topic_data['topic_first_post_id'],
			));

			$topic_tracking_info = $this->forum->get_topic_tracking_info();
			$users_cache = $this->forum->get_posters_info();

			$this->show_posts($topic_data, array_values(array_shift($posts_data)), $topic_tracking_info, $users_cache);
		}
	}

	protected function show_posts(array $topic_data, array $posts_data, array $topic_tracking_info, array $users_cache)
	{
		for ($i = 0, $size = sizeof($posts_data); $i < $size; $i++)
		{
			$attachments = $update_count = array();
		}
	}

	/**
	 * Post comments
	 */
	public function post($type, $current_page, $action, $topic_data, $post_id)
	{
		$forum_id = (int) $topic_data['forum_id'];
		$topic_id = (int) $topic_data['topic_id'];

		$message = '';
		$post_data = array(
			'forum_id'			=> $forum_id,
			'topic_id'			=> $topic_id,
			'post_id'			=> $post_id,
			'icon_id'			=> false,

			'post_edit_locked'  => 0,
			'notify_set'        => true,
			'notify'            => true,
			'post_time'         => 0,
			'forum_name'        => '',
			'enable_indexing'   => true,
		);

		if ($action == 'edit')
		{
			$result = $this->db->sql_query('SELECT * FROM ' . POSTS_TABLE . ' WHERE post_id = ' . (int) $post_id);
			$post_data = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$message = $post_data['post_text'];

			decode_message($message, $post_data['bbcode_uid']);
		}

		$this->form->create('postform', $current_page, '', 'post', $forum_id)
			->add('comment', 'textarea', array('field_value' => $message, 'field_explain' => '', 'editor' => true))
			->add('p', 'hidden', array('field_value' => $post_id))
			->add('action', 'hidden', array('field_value' => $action))
			->add('cancel', 'submit', array('field_value' => $this->user->lang['CANCEL']))
			->add('submit', 'submit', array('field_value' => $this->user->lang['POST_COMMENT']));

		$this->form->handle_request($this->request);

		if ($this->form->is_valid && $this->request->is_set_post('submit'))
		{
			$data = $this->form->get_data();
			$message = $data['comment']['field_value'];

			if ($message)
			{
				if (!function_exists('submit_post'))
				{
					include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
				}

				$poll = array();
				$uid = $bitfield = $options = '';
				$allow_bbcode	= ($this->config['allow_bbcode']) ? true : false;
				$allow_smilies	= ($this->config['allow_smilies']) ? true : false;
				$allow_urls		= ($this->config['allow_post_links']) ? true : false;

				generate_text_for_storage($message, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);

				$post_data = array_merge($post_data, array(
					'enable_bbcode'		=> $allow_bbcode,
					'enable_smilies'	=> $allow_smilies,
					'enable_urls'		=> $allow_urls,
					'enable_sig'		=> false,

					'message'			=> (string) $message,
					'message_md5'		=> md5($message),
					'bbcode_bitfield'	=> $bitfield,
					'bbcode_uid'		=> $uid,

					'post_edit_locked'  => 0,
					'topic_title'       => $topic_data['topic_title'],
				));

				submit_post($action, $topic_data['topic_title'], $this->user->data['username'], POST_NORMAL, $poll, $post_data);

				$post_id = $post_data['post_id'];
				$redirect_url = $current_page . ((strpos($current_page, '?') === false) ? '?' : '&amp;')  . "p=$post_id#p$post_id";
				$message = $this->user->lang['COMMENT_POSTED'] . '<br /><br />' . sprintf($this->user->lang['RETURN_PAGE'], '<a href="' . $redirect_url . '">', '</a>');

				meta_refresh(3, $redirect_url);
				trigger_error($message);
			}
		}

		$this->template->assign_var('POST_FORM', $this->form->get_form());
	}

	protected function build_pagination(&$start, $forum_id, $topic_id, $post_id, $total_comments, $action, $base_url)
	{
		if ($post_id && !$action)
		{
			$sql = 'SELECT post_id, post_time, post_visibility
				FROM ' . POSTS_TABLE . " p
				WHERE p.topic_id = $topic_id
					AND p.post_id = $post_id";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$sql = 'SELECT COUNT(p.post_id) AS prev_posts
				FROM ' . POSTS_TABLE . " p
				WHERE p.topic_id = $topic_id
					AND (p.post_time < {$row['post_time']} OR (p.post_time = {$row['post_time']} AND p.post_id <= {$row['post_id']}))
					AND " . $this->content_visibility->get_visibility_sql('post', $forum_id, 'p.');

			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$start = floor(($row['prev_posts'] - 2) / $this->config['posts_per_page']) * $this->config['posts_per_page'];
		}

		$start = $this->pagination->validate_start($start, $this->config['posts_per_page'], $total_comments) + 1;
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'page', $total_comments, $this->config['posts_per_page'], $start);
	}
}
