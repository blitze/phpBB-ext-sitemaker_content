<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\actions\topic;

use blitze\content\services\actions\action_interface;

class moderate implements action_interface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth								$auth					Auth object
	 * @param \phpbb\db\driver\driver_interface				$db						Database connection
	 * @param \phpbb\language\language						$language				Language object
	 * @param \phpbb\request\request_interface				$request				Request object
	 * @param string										$phpbb_root_path		Path to the phpbb includes directory.
	 * @param string										$php_ext				php file extension
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\request\request_interface $request, $phpbb_root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->db = $db;
		$this->language = $language;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @inheritdoc
	 */
	public function execute($u_action, $mode = '')
	{
		$this->language->add_lang('manager', 'blitze/content');

		$action = $this->request->variable('action', '');
		$topic_ids = array_filter($this->request->variable('topic_id_list', array(0)));

		if (!sizeof($topic_ids) && !$this->request->is_set_post('confirm'))
		{
			trigger_error('NO_TOPIC_SELECTED');
		}

		if (method_exists($this, $action))
		{
			$forum_ids = $this->get_selected_forum_ids($topic_ids);
			$this->{$action}($topic_ids, $forum_ids);
		}
		else
		{
			$message =  $this->language->lang('INVALID_REQUEST', $action);
			trigger_error($message . '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $u_action . '">', '</a>'));
		}
	}

	/**
	 * @param array $topic_ids
	 * @param array $forum_ids
	 * @param bool $execute
	 * @return void
	 */
	protected function approve(array $topic_ids, array $forum_ids, $execute = true)
	{
		include($this->phpbb_root_path . 'includes/mcp/mcp_queue.' . $this->php_ext);
		include($this->phpbb_root_path . 'includes/functions_messenger.' . $this->php_ext);

		if (!(sizeof(array_intersect_key($forum_ids, $this->auth->acl_getf('m_approve', true)))))
		{
			trigger_error('NOT_AUTHORISED');
		}

		if ($execute)
		{
			\mcp_queue::approve_topics('approve', $topic_ids, '-blitze-content-mcp-content_module', 'content');
		}
	}

	/**
	 * @param array $topic_ids
	 * @param array $forum_ids
	 * @return void
	 */
	protected function disapprove(array $topic_ids, array $forum_ids)
	{
		$post_id_list = $this->get_selected_post_ids($topic_ids);

		if (!empty($post_id_list))
		{
			$this->approve($topic_ids, $forum_ids, false);
			\mcp_queue::disapprove_posts($post_id_list, '-blitze-content-mcp-content_module', 'content');
		}
		else
		{
			trigger_error('NO_POST_SELECTED');
		}
	}

	/**
	 * @param array $topic_ids
	 * @return void
	 */
	protected function restore_topic(array $topic_ids)
	{
		mcp_restore_topic($topic_ids);
	}

	/**
	 * @param array $topic_ids
	 * @param array $forum_ids
	 */
	protected function delete_topic(array $topic_ids, array $forum_ids)
	{
		$this->language->add_lang('viewtopic');

		$can_delete = (sizeof(array_intersect_key($forum_ids, $this->auth->acl_getf('m_delete', true)))) ? true : false;
		$soft_delete = (($this->request->is_set_post('confirm') && !$this->request->is_set_post('delete_permanent')) || !$can_delete) ? true : false;

		mcp_delete_topic($topic_ids, $soft_delete, $this->request->variable('delete_reason', '', true));
	}

	/**
	 * @param array $topic_ids
	 * @return void
	 */
	protected function resync(array $topic_ids)
	{
		include($this->phpbb_root_path . 'includes/mcp/mcp_forum.' . $this->php_ext);

		mcp_resync_topics($topic_ids);
	}

	/**
	 * @param $action
	 * @param array $topic_ids
	 */
	protected function change_topic_type($action, array $topic_ids)
	{
		change_topic_type($action, $topic_ids);
	}

	/**
	 * @param array $topic_ids
	 * @return void
	 */
	protected function make_announce(array $topic_ids)
	{
		$this->change_topic_type('make_announce', $topic_ids);
	}

	/**
	 * @param array $topic_ids
	 * @return void
	 */
	protected function make_sticky(array $topic_ids)
	{
		$this->change_topic_type('make_sticky', $topic_ids);
	}

	/**
	 * @param array $topic_ids
	 * @return void
	 */
	protected function make_global(array $topic_ids)
	{
		$this->change_topic_type('make_global', $topic_ids);
	}

	/**
	 * @param array $topic_ids
	 * @return void
	 */
	protected function make_normal(array $topic_ids)
	{
		$this->change_topic_type('make_normal', $topic_ids);
	}

	/**
	 * @param array $topic_ids
	 * @return void
	 */
	protected function lock(array $topic_ids)
	{
		lock_unlock('lock', $topic_ids);
	}

	/**
	 * @param array $topic_ids
	 * @return void
	 */
	protected function unlock(array $topic_ids)
	{
		lock_unlock('unlock', $topic_ids);
	}

	/**
	 * @param array $topic_ids
	 * @return array
	 */
	protected function get_selected_forum_ids(array $topic_ids)
	{
		if (!sizeof($topic_ids))
		{
			return array();
		}

		$sql = 'SELECT forum_id
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids);
		$result = $this->db->sql_query($sql);

		$forum_ids = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$forum_ids[$row['forum_id']] = $row['forum_id'];
		}
		$this->db->sql_freeresult($result);

		return $forum_ids;
	}

	/**
	 * @param array $topic_ids
	 * @return array
	 */
	protected function get_selected_post_ids(array $topic_ids)
	{
		$post_id_list = array_filter($this->request->variable('post_id_list', array(0)));

		if (sizeof($post_id_list))
		{
			return $post_id_list;
		}

		$sql = 'SELECT post_id
			FROM ' . POSTS_TABLE . '
			WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids);
		$result = $this->db->sql_query($sql);

		$post_id_list = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$post_id_list[] = (int) $row['post_id'];
		}
		$this->db->sql_freeresult($result);

		return $post_id_list;
	}
}
