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

	/** @var \primetime\content\services\form */
	protected $form;

	/** @var \primetime\core\services\forum\query */
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
	 * @param \primetime\content\services\form			$form				Form object
	 * @param \primetime\core\services\forum\query		$forum				Forum object
	 * @param string									$root_path			Path to the phpbb includes directory.
	 * @param string									$php_ext			php file extension
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\db $config, \phpbb\content_visibility $content_visibility, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\pagination $pagination, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\user $user, \primetime\content\services\form $form, \primetime\core\services\forum\query $forum, $root_path, $php_ext)
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
		$action = $this->request->variable('action', 'reply');
		$post_id = $this->request->variable('p', 0);

		$topic_id = (int) $topic_data['topic_id'];
		$forum_id = (int) $topic_data['forum_id'];

		$start = ($page - 1) * $this->config['posts_per_page'];
		$total_topics = $this->count($topic_data);
		$current_page = generate_board_url() . '/' . ltrim(build_url(array('p', 'action')), './../');

		if ($this->auth->acl_get('f_reply', $forum_id))
		{
			$this->post($content_type, rtrim($current_page, '?'), $action, $topic_data, $post_id);
		}

		if (!$total_topics)
		{
			return;
		}

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

		$start = $this->pagination->validate_start($start, $this->config['posts_per_page'], $total_topics) + 1;

		$this->pagination->generate_template_pagination(
			array(
				'routes' => array(
					'primetime_content_show',
					'primetime_content_show_comments_page',
				),
				'params' => array(
					'type'			=> $content_type,
					'topic_id'		=> $topic_data['topic_id'],
					'slug'			=> $topic_data['topic_slug'],
					'#'				=> 'comments'
				),
			),
			'pagination', 'page', $total_topics, $this->config['posts_per_page'], $start);

		$posts_data = $this->forum->get_post_data(false, array(), $this->config['posts_per_page'], $start);
		$topic_tracking_info = $this->forum->get_topic_tracking_info();
		$users_cache = $this->forum->get_posters_info();

		if (!sizeof($posts_data))
		{
			return;
		}

		$posts_data = array_values(array_shift($posts_data));

		for ($i = 0, $size = sizeof($posts_data); $i < $size; $i++)
		{
			$row = $posts_data[$i];

			$post_id = (int) $row['post_id'];
			$poster_id = (int) $row['poster_id'];

			$l_edited_by = '';
			if (($row['post_edit_count'] && $this->config['display_last_edited']) || $row['post_edit_reason'])
			{
				$l_edited_by = $this->user->lang('EDITED_TIMES_TOTAL', (int) $row['post_edit_count'], $users_cache[$poster_id]['author_full'], $this->user->format_date($row['post_edit_time'], false, true));
			}

			$s_cannot_edit = !$this->auth->acl_get('f_edit', $forum_id) || $this->user->data['user_id'] != $poster_id;
			$s_cannot_edit_time = $this->config['edit_time'] && $row['post_time'] <= time() - ($this->config['edit_time'] * 60);
			$s_cannot_edit_locked = $topic_data['topic_status'] == ITEM_LOCKED || $row['post_edit_locked'];

			$s_cannot_delete = $this->user->data['user_id'] != $poster_id || (
				!$this->auth->acl_get('f_delete', $forum_id) &&
				(!$this->auth->acl_get('f_softdelete', $forum_id) || $row['post_visibility'] == ITEM_DELETED)
			);
			$s_cannot_delete_lastpost = $topic_data['topic_last_post_id'] != $row['post_id'];
			$s_cannot_delete_time = $this->config['delete_time'] && $row['post_time'] <= time() - ($this->config['delete_time'] * 60);
			// we do not want to allow removal of the last post if a moderator locked it!
			$s_cannot_delete_locked = $topic_data['topic_status'] == ITEM_LOCKED || $row['post_edit_locked'];

			$edit_url = '';
			if ($this->user->data['is_registered'] && ($this->auth->acl_get('m_edit', $forum_id) || (
				!$s_cannot_edit &&
				!$s_cannot_edit_time &&
				!$s_cannot_edit_locked)))
			{
				$edit_url = reapply_sid($current_page . "action=edit&amp;p=$post_id#postform");
			}

			$u_delete_topic = '';
			if ($this->user->data['is_registered'] && (
				($this->auth->acl_get('m_delete', $forum_id) || ($this->auth->acl_get('m_softdelete', $forum_id) && $row['post_visibility'] != ITEM_DELETED)) ||
				(!$s_cannot_delete && !$s_cannot_delete_lastpost && !$s_cannot_delete_time && !$s_cannot_delete_locked)
			))
			{
				$u_delete_topic = append_sid("{$this->root_path}posting." . $this->php_ext, "mode=delete&amp;f=$forum_id&amp;p=" . $row['post_id']);
			}

			// Deleting information
			$l_deleted_by = '';
			if ($row['post_visibility'] == ITEM_DELETED && $row['post_delete_user'])
			{
				// User having deleted the post also being the post author?
				if (!$row['post_delete_user'] || $row['post_delete_user'] == $row['poster_id'])
				{
					$display_username = get_username_string('full', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']);
				}
				else
				{
					$sql = 'SELECT user_id, username, user_colour
							FROM ' . USERS_TABLE . '
							WHERE user_id = ' . (int) $row['post_delete_user'];
					$result = $this->db->sql_query($sql);
					$user_delete_row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);
					$display_username = get_username_string('full', $row['post_delete_user'], $user_delete_row['username'], $user_delete_row['user_colour']);
				}

				$this->user->add_lang('viewtopic');
				$l_deleted_by = $this->user->lang('DELETED_INFORMATION', $display_username, $this->user->format_date($row['post_delete_time'], false, true));
			}

			$parse_flags = ($row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
			$row['post_text'] = generate_text_for_display($row['post_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $parse_flags, true);
			$post_unread = (isset($topic_tracking_info[$forum_id][$topic_id]) && $row['post_time'] > $topic_tracking_info[$forum_id][$topic_id]) ? true : false;

			$this->template->assign_block_vars('comment', array(
				'POST_ID'			=> $post_id,
				'POST_AUTHOR_FULL'	=> $users_cache[$poster_id]['username_full'],
				'POST_AUTHOR'		=> $users_cache[$poster_id]['username'],
				'POSTER_AVATAR'		=> $users_cache[$poster_id]['avatar'],
				'U_POST_AUTHOR'		=> $users_cache[$poster_id]['user_profile'],

				'POST_DATE'			=> $this->user->format_date($row['post_time']),
				'MESSAGE'			=> $row['post_text'],
				'MINI_POST_IMG'		=> ($post_unread) ? $this->user->img('icon_post_target_unread', 'UNREAD_POST') : $this->user->img('icon_post_target', 'POST'),

				'S_POST_DELETED'		=> ($row['post_visibility'] == ITEM_DELETED) ? true : false,
				'S_POST_REPORTED'		=> ($row['post_reported'] && $this->auth->acl_get('m_report', $forum_id)),
				'S_POST_UNAPPROVED'		=> (($row['post_visibility'] == ITEM_UNAPPROVED || $row['post_visibility'] == ITEM_REAPPROVE) && $this->auth->acl_get('m_approve', $forum_id)),
				'S_POST_DELETED'		=> ($row['post_visibility'] == ITEM_DELETED && $this->auth->acl_get('m_approve', $forum_id)),

				'EDITED_MESSAGE'		=> $l_edited_by,
				'EDIT_REASON'			=> $row['post_edit_reason'],
				'DELETED_MESSAGE'		=> $l_deleted_by,
				'DELETE_REASON'			=> $row['post_delete_reason'],

				'U_INFO'				=> ($this->auth->acl_get('m_info', $forum_id)) ? append_sid("{$this->root_path}mcp.$this->php_ext", "i=main&amp;mode=post_details&amp;f=$forum_id&amp;p=" . $row['post_id'], true, $this->user->session_id) : '',
				'U_MCP_REPORT'			=> ($this->auth->acl_get('m_report', $forum_id)) ? append_sid("{$this->root_path}mcp.$this->php_ext", 'i=reports&amp;mode=report_details&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $this->user->session_id) : '',
				'U_MCP_APPROVE'			=> ($this->auth->acl_get('m_approve', $forum_id)) ? append_sid("{$this->root_path}mcp.$this->php_ext", 'i=queue&amp;mode=approve_details&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $this->user->session_id) : '',
				'U_MCP_RESTORE'			=> ($this->auth->acl_get('m_approve', $forum_id)) ? append_sid("{$this->root_path}mcp.$this->php_ext", 'i=queue&amp;mode=' . (($topic_data['topic_visibility'] != ITEM_DELETED) ? 'deleted_posts' : 'deleted_topics') . '&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $this->user->session_id) : '',
				'U_NOTES'				=> ($this->auth->acl_getf_global('m_')) ? append_sid("{$this->root_path}mcp.$this->php_ext", 'i=notes&amp;mode=user_notes&amp;u=' . $poster_id, true, $this->user->session_id) : '',
				'U_WARN'				=> ($this->auth->acl_get('m_warn') && $poster_id != $this->user->data['user_id'] && $poster_id != ANONYMOUS) ? append_sid("{$this->root_path}mcp.$this->php_ext", 'i=warn&amp;mode=warn_post&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $this->user->session_id) : '',

				'U_EDIT'				=> $edit_url,
				'U_DELETE'				=> $u_delete_topic,
			));
		}

		$this->template->assign_var('TOPIC_COMMENTS', $total_topics);
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
}
