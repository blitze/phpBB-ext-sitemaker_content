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
	 * @param array $post_data
	 * @param array $topic_data
	 * @param string $cp_mode
	 * @return string
	 */
	public function get_edit_url(array $post_data, array $topic_data, $cp_mode = '')
	{
		$cp_param = $this->get_cp_param($post_data, $topic_data, $cp_mode);
		return ($this->edit_allowed($post_data, $topic_data)) ? append_sid("{$this->phpbb_root_path}posting.$this->php_ext", "mode=edit&amp;f={$topic_data['forum_id']}&amp;p={$post_data['post_id']}" . $cp_param) : '';
	}

	/**
	 * @param array $post_data
	 * @param array $topic_data
	 * @param string $cp_mode
	 * @return string
	 */
	public function get_delete_url(array $post_data, array $topic_data, $cp_mode = '')
	{
		$cp_param = $this->get_cp_param($post_data, $topic_data, $cp_mode);
		return ($this->delete_allowed($post_data, $topic_data)) ? append_sid("{$this->phpbb_root_path}posting.$this->php_ext", 'mode=' . (($this->softdelete_allowed($topic_data, $post_data)) ? 'soft_delete' : 'delete') . "&amp;f={$post_data['forum_id']}&amp;p={$post_data['post_id']}" . $cp_param) : '';
	}

	/**
	 * @param array $post_data
	 * @param array $topic_data
	 * @return string
	 */
	public function get_quote_url(array $post_data, array $topic_data)
	{
		return ($this->post_is_quotable($post_data, $topic_data) && $this->quote_allowed($topic_data)) ? append_sid("{$this->phpbb_root_path}posting.$this->php_ext", "mode=quote&amp;f={$topic_data['forum_id']}&amp;p={$post_data['post_id']}") : '';
	}

	/**
	 * @param array $post_data
	 * @return string
	 */
	public function get_info_url(array $post_data)
	{
		$forum_id = $post_data['forum_id'];
		return ($this->auth->acl_get('m_info', $forum_id)) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", "i=main&amp;mode=post_details&amp;f=$forum_id&amp;p=" . $post_data['post_id'], true, $this->user->session_id) : '';
	}

	/**
	 * @param array $post_data
	 * @param string $viewtopic_url
	 * @return string
	 */
	public function get_approve_url(array $post_data, $viewtopic_url)
	{
		return append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", "i=queue&amp;p={$post_data['post_id']}&amp;f={$post_data['forum_id']}&amp;redirect=" . urlencode(str_replace('&amp;', '&', $viewtopic_url . '&amp;p=' . $post_data['post_id'] . '#p' . $post_data['post_id'])));
	}

	/**
	 * @param array $post_data
	 * @param array $topic_data
	 * @return string
	 */
	public function get_mcp_edit_url(array $post_data, array $topic_data)
	{
		return append_sid("{$this->phpbb_root_path}posting.$this->php_ext", "mode=edit&amp;f={$topic_data['forum_id']}&amp;p={$post_data['post_id']}&amp;cp=mcp");
	}

	/**
	 * @param array $post_data
	 * @return string
	 *
	public function get_mcp_approve_url(array $post_data)
	{
		return append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=queue&amp;mode=approve_details&amp;f=' . $post_data['forum_id'] . '&amp;p=' . $post_data['post_id'], true, $this->user->session_id);
	}
	*/

	/**
	 * @param array $post_data
	 * @return string
	 */
	public function get_mcp_report_url(array $post_data)
	{
		return ($this->auth->acl_get('m_report', $post_data['forum_id'])) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=reports&amp;mode=report_details&amp;f=' . $post_data['forum_id'] . '&amp;p=' . $post_data['post_id'], true, $this->user->session_id) : '';
	}

	/**
	 * @param array $post_data
	 * @param array $topic_data
	 * @return string
	 */
	public function get_mcp_restore_url(array $post_data, array $topic_data)
	{
		return ($this->auth->acl_get('m_approve', $post_data['forum_id'])) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=queue&amp;mode=' . (($topic_data['topic_visibility'] != ITEM_DELETED) ? 'deleted_posts' : 'deleted_topics') . '&amp;f=' . $post_data['forum_id'] . '&amp;p=' . $post_data['post_id'], true, $this->user->session_id) : '';
	}

	/**
	 * @param array $post_data
	 * @return string
	 */
	public function get_notes_url(array $post_data)
	{
		return ($this->auth->acl_getf_global('m_')) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=notes&amp;mode=user_notes&amp;u=' . $post_data['poster_id'], true, $this->user->session_id) : '';
	}

	/**
	 * @param array $post_data
	 * @return string
	 */
	public function get_warning_url(array $post_data)
	{
		return ($this->auth->acl_get('m_warn') && !$this->user_is_poster($post_data['poster_id']) && $post_data['poster_id'] != ANONYMOUS) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=warn&amp;mode=warn_post&amp;f=' . $post_data['forum_id'] . '&amp;p=' . $post_data['post_id'], true, $this->user->session_id) : '';
	}

	/**
	 * @param int $topic_id
	 * @return string
	 */
	public function get_mcp_queue_url($topic_id)
	{
		return append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", "i=queue&amp;mode=unapproved_posts&amp;t=$topic_id", true, $this->user->session_id);
	}

	/**
	 * @param string $content_type
	 * @param int $topic_id
	 * @return string
	 */
	public function get_mcp_review_url($content_type, $topic_id)
	{
		return append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", "i=-blitze-content-mcp-content_module&amp;mode=content&amp;do=view&amp;type=$content_type&amp;t=$topic_id");
	}

	/**
	 * @param array $post_data
	 * @return bool
	 */
	public function display_attachments_notice(array $post_data)
	{
		return (!$this->auth->acl_get('f_download', $post_data['forum_id']) && $post_data['post_attachment']);
	}

	/**
	 * @param array $post_data
	 * @return bool
	 */
	public function permanent_delete_allowed(array $post_data)
	{
		return ($this->auth->acl_get('m_delete', $post_data['forum_id']) ||
		($this->auth->acl_get('f_delete', $post_data['forum_id']) && $this->user->data['user_id'] == $post_data['poster_id']));
	}

	/**
	 * @param int $poster_id
	 * @return bool
	 */
	public function user_is_poster($poster_id)
	{
		return ($poster_id == $this->user->data['user_id']);
	}

	/**
	 * @param int $forum_id
	 * @return bool
	 */
	public function can_report_post($forum_id)
	{
		return ($this->auth->acl_get('f_report', $forum_id));
	}

	/**
	 * @param array $topic_data
	 * @return bool
	 *
	 public function topic_is_unapproved(array $topic_data)
	 {
	 	return (($topic_data['topic_visibility'] == ITEM_UNAPPROVED || $topic_data['topic_visibility'] == ITEM_REAPPROVE) && $this->auth->acl_get('m_approve', $topic_data['forum_id']));
	 }
	 */

	/**
	 * @param array $topic_data
	 * @return bool
	 */
	 public function topic_has_unapproved_posts(array $topic_data)
	 {
	 	return ($topic_data['topic_visibility'] == ITEM_APPROVED && $topic_data['topic_posts_unapproved'] && $this->auth->acl_get('m_approve', $topic_data['forum_id']));
	 }

	/**
	 * @param array $topic_data
	 * @return bool
	 */
	public function topic_is_reported(array $topic_data)
	{
		return ($topic_data['topic_reported'] && !$topic_data['topic_moved_id'] && $this->auth->acl_get('m_report', $topic_data['forum_id'])) ? true : false;
	}

	/**
	 * @param array $topic_data
	 * @return bool
	 */
	public function topic_is_locked(array $topic_data)
	{
		return ($topic_data['topic_status'] == ITEM_UNLOCKED && $topic_data['forum_status'] == ITEM_UNLOCKED) ? false : true;
	}

	/**
	 * @param array $post_data
	 * @return bool
	 */
	public function post_is_unapproved(array $post_data)
	{
		return (($post_data['post_visibility'] == ITEM_UNAPPROVED || $post_data['post_visibility'] == ITEM_REAPPROVE) && $this->auth->acl_get('m_approve', $topic_data['forum_id'])) ? true : false;
	}

	/**
	 * @param array $post_data
	 * @param array $topic_data
	 * @param string $cp_class
	 * @return string
	 */
	protected function get_cp_param(array $post_data, array $topic_data, $cp_mode)
	{
		$cp_param = '';
		if ($topic_data['topic_first_post_id'] == $post_data['post_id'])
		{
			$cp_param = '&amp;cp=' . ((!$cp_mode) ? (($this->user_is_poster($post_data['poster_id'])) ? 'ucp' : 'mcp') : $cp_mode);
		}
		return $cp_param;
	}

	/**
	 * @param array $post_data
	 * @param array $topic_data
	 * @return bool
	 */
	protected function edit_allowed(array $post_data, array $topic_data)
	{
		return ($this->user->data['is_registered'] && ($this->auth->acl_get('m_edit', $post_data['forum_id']) || (
			!$this->cannot_edit($post_data) &&
			!$this->cannot_edit_time($post_data) &&
			!$this->cannot_edit_locked($post_data, $topic_data)
		)));
	}

	/**
	 * @param array $topic_data
	 * @return bool
	 */
	protected function quote_allowed(array $topic_data)
	{
		return $this->auth->acl_get('m_edit', $topic_data['forum_id']) || ($topic_data['topic_status'] != ITEM_LOCKED &&
			($this->user->data['user_id'] == ANONYMOUS || $this->auth->acl_get('f_reply', $topic_data['forum_id']))
		);
	}

	/**
	 * @param array $post_data
	 * @param array $topic_data
	 * @return bool
	 */
	protected function post_is_quotable(array $post_data, array $topic_data)
	{
		return ($post_data['post_visibility'] == ITEM_APPROVED && $topic_data['topic_first_post_id'] != $post_data['post_id']);
	}

	/**
	 * @param array $post_data
	 * @param array $topic_data
	 * @return bool
	 */
	protected function delete_allowed(array $post_data, array $topic_data)
	{
		return ($this->user->data['is_registered'] && (($this->auth->acl_get('m_delete', $post_data['forum_id']) || ($this->auth->acl_get('m_softdelete', $post_data['forum_id']) && $post_data['post_visibility'] != ITEM_DELETED)) || (
			!$this->cannot_delete($post_data) &&
			!$this->cannot_delete_lastpost($post_data, $topic_data) &&
			!$this->cannot_delete_time($post_data) &&
			!$this->cannot_delete_locked($post_data, $topic_data)
		)));
	}

	/**
	 * @param array $post_data
	 * @return bool
	 */
	protected function softdelete_allowed(array $post_data)
	{
		return ($this->auth->acl_get('m_softdelete', $post_data['forum_id']) ||
			($this->auth->acl_get('f_softdelete', $post_data['forum_id']) && $this->user->data['user_id'] == $post_data['poster_id'])) && ($post_data['post_visibility'] != ITEM_DELETED);
	}

	/**
	 * @param array $post_data
	 * @return bool
	 */
	protected function cannot_edit(array $post_data)
	{
		return (!$this->auth->acl_get('f_edit', $post_data['forum_id']) || $this->user->data['user_id'] != $post_data['poster_id']);
	}

	/**
	 * @param array $post_data
	 * @return bool
	 */
	protected function cannot_edit_time(array $post_data)
	{
		return ($this->config['edit_time'] && $post_data['post_time'] <= time() - ($this->config['edit_time'] * 60));
	}

	/**
	 * @param array $post_data
	 * @param array $topic_data
	 * @return bool
	 */
	protected function cannot_edit_locked(array $post_data, array $topic_data)
	{
		return ($topic_data['topic_status'] == ITEM_LOCKED || $post_data['post_edit_locked']);
	}

	/**
	 * @param array $post_data
	 * @return bool
	 */
	protected function cannot_delete(array $post_data)
	{
		return $this->user->data['user_id'] != $post_data['poster_id'] || (
			!$this->auth->acl_get('f_delete', $post_data['forum_id']) &&
			(!$this->auth->acl_get('f_softdelete', $post_data['forum_id']) || $post_data['post_visibility'] == ITEM_DELETED)
		);
	}

	/**
	 * @param array $post_data
	 * @param array $topic_data
	 * @return bool
	 */
	protected function cannot_delete_lastpost(array $post_data, array $topic_data)
	{
		return $topic_data['topic_last_post_id'] != $post_data['post_id'];
	}

	/**
	 * @param array $post_data
	 * @return bool
	 */
	protected function cannot_delete_time(array $post_data)
	{
		return $this->config['delete_time'] && $post_data['post_time'] <= time() - ($this->config['delete_time'] * 60);
	}

	/**
	 * we do not want to allow removal of the last post if a moderator locked it!
	 * @param array $post_data
	 * @param array $topic_data
	 * @return bool
	 */
	protected function cannot_delete_locked(array $post_data, array $topic_data)
	{
		return $topic_data['topic_status'] == ITEM_LOCKED || $post_data['post_edit_locked'];
	}
}
