<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\actions\topic;

class filter
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var array */
	protected $content_forums;

	/** @var array */
	protected $params = array();

	/** @var array */
	protected $filter_topic_status_ary = array(
		'-1'				=> 'scheduled',
		ITEM_UNAPPROVED		=> 'unapproved',
		ITEM_APPROVED		=> 'published',
		ITEM_DELETED		=> 'deleted',
	);

	/** @var array */
	protected $filter_topic_types_ary = array(
		POST_NORMAL		=> 'published',
		POST_STICKY		=> 'featured',
		POST_ANNOUNCE	=> 'recommended',
		POST_GLOBAL		=> 'must_read',
	);

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface				$db						Database connection
	 * @param \phpbb\language\language						$language				Language object
	 * @param \phpbb\request\request_interface				$request				Request object
	 * @param \phpbb\template\template						$template				Template object
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\request\request_interface $request, \phpbb\template\template $template)
	{
		$this->db = $db;
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
	}

	/**
	 * @return string
	 */
	protected function apply_content_type_filter()
	{
		$content_type = $this->request->variable('type', '');

		if ($content_type && in_array($content_type, $this->content_forums))
		{
			$this->params['type'] = $content_type;
			$this->content_forums = array_intersect($this->content_forums, array($content_type));
			$this->template->assign_vars(array('S_CONTENT_FILTER' => true));
		}

		return $content_type;
	}

	/**
	 * @param string $type
	 * @param array $content_types
	 * @param string $u_action
	 * @return void
	 */
	protected function generate_content_type_filter($type, array $content_types, $u_action)
	{
		$copy_params = $this->params;
		unset($copy_params['type']);
		$view_url = $u_action . http_build_query($copy_params);

		$this->template->assign_block_vars('content', array(
			'TITLE'			=> $this->language->lang('TOPIC_ALL'),
			'COLOUR'		=> '',
			'S_SELECTED'	=> (!$type) ? true : false,
			'U_VIEW'		=> $view_url
		));

		foreach ($content_types as $entity)
		{
			$content_name = $entity->get_content_name();
			$this->template->assign_block_vars('content', array(
				'TITLE'			=> $entity->get_content_langname(),
				'COLOUR'		=> $entity->get_content_colour(),
				'S_SELECTED'	=> ($type === $content_name) ? true : false,
				'U_VIEW'		=> $view_url . '&amp;type=' . $content_name
			));
		}
	}

	/**
	 * @param array $sql_where_array
	 * @return string
	 */
	protected function apply_status_filter(array &$sql_where_array)
	{
		if ($topic_status = $this->request->variable('status', ''))
		{
			$this->template->assign_vars(array('S_STATUS_FILTER' => true));
			$this->params['status'] = $topic_status;
			$this->get_filter_status_sql($topic_status, $sql_where_array);
		}

		return $topic_status;
	}

	/**
	 * @param string $topic_status
	 * @param string $u_action
	 * @return void
	 */
	protected function generate_topic_status_filter($topic_status, $u_action)
	{
		$copy_params = $this->params;
		unset($copy_params['status']);
		$view_url = $u_action . http_build_query($copy_params);

		$this->template->assign_block_vars('status', array(
			'TITLE'			=> $this->language->lang('TOPIC_ALL'),
			'S_SELECTED'	=> (!$topic_status) ? true : false,
			'U_VIEW'		=> $view_url,
		));

		$topic_status_ary = array_unique(array_merge($this->filter_topic_status_ary, $this->filter_topic_types_ary));
		foreach ($topic_status_ary as $status)
		{
			$this->template->assign_block_vars('status', array(
				'TITLE'			=> $this->language->lang('TOPIC_' . strtoupper($status)),
				'S_SELECTED'	=> ($status === $topic_status) ? true : false,
				'U_VIEW'		=> $view_url . '&amp;status=' . $status
			));
		}
	}

	/**
	 * @param string $topic_status
	 * @param array $sql_where_array
	 * @return void
	 */
	protected function get_filter_status_sql($topic_status, array &$sql_where_array)
	{
		switch ($topic_status)
		{
			case 'scheduled':
				$sql_where_array[] = 't.topic_time > ' . time();
			break;
			case 'unapproved':
			case 'published':
			case 'deleted':
				$sql_where_array[] = 't.topic_visibility = ' . (int) array_search($topic_status, $this->filter_topic_status_ary);
			break;
			case 'recommended':
			case 'featured':
			case 'must_read':
				$sql_where_array[] = 't.topic_type = ' . (int) array_search($topic_status, $this->filter_topic_types_ary);
				$sql_where_array[] = 't.topic_visibility = ' . ITEM_APPROVED;
			break;
		}
	}

	/**
	 * @param array $sql_where_array
	 * @return void
	 */
	protected function apply_keyword_filter(array &$sql_where_array)
	{
		if ($keyword = $this->request->variable('keyword', '', true))
		{
			$this->params['keyword'] = $keyword;
			$sql_where_array[] = 't.topic_title ' . $this->db->sql_like_expression($this->db->get_any_char() . $keyword . $this->db->get_any_char());
		}
	}
}
