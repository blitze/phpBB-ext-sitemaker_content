<?php

/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services;

class topic
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/* @var \blitze\content\services\helper */
	protected $helper;

	/* @var string */
	protected $short_date_format;

	/* @var string */
	protected $long_date_format;

	/**
	 * Construct
	 *
	 * @param \phpbb\config\config					$config					Config object
	 * @param \phpbb\controller\helper				$controller_helper		Controller Helper object
	 * @param \phpbb\event\dispatcher_interface		$phpbb_dispatcher		Event dispatcher object
	 * @param \phpbb\language\language				$language				Language object
	 * @param \phpbb\template\template				$template				Template object
	 * @param \phpbb\user							$user					User object
	 * @param \blitze\content\services\helper		$helper					Content helper object
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $controller_helper, \phpbb\event\dispatcher_interface $phpbb_dispatcher, \phpbb\language\language $language, \phpbb\template\template $template, \phpbb\user $user, \blitze\content\services\helper $helper)
	{
		$this->config = $config;
		$this->controller_helper = $controller_helper;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->language = $language;
		$this->template = $template;
		$this->user = $user;
		$this->helper = $helper;
	}

	/**
	 * @param string $type
	 * @param array $topic_data
	 * @param array $topic_tracking_info
	 * @return array
	 */
	public function get_min_topic_info($type, array &$topic_data, array $topic_tracking_info)
	{
		$topic_id = $topic_data['topic_id'];
		$post_unread = (isset($topic_tracking_info[$topic_id]) && $topic_data['topic_last_post_time'] > $topic_tracking_info[$topic_id]) ? true : false;
		$topic_data['topic_url'] = $this->get_topic_url($type, $topic_data);

		$short_date_format = $this->language->lang('CONTENT_SHORT_DATE_FORMAT');
		$long_date_format = $this->language->lang('CONTENT_LONG_DATE_FORMAT');

		return array(
			'TOPIC_ID'			=> $topic_data['topic_id'],
			'TOPIC_VIEWS'		=> $topic_data['topic_views'],
			'TOPIC_TITLE'		=> censor_text($topic_data['topic_title']),
			'TOPIC_DATE'		=> $this->user->format_date($topic_data['topic_time'], $short_date_format),
			'TOPIC_DATE_LONG'	=> $this->user->format_date($topic_data['topic_time'], $long_date_format),
			'TOPIC_UNIX_TIME'	=> $topic_data['topic_time'],
			'TOPIC_URL'			=> $topic_data['topic_url'],
			'MINI_POST'			=> ($post_unread) ? $this->user->img('icon_post_target_unread', 'UNREAD_POST') : $this->user->img('icon_post_target', 'POST'),
			'S_REQ_MOD_INPUT'	=> $topic_data['req_mod_input'],
			'S_UNREAD_POST'		=> $post_unread,
		);
	}

	/**
	 * @param string $type
	 * @param array $topic_data
	 * @param array $post_data
	 * @param array $users_cache
	 * @param array $attachments
	 * @param array $topic_tracking_info
	 * @param array $update_count
	 * @return array
	 */
	public function get_summary_template_data($type, array &$topic_data, array $post_data, array $users_cache, array &$attachments, array $topic_tracking_info, array &$update_count)
	{
		$tpl_data = array_merge(
			(empty($topic_data['topic_url'])) ? $this->get_min_topic_info($type, $topic_data, $topic_tracking_info) : array(),
			array(
				'POST_ID'				=> $post_data['post_id'],
				'POSTER_ID'				=> $post_data['poster_id'],
				'UPDATED'				=> max($post_data['post_edit_time'], $topic_data['topic_time']),
				'MESSAGE'				=> $this->get_parsed_text($post_data, $attachments, $update_count),

				'S_TOPIC_TYPE'			=> $topic_data['topic_type'],
				'S_HAS_ATTACHMENTS'		=> $topic_data['topic_attachment'],
				'S_HAS_POLL'			=> (bool) $topic_data['poll_start'],
			),
			$this->get_topic_status_data($type, $topic_data, $post_data),
			array_change_key_case($users_cache[$post_data['poster_id']], CASE_UPPER)
		);

		/**
		 * Event to modify template data
		 *
		 * @event blitze.content.modify_template_data
		 * @var	array	tpl_data	Array containing template data
		 * @since 3.0.0-RC4
		 */
		$vars = array('tpl_data');
		extract($this->phpbb_dispatcher->trigger_event('blitze.content.modify_template_data', compact($vars)));

		return $tpl_data;
	}

	/**
	 * @param string $type
	 * @param array $topic_data
	 * @param array $post_data
	 * @param array $users_cache
	 * @param array $attachments
	 * @param array $topic_tracking_info
	 * @param array $update_count
	 * @param string $redirect_url
	 * @return array
	 */
	public function get_detail_template_data($type, array &$topic_data, array $post_data, array $users_cache, array &$attachments, array $topic_tracking_info, array &$update_count, $redirect_url = '')
	{
		return array_merge(
			$this->get_summary_template_data($type, $topic_data, $post_data, $users_cache, $attachments, $topic_tracking_info, $update_count),
			$this->show_delete_reason($post_data, $users_cache),
			$this->show_edit_reason($post_data, $users_cache),
			array(
				'S_DISPLAY_NOTICE'		=> $this->helper->display_attachments_notice($post_data),
				'S_DELETE_PERMANENT'	=> $this->helper->permanent_delete_allowed($post_data),
				'S_IS_LOCKED'			=> $this->helper->topic_is_locked($topic_data),

				'U_EDIT'			=> $this->helper->get_edit_url($post_data, $topic_data, $redirect_url),
				'U_QUOTE'			=> $this->helper->get_quote_url($post_data, $topic_data),
				'U_INFO'			=> $this->helper->get_info_url($post_data),
				'U_DELETE'			=> $this->helper->get_delete_url($post_data, $topic_data, $redirect_url),
				'U_REPORT'			=> $this->helper->can_report_post($post_data['forum_id']) ? $this->controller_helper->route('phpbb_report_post_controller', array('id' => $post_data['post_id'])) : '',
				'U_APPROVE_ACTION'	=> $this->helper->get_approve_url($post_data, $topic_data['topic_url']),
				'U_MCP_EDIT'		=> $this->helper->get_mcp_edit_url($post_data, $topic_data, $redirect_url),
				'U_MCP_RESTORE'		=> $this->helper->get_mcp_restore_url($post_data, $topic_data),
				'U_NOTES'			=> $this->helper->get_notes_url($post_data),
				'U_WARN'			=> $this->helper->get_warning_url($post_data),
			)
		);
	}

	/**
	 * @param array $topic_data
	 * @return array
	 */
	public function get_topic_tools_data(array $topic_data)
	{
		return array_merge(
			array(
				'U_PRINT_TOPIC'		=> $this->helper->get_print_topic_url($topic_data),
				'U_EMAIL_TOPIC'		=> $this->helper->get_email_topic_url($topic_data),
			),
			$this->get_watch_status_data($topic_data),
			$this->get_bookmark_status_data($topic_data)
		);
	}

	/**
	 * @param string $type
	 * @param array $topic_data
	 * @return string
	 */
	public function get_topic_url($type, array $topic_data)
	{
		return $this->controller_helper->route('blitze_content_show', array(
			'type'		=> $type,
			'topic_id'	=> $topic_data['topic_id'],
			'slug'		=> $topic_data['topic_slug']
		));
	}

	/**
	 * @param array $attachments
	 * @param int $post_id
	 * @param string $handle
	 * @return void
	 */
	public function show_attachments(array $attachments, $post_id, $handle = 'attachment')
	{
		if (!empty($attachments[$post_id]))
		{
			foreach ($attachments[$post_id] as $attachment)
			{
				$this->template->assign_block_vars($handle, array(
					'DISPLAY_ATTACHMENT' => $attachment
				));
			}
		}
	}

	/**
	 * @param array $post_data
	 * @param array $attachments
	 * @param array $update_count
	 * @return string
	 */
	protected function get_parsed_text(array $post_data, array &$attachments, array &$update_count)
	{
		$parse_flags = ($post_data['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
		$message = generate_text_for_display($post_data['post_text'], $post_data['bbcode_uid'], $post_data['bbcode_bitfield'], $parse_flags, true);

		if (!empty($attachments[$post_data['post_id']]))
		{
			parse_attachments($post_data['forum_id'], $message, $attachments[$post_data['post_id']], $update_count);
		}

		return $message;
	}

	/**
	 * @param string $type
	 * @param array $topic_data
	 * @param array $post_data
	 * @return array
	 */
	protected function get_topic_status_data($type, array $topic_data, array $post_data)
	{
		return array(
			'S_POST_UNAPPROVED'		=> $this->helper->post_is_unapproved($post_data),
			'S_POSTS_UNAPPROVED'	=> $this->helper->topic_has_unapproved_posts($topic_data),
			'S_TOPIC_REPORTED'		=> $this->helper->topic_is_reported($topic_data),
			'S_TOPIC_DELETED'		=> $topic_data['topic_visibility'] == ITEM_DELETED,

			'U_MINI_POST'			=> $this->get_mini_post_url($topic_data, $post_data),
			'U_MCP_REPORT'			=> $this->helper->get_mcp_report_url($post_data),
			'U_MCP_REVIEW'			=> $this->helper->get_mcp_review_url($type, $topic_data['topic_id']),
			'U_MCP_QUEUE'			=> $this->helper->get_mcp_queue_url($topic_data['topic_id']),
		);
	}

	/**
	 * @param array $topic_data
	 * @param array $post_data
	 * @return string
	 */
	protected function get_mini_post_url(array $topic_data, array $post_data)
	{
		if ($topic_data['topic_first_post_id'] === $post_data['post_id'])
		{
			return append_sid($topic_data['topic_url'], 'view=unread') . '#unread';
		}

		return append_sid($topic_data['topic_url'], 'p=' . $post_data['post_id']) . '#p' . $post_data['post_id'];
	}

	/**
	 * @param array $row
	 * @param array $users_cache
	 * @return array
	 */
	protected function show_edit_reason(array $row, array $users_cache)
	{
		$l_edited_by = $edit_reason = '';
		if (($row['post_edit_count'] && $this->config['display_last_edited']) || $row['post_edit_reason'])
		{
			$display_username	= $users_cache[$row['poster_id']]['username_full'];
			$l_edited_by = $this->language->lang('EDITED_TIMES_TOTAL', (int) $row['post_edit_count'], $display_username, $this->user->format_date($row['post_edit_time'], false, true));
			$edit_reason = $row['post_edit_reason'];
		}

		return array(
			'EDITED_MESSAGE'	=> $l_edited_by,
			'EDIT_REASON'		=> $edit_reason,
		);
	}

	/**
	 * @param array $row
	 * @param array $users_cache
	 * @return array
	 */
	protected function show_delete_reason(array $row, array $users_cache)
	{
		$l_deleted_by = $delete_reason = $l_deleted_message = '';
		$s_post_deleted = ($row['post_visibility'] == ITEM_DELETED) ? true : false;

		if ($s_post_deleted && $row['post_delete_user'])
		{
			$display_postername	= $users_cache[$row['poster_id']]['username_full'];
			$display_username	= $users_cache[$row['post_delete_user']]['username_full'];

			$l_deleted_message = $this->get_delete_message($row, $display_postername, $display_username);
			$l_deleted_by = $this->language->lang('DELETED_INFORMATION', $display_username, $this->user->format_date($row['post_delete_time'], false, true));
			$delete_reason = $row['post_delete_reason'];
		}

		return array(
			'DELETED_MESSAGE'			=> $l_deleted_by,
			'DELETE_REASON'				=> $delete_reason,
			'L_POST_DELETED_MESSAGE'	=> $l_deleted_message,
			'S_POST_DELETED'			=> $s_post_deleted,
		);
	}

	/**
	 * @param array $row
	 * @param string $display_postername
	 * @param string $display_username
	 * @return string
	 */
	protected function get_delete_message(array $row, $display_postername, $display_username)
	{
		if ($row['post_delete_reason'])
		{
			return $this->language->lang('POST_DELETED_BY_REASON', $display_postername, $display_username, $this->user->format_date($row['post_delete_time'], false, true), $row['post_delete_reason']);
		}
		else
		{
			return $this->language->lang('POST_DELETED_BY', $display_postername, $display_username, $this->user->format_date($row['post_delete_time'], false, true));
		}
	}

	/**
	 * @param array $topic_data
	 * @return array
	 */
	protected function get_watch_status_data(array $topic_data)
	{
		$s_watching_topic = array(
			'link'			=> '',
			'link_toggle'	=> '',
			'title'			=> '',
			'title_toggle'	=> '',
			'is_watching'	=> false,
		);

		if ($this->config['allow_topic_notify'])
		{
			$notify_status = (isset($topic_data['notify_status'])) ? $topic_data['notify_status'] : null;
			watch_topic_forum('topic', $s_watching_topic, $this->user->data['user_id'], $topic_data['forum_id'], $topic_data['topic_id'], $notify_status, 0, $topic_data['topic_title']);
		}

		return array(
			'U_WATCH_TOPIC'			=> $s_watching_topic['link'],
			'U_WATCH_TOPIC_TOGGLE'	=> $s_watching_topic['link_toggle'],
			'S_WATCH_TOPIC_TITLE'	=> $s_watching_topic['title'],
			'S_WATCH_TOPIC_TOGGLE'	=> $s_watching_topic['title_toggle'],
			'S_WATCHING_TOPIC'		=> $s_watching_topic['is_watching'],
		);
	}

	/**
	 * @param array $topic_data
	 * @return array
	 */
	protected function get_bookmark_status_data(array $topic_data)
	{
		$bookmarked = (bool) (isset($topic_data['bookmarked']) ? $topic_data['bookmarked'] : false);
		$state_key = (int) $bookmarked;
		$lang_keys = array(
			'toggle'	=> array('BOOKMARK_TOPIC_REMOVE', 'BOOKMARK_TOPIC'),
			'bookmark'	=> array('BOOKMARK_TOPIC', 'BOOKMARK_TOPIC_REMOVE'),
		);

		return array(
			'U_BOOKMARK_TOPIC'		=> ($this->user->data['is_registered'] && $this->config['allow_bookmarks']) ? $this->helper->get_viewtopic_url($topic_data) . '&amp;bookmark=1&amp;hash=' . generate_link_hash("topic_{$topic_data['topic_id']}") : '',
			'S_BOOKMARK_TOPIC'		=> $this->language->lang($lang_keys['bookmark'][$state_key]),
			'S_BOOKMARK_TOGGLE'		=> $this->language->lang($lang_keys['toggle'][$state_key]),
			'S_BOOKMARKED_TOPIC'	=> $bookmarked,
		);
	}
}
