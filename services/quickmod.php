<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services;

class quickmod
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth				$auth				Auth object
	 * @param \phpbb\template\template		$template			Template object
	 * @param \phpbb\user					$user				User object
	 * @param string						$phpbb_root_path	Path to the phpbb includes directory.
	 * @param string						$php_ext			php file extension
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\template\template $template, \phpbb\user $user, $phpbb_root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @param array $topic_data
	 * @return void
	 */
	public function show_tools(array $topic_data)
	{
		$s_quickmod_action = append_sid(
			"{$this->phpbb_root_path}mcp.$this->php_ext",
			array(
				'f'	=> $topic_data['forum_id'],
				't'	=> $topic_data['topic_id'],
				'quickmod'	=> 1,
				'redirect'	=> urlencode(str_replace('&amp;', '&', $topic_data['topic_url'])),
			),
			true,
			$this->user->session_id
		);

		$quickmod_array = $this->get_options($topic_data);
		foreach ($quickmod_array as $option => $qm_ary)
		{
			if (!empty($qm_ary[1]))
			{
				phpbb_add_quickmod_option($s_quickmod_action, $option, $qm_ary[0]);
			}
		}

		$this->template->assign_var('S_MOD_ACTION', $s_quickmod_action);
	}

	/**
	 * @param array $topic_data
	 * @return array
	 */
	protected function get_options(array $topic_data)
	{
		$s_quickmod_array = array(
			'lock'				=> array('LOCK_TOPIC', $this->allow_topic_lock($topic_data)),
			'unlock'			=> array('UNLOCK_TOPIC', $this->allow_topic_unlock($topic_data)),
			'delete_topic'		=> array('DELETE_TOPIC', $this->allow_topic_delete($topic_data)),
			'restore_topic'		=> array('RESTORE_TOPIC', $this->allow_topic_restore($topic_data)),
		);

		if ($this->auth->acl_get('m_', $topic_data['forum_id']) || $this->user_is_topic_poster($topic_data['topic_poster']))
		{
			$s_quickmod_array += array(
				'make_normal'		=> array('MAKE_NORMAL', $this->allow_make_normal($topic_data['forum_id'], $topic_data['topic_type'])),
				'make_sticky'		=> array('MAKE_STICKY', $this->allow_make_sticky($topic_data['forum_id'], $topic_data['topic_type'])),
				'make_announce'		=> array('MAKE_ANNOUNCE', $this->allow_make_announce($topic_data['forum_id'], $topic_data['topic_type'])),
				'make_global'		=> array('MAKE_GLOBAL', $this->allow_make_global($topic_data['forum_id'], $topic_data['topic_type'])),
			);
		}

		return array_merge($s_quickmod_array, array('topic_logs' => array('VIEW_TOPIC_LOGS', $this->auth->acl_get('m_', $topic_data['forum_id']))));
	}

	/**
	 * @param array $topic_data
	 * @return bool
	 */
	protected function allow_topic_lock(array $topic_data)
	{
		return ($topic_data['topic_status'] == ITEM_UNLOCKED) && $this->user_can_lock_topic($topic_data['forum_id'], $topic_data['topic_poster']);
	}

	/**
	 * @param array $topic_data
	 * @return bool
	 */
	protected function allow_topic_unlock(array $topic_data)
	{
		return ($topic_data['topic_status'] != ITEM_UNLOCKED) && ($this->auth->acl_get('m_lock', $topic_data['forum_id']));
	}

	/**
	 * @param array $topic_data
	 * @return bool
	 */
	protected function allow_topic_delete(array $topic_data)
	{
		return ($this->auth->acl_get('m_delete',  $topic_data['forum_id'])) || (($topic_data['topic_visibility'] != ITEM_DELETED) && $this->auth->acl_get('m_softdelete',  $topic_data['forum_id']));
	}

	/**
	 * @param array $topic_data
	 * @return bool
	 */
	protected function allow_topic_restore(array $topic_data)
	{
		return (($topic_data['topic_visibility'] == ITEM_DELETED) && $this->auth->acl_get('m_approve', $topic_data['forum_id']));
	}

	/**
	 * @param int $poster_id
	 * @return bool
	 */
	protected function user_is_topic_poster($poster_id)
	{
		return ($this->user->data['is_registered'] && $this->user->data['user_id'] == $poster_id) ? true : false;
	}

	/**
	 * @param int $forum_id
	 * @param int $poster_id
	 * @return bool
	 */
	protected function user_can_lock_topic($forum_id, $poster_id)
	{
		return ($this->auth->acl_get('m_lock', $forum_id) || ($this->auth->acl_get('f_user_lock', $forum_id) && $this->user_is_topic_poster($poster_id))) ? true : false;
	}

	/**
	 * @param int $forum_id
	 * @param int $topic_type
	 * @return bool
	 */
	protected function allow_make_normal($forum_id, $topic_type)
	{
		return ($this->auth->acl_gets('f_sticky', 'f_announce', 'f_announce_global', $forum_id) && $topic_type != POST_NORMAL);
	}

	/**
	 * @param int $forum_id
	 * @param int $topic_type
	 * @return bool
	 */
	protected function allow_make_sticky($forum_id, $topic_type)
	{
		return ($this->auth->acl_get('f_sticky', $forum_id) && $topic_type != POST_STICKY);
	}

	/**
	 * @param int $forum_id
	 * @param int $topic_type
	 * @return bool
	 */
	protected function allow_make_announce($forum_id, $topic_type)
	{
		return ($this->auth->acl_get('f_announce', $forum_id) && $topic_type != POST_ANNOUNCE);
	}

	/**
	 * @param int $forum_id
	 * @param int $topic_type
	 * @return bool
	 */
	protected function allow_make_global($forum_id, $topic_type)
	{
		return ($this->auth->acl_get('f_announce_global', $forum_id) && $topic_type != POST_GLOBAL);
	}
}
