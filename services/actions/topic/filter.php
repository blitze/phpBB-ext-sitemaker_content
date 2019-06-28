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

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var array */
	protected $content_forums;

	/** @var array */
	protected $params = array();

	/** @var string */
	protected $content_type_base_url = '';

	/** @var string */
	protected $topic_status_base_url = '';

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface				$db						Database connection
	 * @param \phpbb\request\request_interface				$request				Request object
	 * @param \phpbb\template\template						$template				Template object
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\request\request_interface $request, \phpbb\template\template $template)
	{
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
	}

	/**
	 * @param array $search_info
	 * @param string $u_action
	 * @return void
	 */
	protected function generate_search_filter(array $search_info, $u_action)
	{
		$this->template->assign_vars(array(
			'search_info'	=> $search_info,
			'search_url'	=> $u_action,
			'search_types'	=> array(
				'topic_title'				=> 'TOPIC_TITLE',
				'topic_first_poster_name'	=> 'AUTHOR',
			),
		));
	}

	/**
	 * @param array $sql_where_array
	 * @return array
	 */
	protected function apply_search_filter(array &$sql_where_array)
	{
		$table_field = 'topic_title';
		if ($search = $this->request->variable('search', '', true))
		{
			$search_type = $this->request->variable('search_type', $table_field);
			$table_field = (in_array($table_field, ['topic_title', 'topic_first_poster_name'])) ? $search_type : $table_field;

			$sql_where_array[] = 't.' . $table_field . ' ' . $this->db->sql_like_expression($this->db->get_any_char() . $search . $this->db->get_any_char());

			$this->params = array(
				'search'		=> $search,
				'search_type'	=> $table_field,
			);
		}

		return array(
			'search'	=> $search,
			'type'		=> $table_field,
		);
	}

	/**
	 * @param string $type
	 * @param array $content_types
	 * @param string $u_action
	 * @return void
	 */
	protected function generate_content_type_filter($type, array $content_types, $u_action)
	{
		$this->content_type_base_url = $this->get_filter_type_base_url($u_action, 'type');

		$this->template->assign_block_vars('content', array(
			'TITLE'			=> 'TOPIC_ALL',
			'S_SELECTED'	=> (!$type) ? true : false,
			'U_VIEW'		=> $this->content_type_base_url,
		));

		foreach ($content_types as $entity)
		{
			$content_name = $entity->get_content_name();
			$this->template->assign_block_vars('content', array(
				'TITLE'			=> $entity->get_content_langname(),
				'COLOUR'		=> $entity->get_content_colour(),
				'S_SELECTED'	=> ($type === $content_name) ? true : false,
				'U_VIEW'		=> $this->content_type_base_url . '&amp;type=' . $content_name,
			));
		}
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
	 * @param string $topic_status
	 * @param string $u_action
	 * @return void
	 */
	protected function generate_topic_status_filter($topic_status, $u_action)
	{
		$this->topic_status_base_url = $this->get_filter_type_base_url($u_action, 'status');

		$this->template->assign_block_vars('status', array(
			'TITLE'			=> 'TOPIC_ALL',
			'S_SELECTED'	=> (!$topic_status) ? true : false,
			'U_VIEW'		=> $this->topic_status_base_url,
		));

		$topic_status_ary = array_unique(array_merge($this->get_topic_status_filters(), $this->get_topic_types_filters()));
		foreach ($topic_status_ary as $status)
		{
			$this->template->assign_block_vars('status', array(
				'TITLE'			=> 'TOPIC_' . strtoupper($status),
				'S_SELECTED'	=> ($status === $topic_status) ? true : false,
				'U_VIEW'		=> $this->topic_status_base_url . '&amp;status=' . $status
			));
		}
	}

	/**
	 * @param array $sql_where_array
	 * @return string
	 */
	protected function apply_topic_status_filter(array &$sql_where_array)
	{
		if ($topic_status = $this->request->variable('status', ''))
		{
			$this->template->assign_vars(array('S_STATUS_FILTER' => true));
			$this->params['status'] = $topic_status;

			$filter_types = array(
				$topic_status . '_topics' => [$topic_status],
				'visibility'	=> ['published', 'deleted', 'unapproved'],
				'topic_type'	=> ['featured', 'must_read', 'recommended'],
			);

			do
			{
				$filter = key($filter_types);
				$type = array_shift($filter_types);
				$method = 'set_' . $filter . '_sql';

				if ($this->is_callable($topic_status, $type, $method))
				{
					call_user_func_array(array($this, $method), array($topic_status, &$sql_where_array));
					break;
				}
			}
			while (sizeof($filter_types));
		}

		return $topic_status;
	}

	/**
	 * @param string $topic_status
	 * @param array $sql_where_array
	 * @return void
	 */
	protected function set_visibility_sql($topic_status, array &$sql_where_array)
	{
		$sql_where_array[] = 't.topic_visibility = ' . (int) array_search($topic_status, $this->get_topic_status_filters());
	}

	/**
	 * @param string $topic_status
	 * @param array $sql_where_array
	 * @return void
	 */
	protected function set_topic_type_sql($topic_status, array &$sql_where_array)
	{
		$sql_where_array[] = 't.topic_type = ' . (int) array_search($topic_status, $this->get_topic_types_filters());
	}

	/**
	 * @param string $topic_status
	 * @param array $filter_type
	 * @param string $method
	 * @return bool
	 */
	protected function is_callable($topic_status, array $filter_type, $method)
	{
		return (in_array($topic_status, $filter_type) && is_callable(array($this, $method))) ? true : false;
	}

	/**
	 * @param string $u_action
	 * @param string $type
	 * @return string
	 */
	protected function get_filter_type_base_url($u_action, $type = '')
	{
		$copy_params = $this->params;
		unset($copy_params[$type]);
		return $u_action . '&amp;' . http_build_query($copy_params);
	}

	/**
	 * @return array
	 */
	protected function get_topic_status_filters()
	{
		return array(
			ITEM_UNAPPROVED		=> 'unapproved',
			ITEM_APPROVED		=> 'published',
			ITEM_DELETED		=> 'deleted',
			ITEM_REAPPROVE		=> 'unapproved',
		);
	}

	/**
	 * @return array
	 */
	protected function get_topic_types_filters()
	{
		return array(
			POST_NORMAL		=> 'published',
			POST_STICKY		=> 'featured',
			POST_ANNOUNCE	=> 'recommended',
			POST_GLOBAL		=> 'must_read',
		);
	}
}
