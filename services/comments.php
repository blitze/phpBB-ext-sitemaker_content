<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\services;

class comments implements comments_interface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\db */
	protected $config;

	/** @var \phpbb\content_visibility */
	protected $content_visibility;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \primetime\content\services\form\builder */
	protected $form;

	/** @var \primetime\primetime\core\forum\query */
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
	 * @param \primetime\content\services\form\builder	$form				Form object
	 * @param \primetime\primetime\core\forum\query		$forum				Forum object
	 * @param string									$root_path			Path to the phpbb includes directory.
	 * @param string									$php_ext			php file extension
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\db $config, \phpbb\content_visibility $content_visibility, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\pagination $pagination, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\user $user, \primetime\content\services\form\builder $form, \primetime\primetime\core\forum\query $forum, $root_path, $php_ext)
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
	 * Get comments count for topic
	 */
	public function count($topic_data)
	{
		return $this->content_visibility->get_count('topic_posts', $topic_data, $topic_data['forum_id']) - 1;
	}

	/**
	 * Show comments for topic
	 */
	public function show($content_type, $topic_data, $page)
	{
		$total_topics = $this->count($topic_data);
		if (!sizeof($topic_data) || !$total_topics)
		{
			return;
		}

		$post_id = $this->request->variable('p', 0);

		$topic_id = (int) $topic_data['topic_id'];
		$forum_id = (int) $topic_data['forum_id'];

		$start = ($page - 1) * $this->config['posts_per_page'];

		if ($post_id)
		{
			$sql = 'SELECT COUNT(p.post_id) AS prev_posts
				FROM ' . POSTS_TABLE . " p
				WHERE p.topic_id = $topic_id
					AND " . $this->content_visibility->get_visibility_sql('post', $forum_id, 'p.');

			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$start = floor(($row['prev_posts'] - 1) / $this->config['posts_per_page']) * $this->config['posts_per_page'];
		}

		$start = $this->pagination->validate_start($start, $this->config['posts_per_page'], $total_topics) + 1;

		$this->pagination->generate_template_pagination(
			array(
				'routes' => array(
					'primetime_content_show',
					'primetime_content_comments_page',
				),
				'params' => array(
					'type'			=> $content_type,
					'topic_id'		=> $topic_data['topic_id'],
					'slug'			=> $topic_data['topic_slug'],
				),
			),
			'pagination', 'page', $total_topics, $this->config['posts_per_page'], $start);

		$posts_data = $this->forum->get_post_data(false, array(), $this->config['posts_per_page'], $start);
		$topic_tracking_info = $this->forum->get_topic_tracking_info();
		$users_cache = $this->forum->get_posters_info();

		$posts_data = array_values(array_shift($posts_data));

		for ($i = 0, $size = sizeof($posts_data); $i < $size; $i++)
		{
			$row = $posts_data[$i];

			$post_id = (int) $row['post_id'];
			$poster_id = (int) $row['poster_id'];

			$parse_flags = ($row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
			$row['post_text'] = generate_text_for_display($row['post_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $parse_flags, true);
			$post_unread = (isset($topic_tracking_info[$forum_id][$topic_id]) && $row['post_time'] > $topic_tracking_info[$forum_id][$topic_id]) ? true : false;

			$this->template->assign_block_vars('comment', array(
				'POST_ID'			=> $post_id,
				'POST_AUTHOR_FULL'	=> $users_cache[$poster_id]['author_full'],
				'POST_AUTHOR'		=> $users_cache[$poster_id]['author_username'],
				'POSTER_AVATAR'		=> $users_cache[$poster_id]['avatar'],
				'U_POST_AUTHOR'		=> $users_cache[$poster_id]['author_profile'],

				'POST_DATE'			=> $this->user->format_date($row['post_time']),
				'MESSAGE'			=> $row['post_text'],
			));
		}

		if ($this->auth->acl_get('f_reply', $forum_id))
		{
			$this->post($content_type, $topic_data);
		}

		$this->template->assign_var('TOPIC_COMMENTS', $total_topics);
	}

	/**
	 * Post comments
	 */
	public function post($type, $topic_data)
	{
		$action = $this->request->variable('action', 'reply');
		$post_id = $this->request->variable('p', 0);

		$forum_id = (int) $topic_data['forum_id'];
		$topic_id = (int) $topic_data['topic_id'];

		$current_page = build_url('action');
		$form_action = $current_page . 'action=' . $action;
		$message = '';

		$post_data = array(
			'forum_id'			=> $forum_id,
			'topic_id'			=> $topic_id,
			'post_id'			=> 0,
			'icon_id'			=> false,

			'post_edit_locked'  => 0,
			'notify_set'        => true,
			'notify'            => true,
			'post_time'         => 0,
			'forum_name'        => '',
			'enable_indexing'   => true,
		);

		if ($post_id && $action == 'edit')
		{
			$result = $this->db->sql_query('SELECT * FROM ' . POSTS_TABLE . ' WHERE post_id = ' . (int) $post_id);
			$post_data = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$message = $post_data['post_text'];
			$form_action .= '&amp;p=' . $post_id;

			decode_message($message, $post_data['bbcode_uid']);
		}

		$this->form->create('postform', $form_action, '', 'post', $forum_id)
			->add('comment', 'textarea', array('field_value' => $message, 'field_explain' => '', 'editor' => true))
			->add('submit', 'submit', array('field_value' => $this->user->lang['POST_COMMENT']));

		$this->form->handle_request($this->request);

		if ($this->form->is_valid)
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
				$redirect_url = $current_page . "p=$post_id#p$post_id";
				$message = $this->user->lang['COMMENT_POSTED'] . '<br /><br />' . sprintf($this->user->lang['RETURN_PAGE'], '<a href="' . $redirect_url . '">', '</a>');
				$this->helper->error($message);
				meta_refresh(3, $redirect_url);
			}
		}

		$this->template->assign_var('POST_FORM', $this->form->get_form());
	}
}
