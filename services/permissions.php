<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services;

class permissions
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\db */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth		$auth		Auth object
	 * @param \phpbb\config\db		$config		Config object
	 * @param \phpbb\user			$user		User object
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\db $config, \phpbb\user $user)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->user = $user;
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
		return (
			$this->auth->acl_get('m_delete', $post_data['forum_id']) ||
			($this->auth->acl_get('f_delete', $post_data['forum_id']) && $this->user_is_poster($post_data['poster_id']))
		);
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
		return (($post_data['post_visibility'] == ITEM_UNAPPROVED || $post_data['post_visibility'] == ITEM_REAPPROVE) && $this->auth->acl_get('m_approve', $post_data['forum_id'])) ? true : false;
	}

	/**
	 * @param array $post_data
	 * @param array $topic_data
	 * @return bool
	 */
	protected function edit_allowed(array $post_data, array $topic_data)
	{
		return $this->auth->acl_get('m_edit', $post_data['forum_id']) || !$this->user_cannot_modify_post($post_data, $topic_data, 'edit');
	}

	/**
	 * @param array $post_data
	 * @param array $topic_data
	 * @return bool
	 */
	protected function delete_allowed(array $post_data, array $topic_data)
	{
		return $this->moderator_can_delete($post_data, $topic_data) || (
			!$this->cannot_delete_lastpost($post_data, $topic_data) &&
			!$this->user_cannot_modify_post($post_data, $topic_data, 'delete')
		);
	}

	/**
	 * @param array $post_data
	 * @param array $topic_data
	 * @return bool
	 */
	protected function moderator_can_delete(array $post_data, array $topic_data)
	{
		return (
			$this->auth->acl_get('m_delete', $post_data['forum_id']) ||
			($this->auth->acl_get('m_softdelete', $post_data['forum_id']) && $post_data['post_visibility'] != ITEM_DELETED)
		);
	}

	/**
	 * @param array $post_data
	 * @param array $topic_data
	 * @param string $mode
	 * @return bool
	 */
	protected function user_cannot_modify_post(array $post_data, array $topic_data, $mode)
	{
		$callable = 'cannot_' . $mode;
		return $this->$callable($post_data) &&
			!$this->cannot_modify_time($post_data, $mode) &&
			!$this->cannot_modify_locked($post_data, $topic_data);
	}

	/**
	 * @param array $post_data
	 * @return bool
	 */
	protected function softdelete_allowed(array $post_data)
	{
		return (
			$this->auth->acl_get('m_softdelete', $post_data['forum_id']) ||
			($this->auth->acl_get('f_softdelete', $post_data['forum_id']) && $this->user_is_poster($post_data['poster_id']))
		) && $post_data['post_visibility'] != ITEM_DELETED;
	}

	/**
	 * @param array $post_data
	 * @param string $mode edit|delete
	 * @return bool
	 */
	protected function cannot_modify_time(array $post_data, $mode)
	{
		$mode += '_time';
		return $this->config[$mode] && $post_data['post_time'] <= time() - ($this->config[$mode] * 60);
	}

	/**
	 * we do not want to allow removal of the last post if a moderator locked it!
	 * @param array $post_data
	 * @param array $topic_data
	 * @return bool
	 */
	protected function cannot_modify_locked(array $post_data, array $topic_data)
	{
		return $topic_data['topic_status'] == ITEM_LOCKED || $post_data['post_edit_locked'];
	}

	/**
	 * @param array $post_data
	 * @return bool
	 */
	protected function cannot_edit(array $post_data)
	{
		return !$this->user_is_poster($post_data['poster_id']) || !$this->auth->acl_get('f_edit', $post_data['forum_id']);
	}

	/**
	 * @param array $post_data
	 * @return bool
	 */
	protected function cannot_delete(array $post_data)
	{
		return !$this->user_is_poster($post_data['poster_id']) || (
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
		return $topic_data['topic_last_post_id'] == $post_data['post_id'];
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
}
