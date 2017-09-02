<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services;

class helper extends permissions
{
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
		parent::__construct($auth, $config, $user);

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
		return ($this->delete_allowed($post_data, $topic_data)) ? append_sid("{$this->phpbb_root_path}posting.$this->php_ext", 'mode=' . (($this->softdelete_allowed($post_data)) ? 'soft_delete' : 'delete') . "&amp;f={$post_data['forum_id']}&amp;p={$post_data['post_id']}" . $cp_param) : '';
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
	 * @param array $topic_data
	 * @return string
	 */
	public function get_viewtopic_url(array $topic_data)
	{
		return append_sid("{$this->phpbb_root_path}viewtopic.$this->php_ext", "f={$topic_data['forum_id']}&amp;t={$topic_data['topic_id']}");
	}

	/**
	 * @param array $topic_data
	 * @return string
	 */
	public function get_print_topic_url(array $topic_data)
	{
		return ($this->auth->acl_get('f_print', $topic_data['forum_id'])) ? $topic_data['topic_url'] . '?view=print' : '';
	}

	/**
	 * @param array $topic_data
	 * @return string
	 */
	public function get_email_topic_url(array $topic_data)
	{
		return ($this->auth->acl_get('f_email', $topic_data['forum_id']) && $this->config['email_enable']) ? append_sid("{$this->phpbb_root_path}memberlist.{$this->php_ext}", 'mode=email&amp;t=' . $topic_data['topic_id']) : '';
	}

	/**
	 * @param int $forum_id
	 * @param string $username
	 * @return string
	 */
	public function get_search_users_posts_url($forum_id, $username)
	{
		return append_sid("{$this->phpbb_root_path}search.{$this->php_ext}", "author={$username}&amp;" . urlencode('fid[]') . "=$forum_id&amp;sc=0&amp;sf=titleonly&amp;sr=topics");
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
	 * @param int $forum_id
	 * @param int $topic_id
	 * @return string
	 */
	public function get_mcp_url($forum_id, $topic_id)
	{
		$u_mcp = '';
		if ($this->auth->acl_get('m_', $forum_id))
		{
			if ($topic_id)
			{
				$u_mcp = append_sid("{$this->phpbb_root_path}mcp.{$this->php_ext}", "i=mcp_main&amp;mode=topic_view&amp;f=$forum_id&amp;t=$topic_id", true, $this->user->session_id);
			}
			else
			{
				$u_mcp = append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=-blitze-content-mcp-content_module&amp;mode=content', true, $this->user->session_id);
			}
		}
		return $u_mcp;
	}

	/**
	 * @param array $post_data
	 * @param array $topic_data
	 * @param string $cp_mode
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
}
