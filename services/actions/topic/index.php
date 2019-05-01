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

class index extends filter implements action_interface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\user */
	protected $user;

	/** @var \blitze\content\services\types */
	protected $content_types;

	/* @var \blitze\content\services\fields */
	protected $fields;

	/** @var \blitze\sitemaker\services\forum\data */
	protected $forum;

	/* @var \blitze\content\services\helper */
	protected $helper;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $base_url;

	/** @var string */
	protected $redirect_url;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth								$auth					Auth object
	 * @param \phpbb\config\config							$config					Config object
	 * @param \phpbb\db\driver\driver_interface				$db						Database connection
	 * @param \phpbb\controller\helper						$controller_helper		Controller Helper object
	 * @param \phpbb\language\language						$language				Language object
	 * @param \phpbb\pagination								$pagination				Pagination object
	 * @param \phpbb\request\request_interface				$request				Request object
	 * @param \phpbb\template\template						$template				Template object
	 * @param \phpbb\user									$user					User object
	 * @param \blitze\content\services\types				$content_types			Content types object
	 * @param \blitze\content\services\fields				$fields					Content fields object
	 * @param \blitze\sitemaker\services\forum\data			$forum					Forum query object
	 * @param \blitze\content\services\helper				$helper					Content helper object
	 * @param string										$phpbb_root_path		Path to the phpbb includes directory.
	 * @param string										$php_ext				php file extension
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $controller_helper, \phpbb\language\language $language, \phpbb\pagination $pagination, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\user $user, \blitze\content\services\types $content_types, \blitze\content\services\fields $fields, \blitze\sitemaker\services\forum\data $forum, \blitze\content\services\helper $helper, $phpbb_root_path, $php_ext)
	{
		parent::__construct($db, $language, $request, $template);

		$this->auth = $auth;
		$this->config = $config;
		$this->controller_helper = $controller_helper;
		$this->pagination = $pagination;
		$this->user = $user;
		$this->content_types = $content_types;
		$this->fields = $fields;
		$this->forum = $forum;
		$this->helper = $helper;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @inheritdoc
	 */
	public function execute($u_action, $mode = '')
	{
		include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);

		$this->language->add_lang('viewforum');
		$this->template->assign_var('MODE', $mode);

		$content_types = $this->content_types->get_all_types();
		$this->content_forums = $this->content_types->get_forum_types();
		$this->redirect_url = build_url();

		if (sizeof($content_types))
		{
			$sql_where_array = array();
			$this->apply_keyword_filter($sql_where_array);
			$filter_topic_status = $this->apply_status_filter($sql_where_array);
			$filter_content_type = $this->apply_content_type_filter();

			$callable = 'init_' . $mode . '_mode';
			$this->base_url = $u_action . (sizeof($this->params) ? '&amp;' : '');
			$this->$callable($content_types, $sql_where_array);

			$this->forum->query()
				->fetch_forum(array_keys($this->content_forums))
				->set_sorting('t.topic_time')
				->fetch_custom(array('WHERE' => $sql_where_array))
				->build(true, false, false);

			$start = $this->generate_pagination($this->base_url);
			$this->show_topics($mode, $u_action, $start);

			$this->generate_content_type_filter($filter_content_type, $content_types, $this->base_url);
			$this->generate_topic_status_filter($filter_topic_status, $this->base_url);
		}
	}

	/**
	 * @param string $mode
	 * @param string $u_action
	 * @param int $start
	 * @return void
	 */
	protected function show_topics($mode, $u_action, $start)
	{
		$topics_data = $this->forum->get_topic_data((int) $this->config['topics_per_page'], $start);
		$posts_data = $this->forum->get_post_data('first');
		$users_cache = $this->forum->get_posters_info();
		$topic_tracking_info = $this->forum->get_topic_tracking_info();

		unset($this->params['type']);
		$base_url = join('&amp;', array($u_action, http_build_query($this->params)));

		$attachments = $update_count = array();
		foreach ($topics_data as $topic_id => $topic_row)
		{
			$post_row = array_shift($posts_data[$topic_id]);
			$content_type = $this->content_forums[$topic_row['forum_id']];
			$tpl_data = $this->fields->show($content_type, $topic_row, $post_row, $users_cache, $attachments, $update_count, $topic_tracking_info, array(), $this->redirect_url);
			$tpl_data['POST_DATE'] = $this->user->format_date($post_row['post_time'], 'm/d/Y');

			$this->template->assign_block_vars('topicrow', array_merge($tpl_data,
				$this->get_content_type_info($content_type, $base_url),
				$this->get_topic_type_info($tpl_data['S_UNREAD_POST'], $tpl_data['TOPIC_COMMENTS'], $topic_row),
				$this->get_topic_status_info($tpl_data['S_POST_UNAPPROVED'], $tpl_data['S_TOPIC_DELETED'], $base_url, $topic_row),
				$this->get_topic_info($content_type, $u_action, $topic_row),
				$this->get_moderator_info($mode, $topic_id, $tpl_data['S_POST_UNAPPROVED'], false, $tpl_data['S_TOPIC_DELETED'])
			));
		}
	}

	/**
	 * @param string $content_type
	 * @param string $u_action
	 * @param array $row
	 * @return array
	 */
	protected function get_topic_info($content_type, $u_action, array $row)
	{
		return array(
			'ATTACH_ICON_IMG'	=> $this->get_attachment_icon($row),
			'U_REVIEW_TOPIC'	=> $u_action . "&amp;do=view&amp;type=$content_type&amp;t=" . $row['topic_id'],
		);
	}

	/**
	 * @return void
	 */
	protected function init_mcp_mode()
	{
		$s_can_approve = (bool) sizeof(array_intersect_key($this->content_forums, $this->auth->acl_getf('m_approve', true)));
		$s_can_make_sticky = (bool) sizeof(array_intersect_key($this->content_forums, $this->auth->acl_getf('f_sticky', true)));
		$s_can_make_announce = (bool) sizeof(array_intersect_key($this->content_forums, $this->auth->acl_getf('f_announce', true)));
		$s_can_delete = (bool) sizeof(array_intersect_key($this->content_forums, $this->auth->acl_getf('m_delete', true)));
		$s_can_lock = (bool) sizeof(array_intersect_key($this->content_forums, $this->auth->acl_getf('m_lock', true)));
		$user_is_mod = (bool) sizeof(array_intersect_key($this->content_forums, $this->auth->acl_getf('m_', true)));

		$this->template->assign_vars(array(
			'S_CAN_DELETE'			=> $s_can_delete,
			'S_CAN_RESTORE'			=> $s_can_approve,
			'S_CAN_LOCK'			=> $s_can_lock,
			'S_CAN_SYNC'			=> $user_is_mod,
			'S_CAN_APPROVE'			=> $s_can_approve,
			'S_CAN_MAKE_NORMAL'		=> ($s_can_make_sticky || $s_can_make_announce),
			'S_CAN_MAKE_STICKY'		=> $s_can_make_sticky,
			'S_CAN_MAKE_ANNOUNCE'	=> $s_can_make_announce,
			'U_MCP_ACTION'			=> $this->base_url . '&amp;do=moderate&amp;redirect=' . $this->get_redirect_url($this->base_url),
		));
	}

	/**
	 * @param array $content_types
	 * @param array $sql_where_array
	 */
	protected function init_ucp_mode(array $content_types, array &$sql_where_array)
	{
		$sql_where_array[] =  't.topic_poster = ' . (int) $this->user->data['user_id'];

		// list all content types that the user can post in
		$postable_forums = array_intersect_key($this->content_forums, $this->auth->acl_getf('f_post', true));
		$postable_types = array_intersect_key($content_types, array_flip($postable_forums));
		$redirect = '&amp;redirect=' . urlencode($this->redirect_url);

		/** @var \blitze\content\model\entity\type $entity */
		foreach ($postable_types as $type => $entity)
		{
			$this->template->assign_block_vars('postable', array(
				'TYPE'		=> $entity->get_content_langname(),
				'COLOUR'	=> $entity->get_content_colour(),
				'U_POST'	=> append_sid("{$this->phpbb_root_path}posting." . $this->php_ext, 'mode=post&amp;f=' . $entity->get_forum_id() . $redirect),
			));
		}
	}

	/**
	 * @param string $type
	 * @param string $base_url
	 * @return array
	 */
	protected function get_content_type_info($type, $base_url)
	{
		$entity = $this->content_types->get_type($type);

		return array(
			'CONTENT_TYPE'			=> $entity->get_content_langname(),
			'CONTENT_TYPE_COLOR'	=> $entity->get_content_colour(),
			'S_COMMENTS'			=> $entity->get_allow_comments(),
			'U_CONTENT_TYPE'		=> $base_url . "&amp;type=$type",
		);
	}

	/**
	 * Get folder img, topic status/type related information
	 * @param bool $unread_topic
	 * @param int $num_comments
	 * @param array $row
	 * @return array
	 */
	protected function get_topic_type_info($unread_topic, $num_comments, $row)
	{
		$folder_img = $folder_alt = $topic_type = '';
		topic_status($row, $num_comments, $unread_topic, $folder_img, $folder_alt, $topic_type);

		return array(
			'TOPIC_TYPE'				=> $topic_type,
			'TOPIC_IMG_STYLE'			=> $folder_img,
			'TOPIC_FOLDER_IMG'			=> $this->user->img($folder_img, $folder_alt),
		);
	}

	/**
	 * @param bool $topic_unapproved
	 * @param bool $topic_deleted
	 * @param string $base_url
	 * @param array $row
	 * @return array
	 */
	protected function get_topic_status_info($topic_unapproved, $topic_deleted, $base_url, array $row)
	{
		if ($topic_deleted)
		{
			$topic_status = 'deleted';
		}
		else if ($topic_unapproved)
		{
			$topic_status = 'unapproved';
		}
		else if ($row['topic_time'] > time())
		{
			$topic_status = 'scheduled';
		}
		else
		{
			$topic_status = $this->filter_topic_types_ary[$row['topic_type']];
		}

		return array(
			'TOPIC_STATUS'		=> $this->language->lang('TOPIC_' . strtoupper($topic_status)),
			'U_TOPIC_STATUS'	=> $base_url . "&amp;status=$topic_status",
		);
	}

	/**
	 * @param array $row
	 * @return string
	 */
	protected function get_attachment_icon(array $row)
	{
		return ($this->auth->acl_get('u_download') && $this->auth->acl_get('f_download', $row['forum_id']) && $row['topic_attachment']) ? $this->user->img('icon_topic_attach', $this->language->lang('TOTAL_ATTACHMENTS')) : '';
	}

	/**
	 * @param string $mode
	 * @param int $topic_id
	 * @param bool $topic_unapproved
	 * @param bool $posts_unapproved
	 * @param bool $topic_deleted
	 * @return array
	 */
	protected function get_moderator_info($mode, $topic_id, $topic_unapproved, $posts_unapproved, $topic_deleted)
	{
		$u_mcp_queue = '';
		if ($mode === 'mcp')
		{
			$u_mcp_queue = $this->get_mcp_queue_url($topic_unapproved, $posts_unapproved, $topic_id);
			$u_mcp_queue = (!$u_mcp_queue && $topic_deleted) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=queue&amp;mode=deleted_topics&amp;t=' . $topic_id, true, $this->user->session_id) : $u_mcp_queue;
		}

		return array(
			'U_MCP_QUEUE'	=> $u_mcp_queue,
		);
	}

	/**
	 * @param bool $topic_unapproved
	 * @param bool $posts_unapproved
	 * @param int $topic_id
	 * @return string
	 */
	protected function get_mcp_queue_url($topic_unapproved, $posts_unapproved, $topic_id)
	{
		return ($topic_unapproved || $posts_unapproved) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=queue&amp;mode=' . (($topic_unapproved) ? 'approve_details' : 'unapproved_posts') . "&amp;t=$topic_id", true, $this->user->session_id) : '';
	}

	/**
	 * @param string $base_url
	 * @return string
	 */
	protected function get_redirect_url($base_url)
	{
		$base_url .= (sizeof($this->params)) ? '&' . http_build_query($this->params) : '';
		return urlencode(str_replace('&amp;', '&', $base_url));
	}

	/**
	 * @param string $u_action
	 * @return int
	 */
	protected function generate_pagination($u_action)
	{
		$start = $this->request->variable('start', 0);

		$topics_count = $this->forum->get_topics_count();
		$u_action .= http_build_query($this->params);

		$this->template->assign_vars(array(
			'TOTAL_TOPICS'		=> $this->language->lang('VIEW_FORUM_TOPICS', $topics_count),
		));

		$start = $this->pagination->validate_start($start, (int) $this->config['topics_per_page'], $topics_count);
		$this->pagination->generate_template_pagination($u_action, 'pagination', 'start', $topics_count, (int) $this->config['topics_per_page'], $start);

		return $start;
	}
}
