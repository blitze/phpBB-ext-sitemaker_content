<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services;

class helper
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\db */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth		$auth				Auth object
	 * @param \phpbb\config\db		$config				Config object
	 * @param \phpbb\user			$user				User object
	 * @param string				$phpbb_root_path	Path to the phpbb includes directory.
	 * @param string				$php_ext			php file extension
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\db $config, \phpbb\user $user, $phpbb_root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @param int $forum_id
	 * @param int $post_attachment
	 * @return bool
	 */
	public function display_attachments_notice($forum_id, $post_attachment)
	{
		return (!$this->auth->acl_get('f_download', $forum_id) && $post_attachment);
	}

	/**
	 * @param int $forum_id
	 * @param int $topic_moved_id
	 * @param int $topic_reported
	 * @return bool
	 */
	public function topic_is_reported($forum_id, $topic_moved_id, $topic_reported)
	{
		return ($topic_reported && !$topic_moved_id && $this->auth->acl_get('m_report', $forum_id)) ? true : false;
	}

	/**
	 * @param int $post_time
	 * @param string $cfg_prop
	 * @return bool
	 */
	protected function topic_is_still_editable($post_time, $cfg_prop = 'edit_time')
	{
		return !($this->config[$cfg_prop] && $post_time <= time() - ($this->config[$cfg_prop] * 60));
	}

	/**
	 * @param int $topic_status
	 * @param bool $post_edit_locked
	 * @return bool
	 */
	protected function topic_is_locked($topic_status, $post_edit_locked)
	{
		return $topic_status === ITEM_LOCKED || $post_edit_locked;
	}

	/**
	 * @param int $topic_status
	 * @param int $post_time
	 * @param bool $post_edit_locked
	 * @return bool
	 */
	protected function is_editable_topic($topic_status, $post_time, $post_edit_locked)
	{
		return (
			$this->topic_is_still_editable($post_time) &&
			!$this->topic_is_locked($topic_status, $post_edit_locked)
		);
	}

	/**
	 * @param int $poster_id
	 * @param string $cp_class
	 * @return string
	 */
	protected function get_cp_class($poster_id, $cp_class = '')
	{
		return (!$cp_class) ? (($this->user->data['user_id'] === $poster_id) ? 'ucp' : 'mcp') : $cp_class;
	}

	/**
	 * @param int $forum_id
	 * @param int $poster_id
	 * @return bool
	 */
	protected function ucp_can_edit_topic($forum_id, $poster_id)
	{
		return $this->auth->acl_get('f_edit', $forum_id) && $this->user->data['user_id'] === $poster_id;
	}

	/**
	 * @param int $forum_id
	 * @return bool
	 */
	protected function mcp_can_edit_topic($forum_id)
	{
		return $this->auth->acl_get('m_edit', $forum_id);
	}

	/**
	 * @param array $topic_data
	 * @param array $post_data
	 * @param string $cp_class
	 * @return string
	 */
	public function edit_post(array $topic_data, array $post_data, $cp_class = '')
	{
		$cp_class = $this->get_cp_class($post_data['poster_id'], $cp_class);
		$callable = $cp_class . '_can_edit_topic';
		$edit_url = '';

		if ($this->user->data['is_registered'] &&
			$this->{$callable}($topic_data['forum_id'], $post_data['poster_id']) &&
			$this->is_editable_topic($topic_data['topic_status'], $post_data['post_time'], $post_data['post_edit_locked'])
		)
		{
			$edit_url = append_sid("{$this->phpbb_root_path}posting.$this->php_ext", "mode=edit&amp;f={$topic_data['forum_id']}&amp;t={$topic_data['topic_id']}&amp;p={$topic_data['topic_first_post_id']}&amp;cp=$cp_class");
		}

		return $edit_url;
	}

	/**
	 * @param array $topic_data
	 * @param array $post_data
	 * @return string
	 */
	protected function topic_is_deletable(array $topic_data, array $post_data)
	{
		return (
			$topic_data['topic_last_post_id'] === $post_data['post_id'] &&
			$this->topic_is_still_editable($post_data['post_time'], 'delete_time') &&
			// we do not want to allow removal of the last post if a moderator locked it!
			!$this->topic_is_locked($topic_data['topic_status'], $post_data['post_edit_locked'])
		);
	}

	/**
	 * @param int $forum_id
	 * @param int $post_visibility
	 * @return string
	 */
	protected function user_can_soft_delete_topic($forum_id, $post_visibility)
	{
		return ($this->auth->acl_get('f_softdelete', $forum_id) && $post_visibility !== ITEM_DELETED);
	}

	/**
	 * @param int $forum_id
	 * @param int $post_visibility
	 * @return string
	 */
	protected function ucp_can_delete_topic($forum_id, $post_visibility)
	{
		return ($this->auth->acl_get('f_delete', $forum_id) || $this->user_can_soft_delete_topic($forum_id, $post_visibility)) ? true : false;
	}

	/**
	 * @param int $forum_id
	 * @param int $post_visibility
	 * @return string
	 */
	protected function mcp_can_delete_topic($forum_id, $post_visibility)
	{
		return ($this->auth->acl_get('m_delete', $forum_id) || ($this->auth->acl_get('m_softdelete', $forum_id) && $post_visibility !== ITEM_DELETED)) ? true : false;
	}

	/**
	 * @param array $topic_data
	 * @param array $post_data
	 * @return string
	 */
	public function delete_post(array $topic_data, array $post_data)
	{
		$cp_class = $this->get_cp_class($post_data['poster_id']);
		$callable = $cp_class . '_can_delete_topic';
		$u_delete = '';

		if ($this->user->data['is_registered'] &&
			$this->topic_is_deletable($topic_data, $post_data) &&
			$this->{$callable}($topic_data['forum_id'], $post_data['post_visibility'])
		)
		{
			$u_delete = append_sid("{$this->phpbb_root_path}posting.$this->php_ext", "mode=soft_delete&amp;f={$topic_data['forum_id']}&amp;p={$post_data['post_id']}");
		}
		return $u_delete;
	}

	/**
	 * @param int $post_id
	 * @return string
	 */
	public function mini_post($post_id)
	{
		return append_sid("{$this->phpbb_root_path}viewtopic.$this->php_ext", 'p=' . $post_id) . '#p' . $post_id;
	}

	/**
	 * @param int $forum_id
	 * @param int $post_id
	 * @return string
	 */
	public function report_topic($forum_id, $post_id)
	{
		return ($this->auth->acl_get('f_report', $forum_id)) ? append_sid("{$this->phpbb_root_path}report.$this->php_ext", 'f=' . $forum_id . '&amp;p=' . $post_id) : '';
	}

	/**
	 * @param string $type
	 * @param int $forum_id
	 * @param int $topic_id
	 * @return string
	 */
	public function mcp_info($type, $forum_id, $topic_id)
	{
		return ($this->auth->acl_get('m_info', $forum_id)) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", "i=-blitze-content-mcp-content_module&amp;mode=content&amp;do=view&amp;type=$type&amp;t=$topic_id", true, $this->user->session_id) : '';
	}

	/**
	 * @param int $forum_id
	 * @param int $post_id
	 * @return string
	 */
	public function mcp_report($forum_id, $post_id)
	{
		return ($this->auth->acl_get('m_report', $forum_id)) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=reports&amp;mode=report_details&amp;f=' . $forum_id . '&amp;p=' . $post_id, true, $this->user->session_id) : '';
	}

	/**
	 * @param int $forum_id
	 * @param int $post_id
	 * @return string
	 */
	public function mcp_approve($forum_id, $post_id)
	{
		return ($this->auth->acl_get('m_approve', $forum_id)) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=queue&amp;mode=approve_details&amp;f=' . $forum_id . '&amp;p=' . $post_id, true, $this->user->session_id) : '';
	}

	/**
	 * @param int $forum_id
	 * @param int $post_id
	 * @param string $viewtopic_url
	 * @return string
	 */
	public function mcp_approve_action($forum_id, $post_id, $viewtopic_url)
	{
		return append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", "i=queue&amp;p=$post_id&amp;f=$forum_id&amp;redirect=" . urlencode(str_replace('&amp;', '&', $viewtopic_url . '&amp;p=' . $post_id . '#p' . $post_id)));
	}

	/**
	 * @param int $forum_id
	 * @param int $post_id
	 * @param int $topic_visibility
	 * @return string
	 */
	public function mcp_restore($forum_id, $post_id, $topic_visibility)
	{
		return ($this->auth->acl_get('m_approve', $forum_id)) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=queue&amp;mode=' . (($topic_visibility !== ITEM_DELETED) ? 'deleted_posts' : 'deleted_topics') . '&amp;f=' . $forum_id . '&amp;p=' . $post_id, true, $this->user->session_id) : '';
	}

	/**
	 * @param int $poster_id
	 * @return string
	 */
	public function mcp_notes($poster_id)
	{
		return ($this->auth->acl_getf_global('m_')) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=notes&amp;mode=user_notes&amp;u=' . $poster_id, true, $this->user->session_id) : '';
	}

	/**
	 * @param int $forum_id
	 * @param int $post_id
	 * @param int $poster_id
	 * @return string
	 */
	public function mcp_warn($forum_id, $post_id, $poster_id)
	{
		return ($this->auth->acl_get('m_warn') && $poster_id !== $this->user->data['user_id'] && $poster_id !== ANONYMOUS) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=warn&amp;mode=warn_post&amp;f=' . $forum_id . '&amp;p=' . $post_id, true, $this->user->session_id) : '';
	}
}
