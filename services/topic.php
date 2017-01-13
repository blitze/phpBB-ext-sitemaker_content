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
	/** @var \phpbb\config\db */
	protected $config;

	/** @var \phpbb\content_visibility */
	protected $content_visibility;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\user */
	protected $user;

	/* @var \blitze\content\services\helper */
	protected $helper;

	/**
	 * Construct
	 *
	 * @param \phpbb\config\db						$config					Config object
	 * @param \phpbb\content_visibility				$content_visibility		Phpbb Content visibility object
	 * @param \phpbb\controller\helper				$controller_helper		Controller Helper object
	 * @param \phpbb\event\dispatcher_interface		$phpbb_dispatcher		Event dispatcher object
	 * @param \phpbb\language\language				$language				Language object
	 * @param \phpbb\user							$user					User object
	 * @param \blitze\content\services\helper		$helper					Content helper object
	 */
	public function __construct(\phpbb\config\db $config, \phpbb\content_visibility $content_visibility, \phpbb\controller\helper $controller_helper, \phpbb\event\dispatcher_interface $phpbb_dispatcher, \phpbb\language\language $language, \phpbb\user $user, \blitze\content\services\helper $helper)
	{
		$this->config = $config;
		$this->content_visibility = $content_visibility;
		$this->controller_helper = $controller_helper;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->language = $language;
		$this->user = $user;
		$this->helper = $helper;
	}

	/**
	 * @param string $type
	 * @param array $topic_data
	 * @param array $topic_tracking_info
	 * @return array
	 */
	public function get_min_topic_info($type, array $topic_data, array $topic_tracking_info)
	{
		$topic_id = $topic_data['topic_id'];
		$post_unread = (isset($topic_tracking_info[$topic_id]) && $topic_data['topic_last_post_time'] > $topic_tracking_info[$topic_id]) ? true : false;

		$route_params = array(
			'type'		=> $type,
			'topic_id'	=> $topic_id,
			'slug'		=> $topic_data['topic_slug']
		);

		return array(
			'TOPIC_ID'			=> $topic_data['topic_id'],
			'TOPIC_VIEWS'		=> $topic_data['topic_views'],
			'TOPIC_TITLE'		=> censor_text($topic_data['topic_title']),
			'TOPIC_DATE'		=> $this->user->format_date($topic_data['topic_time']),
			'TOPIC_COMMENTS'	=> $this->content_visibility->get_count('topic_posts', $topic_data, $topic_data['forum_id']) - 1,
			'TOPIC_URL'			=> $this->controller_helper->route('blitze_content_show', $route_params),
			'MINI_POST'			=> ($post_unread) ? $this->user->img('icon_post_target_unread', 'UNREAD_POST') : $this->user->img('icon_post_target', 'POST'),
			'S_UNREAD_POST'		=> $post_unread,
			'S_REQ_MOD_INPUT'	=> $topic_data['req_mod_input'],
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
	public function get_summary_template_data($type, array $topic_data, array $post_data, array $users_cache, array $attachments, array $topic_tracking_info, &$update_count)
	{
		$tpl_data = array_merge(
			array_change_key_case($users_cache[$post_data['poster_id']], CASE_UPPER),
			$this->get_min_topic_info($type, $topic_data, $topic_tracking_info),
			array(
				'POST_ID'				=> $post_data['post_id'],
				'POSTER_ID'				=> $post_data['poster_id'],
				'MESSAGE'				=> $this->get_parsed_text($post_data, $attachments, $update_count),

				'S_TOPIC_TYPE'			=> $topic_data['topic_type'],
				'S_TOPIC_UNAPPROVED'	=> $this->is_pending_approval($topic_data['topic_visibility']),
				'S_TOPIC_REPORTED'		=> $this->helper->topic_is_reported($topic_data['forum_id'], $topic_data['topic_moved_id'], $topic_data['topic_reported']),
				'S_TOPIC_DELETED'		=> $topic_data['topic_visibility'] === ITEM_DELETED,
			)
		);

		/**
		 * Event to modify template data
		 *
		 * @event blitze.content.modify_template_data
		 * @var	array	tpl_data	Array containing template data
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
	 * @param string $mode
	 * @return array
	 */
	public function get_detail_template_data($type, array $topic_data, array $post_data, array $users_cache,  array $attachments, array $topic_tracking_info, &$update_count, $mode = '')
	{
		$viewtopic_url = '';
		return array_merge(
			$this->get_summary_template_data($type, $topic_data, $post_data, $users_cache, $attachments, $topic_tracking_info, $update_count),
			$this->get_attachments($post_data['post_id'], $attachments),
			$this->show_delete_reason($post_data, $users_cache),
			$this->show_edit_reason($post_data, $users_cache),
			array(
				'S_POST_DELETED'		=> ($post_data['post_visibility'] == ITEM_DELETED) ? true : false,
				'S_DISPLAY_NOTICE'		=> $this->helper->display_attachments_notice($topic_data['forum_id'], $post_data['post_attachment']),
				'U_EDIT'				=> $this->helper->edit_post($topic_data, $post_data, $mode),
				'U_INFO'				=> $this->helper->mcp_info($type, $topic_data['forum_id'], $topic_data['topic_id']),
				'U_DELETE'				=> $this->helper->delete_post($topic_data, $post_data),
				'U_APPROVE_ACTION'		=> $this->helper->mcp_approve_action($topic_data['forum_id'], $post_data['post_id'], $viewtopic_url),
				'U_REPORT'				=> $this->helper->report_topic($topic_data['forum_id'], $post_data['post_id']),
				'U_MCP_REPORT'			=> $this->helper->mcp_report($topic_data['forum_id'], $post_data['post_id']),
				'U_MCP_APPROVE'			=> $this->helper->mcp_approve($topic_data['forum_id'], $post_data['post_id']),
				'U_MCP_RESTORE'			=> $this->helper->mcp_restore($topic_data['forum_id'], $post_data['post_id'], $topic_data['topic_visibility']),
				'U_MINI_POST'			=> $this->helper->mini_post($post_data['post_id']),
				'U_NOTES'				=> $this->helper->mcp_notes($post_data['post_id']),
				'U_WARN'				=> $this->helper->mcp_warn($topic_data['forum_id'], $post_data['post_id'], $post_data['poster_id']),
			)
		);
	}

	/**
	 * @param array $post_data
	 * @param array $attachments
	 * @param array $update_count
	 * @return array
	 */
	protected function get_parsed_text(array $post_data, array &$attachments, array &$update_count)
	{
		$post_id = $post_data['post_id'];
		$parse_flags = ($post_data['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
		$post_data['post_text'] = generate_text_for_display($post_data['post_text'], $post_data['bbcode_uid'], $post_data['bbcode_bitfield'], $parse_flags, true);

		if (!empty($attachments[$post_id]))
		{
			parse_attachments($post_data['forum_id'], $post_data['post_text'], $attachments[$post_id], $update_count);
		}

		return $post_data['post_text'];
	}

	/**
	 * @param int $topic_visibility
	 * @return bool
	 */
	protected function is_pending_approval($topic_visibility)
	{
		return ($topic_visibility == ITEM_UNAPPROVED || $topic_visibility == ITEM_REAPPROVE) ? true : false;
	}

	/**
	 * @param int $post_id
	 * @param array $attachments
	 * @return array
	 */
	protected function get_attachments($post_id, array $attachments)
	{
		return array(
			'ATTACHMENTS'	=> isset($attachments[$post_id]) ? $attachments[$post_id] : '',
		);
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
		if ($row['post_visibility'] === ITEM_DELETED && $row['post_delete_user'])
		{
			$display_postername	= $users_cache[$row['poster_id']]['username_full'];
			$display_username	= $users_cache[$row['post_delete_user']]['username_full'];

			if ($row['post_delete_reason'])
			{
				$l_deleted_message = $this->language->lang('POST_DELETED_BY_REASON', $display_postername, $display_username, $this->user->format_date($row['post_delete_time'], false, true), $row['post_delete_reason']);
			}
			else
			{
				$l_deleted_message = $this->language->lang('POST_DELETED_BY', $display_postername, $display_username, $this->user->format_date($row['post_delete_time'], false, true));
			}

			$l_deleted_by = $this->language->lang('DELETED_INFORMATION', $display_username, $this->user->format_date($row['post_delete_time'], false, true));
			$delete_reason = $row['post_delete_reason'];
		}

		return array(
			'DELETED_MESSAGE'			=> $l_deleted_by,
			'DELETE_REASON'				=> $delete_reason,
			'L_POST_DELETED_MESSAGE'	=> $l_deleted_message
		);
	}
}
