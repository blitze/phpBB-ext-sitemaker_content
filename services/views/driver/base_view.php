<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\views\driver;

abstract class base_view implements views_interface
{
	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

	/** @var\phpbb\language\language */
	protected $language;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\template\template */
	protected $template;

	/* @var \blitze\content\services\fields */
	protected $fields;

	/** @var \blitze\sitemaker\services\forum\data */
	protected $forum;

	/* @var \blitze\content\services\helper */
	protected $helper;

	/* @var \blitze\content\services\quickmod */
	protected $quickmod;

	/** @var \blitze\content\services\topic\blocks_factory */
	protected $topic_blocks_factory;

	/**
	 * Constructor
	 *
	 * @param \phpbb\event\dispatcher_interface					$phpbb_dispatcher		Event dispatcher object
	 * @param \phpbb\language\language							$language				Language Object
	 * @param \phpbb\pagination									$pagination				Pagination object
	 * @param \phpbb\template\template							$template				Template object
	 * @param \blitze\content\services\fields					$fields					Content fields object
	 * @param \blitze\sitemaker\services\forum\data				$forum					Forum Data object
	 * @param \blitze\content\services\helper					$helper					Content helper object
	 * @param \blitze\content\services\quickmod					$quickmod				Quick moderator tools
	 * @param \blitze\content\services\topic\blocks_factory		$topic_blocks_factory	Topic blocks factory object
	 */
	public function __construct(\phpbb\event\dispatcher_interface $phpbb_dispatcher, \phpbb\language\language $language, \phpbb\pagination $pagination, \phpbb\template\template $template, \blitze\content\services\fields $fields, \blitze\sitemaker\services\forum\data $forum, \blitze\content\services\helper $helper, \blitze\content\services\quickmod $quickmod, \blitze\content\services\topic\blocks_factory $topic_blocks_factory)
	{
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->language = $language;
		$this->pagination = $pagination;
		$this->template = $template;
		$this->fields = $fields;
		$this->forum = $forum;
		$this->helper = $helper;
		$this->quickmod = $quickmod;
		$this->topic_blocks_factory = $topic_blocks_factory;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_detail_template()
	{
		return 'views/content_detail.html';
	}

	/**
	 * {@inheritdoc}
	 */
	public function build_index_query(array $filters, \blitze\content\model\entity\type $entity = null)
	{
		$forum_id = $entity ? $entity->get_forum_id() : 0;
		$sql_array = $this->get_filter_sql($filters, $forum_id);

		$this->forum->query()
			->fetch_forum($forum_id)
			->fetch_custom($sql_array)
			->set_sorting('t.topic_time')
			->build(true, false, false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_index(\blitze\content\model\entity\type $entity, $page, array $filters, array $topic_data_overwrite = array())
	{
		$content_type = $entity->get_content_name();
		$items_per_page = $entity->get_items_per_page();
		$start = ($page - 1) * $items_per_page;

		$this->build_index_query($filters, $entity);
		$this->set_mcp_url($entity->get_forum_id());

		if ($entity->get_show_pagination())
		{
			$filter_type = key($filters);
			$filter_value = (array) current($filters);

			$total_topics = $this->forum->get_topics_count();
			$this->generate_pagination('summary', $total_topics, $start, $items_per_page, array(
				'type'			=> $content_type,
				'filter_type'	=> $filter_type,
				'filter_value'	=> current($filter_value),
			));
		}

		return $this->display_topics($entity, $items_per_page, $start, $topic_data_overwrite);
	}

	/**
	 * @param \blitze\content\model\entity\type $entity
	 * @param int $items_per_page
	 * @param int $start
	 * @param array $topic_data_overwrite
	 * @return int
	 */
	protected function display_topics(\blitze\content\model\entity\type $entity, $items_per_page, $start, array $topic_data_overwrite)
	{
		$content_type = $entity->get_content_name();
		$topics_data = $this->forum->get_topic_data($items_per_page, $start);
		$posts_data = $this->forum->get_post_data('first');
		$topic_tracking_info = $this->forum->get_topic_tracking_info($entity->get_forum_id());
		$users_cache = $this->forum->get_posters_info();
		$attachments = $this->forum->get_attachments($entity->get_forum_id());

		$this->fields->prepare_to_show($entity, array_keys($topics_data), $entity->get_summary_fields(), $entity->get_summary_tpl(), 'summary');

		$update_count = array();
		$max_update_time = 0;

		foreach ($posts_data as $topic_id => $posts)
		{
			$post_data	= array_shift($posts);
			$topic_data	= $topics_data[$topic_id];
			$topic_data = $this->fields->show($content_type, $topic_data, $post_data, $users_cache, $attachments, $update_count, $topic_tracking_info, $topic_data_overwrite);

			$this->template->assign_block_vars('topicrow', $topic_data);
			$max_update_time = max($max_update_time, (int) $topic_data['UPDATED']);
		}
		unset($topics_data, $posts_data, $users_cache, $attachments, $topic_tracking_info);

		return $max_update_time;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_detail(\blitze\content\model\entity\type $entity, $topic_id, $view, $redirect_url, array &$update_count, array $topic_data_overwrite = array())
	{
		$this->language->add_lang('viewtopic');
		$this->language->add_lang('content', 'blitze/content');
		$this->set_mcp_url($entity->get_forum_id(), $topic_id);

		$this->forum->query()
			->fetch_topic($topic_id)
			->fetch_watch_status()
			->fetch_bookmark_status()
			->build(true, true, false);

		return $this->display_topic($topic_id, $entity, $view, $redirect_url, $update_count, $topic_data_overwrite);
	}

	/**
	 * @param int $topic_id
	 * @param \blitze\content\model\entity\type $entity
	 * @param string $view	detail|print
	 * @param string $redirect_url
	 * @param array $update_count
	 * @param array $topic_data_overwrite
	 * @return array
	 * @throws \Exception
	 */
	protected function display_topic($topic_id, \blitze\content\model\entity\type $entity, $view, $redirect_url, array &$update_count, array $topic_data_overwrite)
	{
		$forum_id = $entity->get_forum_id();
		$content_type = $entity->get_content_name();

		$topics_data = $this->forum->get_topic_data();
		$post_data = $this->forum->get_post_data('first');
		$topic_tracking_info = $this->forum->get_topic_tracking_info($forum_id);
		$users_cache = $this->forum->get_posters_info();
		$attachments = $this->forum->get_attachments($forum_id);

		if (!sizeof($topics_data))
		{
			throw new \phpbb\exception\http_exception(404, 'NO_TOPIC');
		}

		$this->fields->prepare_to_show($entity, array_keys($topics_data), $entity->get_detail_fields(), $entity->get_detail_tpl(), 'detail')
			->set_display_mode($view);

		$topic_data = array_shift($topics_data);
		$post_data = array_shift($post_data[$topic_id]);
		$tpl_data = array_merge($topic_data,
			$this->fields->show($content_type, $topic_data, $post_data, $users_cache, $attachments, $update_count, $topic_tracking_info, $topic_data_overwrite, $redirect_url),
			$this->fields->get_topic_tools_data($topic_data)
		);

		$this->template->assign_vars(array_change_key_case($tpl_data, CASE_UPPER));
		$this->fields->show_attachments($attachments, $post_data['post_id']);
		$this->show_topic_blocks($entity, $topic_data, $post_data, array_shift($users_cache));
		$this->quickmod->show_tools($topic_data);
		$this->set_meta_tags($entity->get_detail_fields(), $tpl_data);

		return array_merge($topic_data, array(
			'topic_title'		=> $tpl_data['TOPIC_TITLE'],
			'total_comments'	=> $tpl_data['TOPIC_COMMENTS'],
			'topic_url'			=> $tpl_data['TOPIC_URL'],
		));
	}

	/**
	 * @param array $attachments
	 * @param int $post_id
	 * @return void
	 */
	protected function show_attachments(array $attachments, $post_id)
	{
		if (!empty($attachments[$post_id]))
		{
			foreach ($attachments[$post_id] as $attachment)
			{
				$this->template->assign_block_vars('attachment', array(
					'DISPLAY_ATTACHMENT'	=> $attachment)
				);
			}
		}
	}

	/**
	 * @param string $view_mode
	 * @param int $total_topics
	 * @param int $start
	 * @param int $items_per_page
	 * @param array $params
	 */
	protected function generate_pagination($view_mode, $total_topics, &$start, $items_per_page, array $params)
	{
		$params = array_filter($params);
		$route_type = $this->get_route_type($view_mode, $params);
		$start = $this->pagination->validate_start($start, $items_per_page, $total_topics);
		$this->pagination->generate_template_pagination(
			array(
				'routes' => array(
					'blitze_content_' . $route_type,
					'blitze_content_' . $route_type . '_page',
				),
				'params' => $params,
			),
			'pagination', 'page', $total_topics, $items_per_page, $start
		);
	}

	/**
	 * @param string $view_mode
	 * @param array $params
	 * @return string
	 */
	protected function get_route_type($view_mode, array $params)
	{
		$types = array(
			'show'		=> 'show',
			'summary'	=> join('_', array_filter(array(
				(!empty($params['type'])) ? 'type' : '',
				(!empty($params['filters'])) ? 'multi' : '',
				(!empty($params['filter_type'])) ? 'filter' : '',
			))),
		);

		return $types[$view_mode];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_filter_sql(array $filters, $forum_id)
	{
		$sql_array = array();

		/**
		 * Event to filter topics by field value e.g category/food
		 *
		 * @event blitze.content.view.filter
		 * @var mixed								forum_id		Forum id, if available
		 * @var array								filters			Filters
		 * @var array								sql_array		Array to modify sql query to get topics
		 */
		$vars = array('forum_id', 'filters', 'sql_array');
		extract($this->phpbb_dispatcher->trigger_event('blitze.content.view.filter', compact($vars)));

		return $sql_array;
	}

	/**
	 * @param \blitze\content\model\entity\type $entity
	 * @param array $topic_data
	 * @param array $post_data
	 * @param array $user_cache
	 * @return void
	 */
	protected function show_topic_blocks(\blitze\content\model\entity\type $entity, array $topic_data, array $post_data, array $user_cache)
	{
		$topic_blocks = $entity->get_topic_blocks();
		foreach ($topic_blocks as $service_name)
		{
			/** @var \blitze\content\services\topic\driver\block_interface $block */
			if ($block = $this->topic_blocks_factory->get($service_name))
			{
				$block->show_block($entity, $topic_data, $post_data, $user_cache);
			}
		}
	}

	/**
	 * @param int $forum_id
	 * @param int $topic_id
	 * @return void
	 */
	protected function set_mcp_url($forum_id, $topic_id = 0)
	{
		$this->template->assign_var('U_MCP', $this->helper->get_mcp_url($forum_id, $topic_id));
	}

	/**
	 * @param array $field_types
	 * @param array $topic_data
	 * @return void
	 */
	protected function set_meta_tags(array $field_types, array $topic_data)
	{
		if (isset($topic_data['FIELDS']))
		{
			$image_url = $this->get_field_value_by_type('image', $field_types, (array) $topic_data['FIELDS']['raw']);
			$description = $this->get_field_value_by_type('textarea', $field_types, (array) $topic_data['FIELDS']['raw']);

			// reduce to 20 words
			$description = implode(' ', array_slice(explode(' ', strip_tags($description)), 0, 19));

			$meta = "<meta name=\"description\" content=\"$description\" />\n";
			$meta .= "<meta name=\"twitter:card\" value=\"summary\">\n";
			$meta .= "<meta property=\"og:title\" content=\"{$topic_data['TOPIC_TITLE']}\" />\n";
			$meta .= "<meta property=\"og:type\" content=\"article\" />\n";
			$meta .= "<meta property=\"og:url\" content=\"{$topic_data['PERMA_LINK']}\" />\n";
			$meta .= "<meta property=\"og:image\" content=\"$image_url\" />\n";
			$meta .= "<meta property=\"og:description\" content=\"$description\" />";

			$this->template->assign_var('META', $meta);
		}
	}

	/**
	 * @param string $field_type
	 * @param array $field_types
	 * @param array $raw_data
	 * @return string
	 */
	protected function get_field_value_by_type($field_type, array $field_types, array $raw_data)
	{
		$field_name = array_shift(array_keys($field_types, $field_type));

		return isset($raw_data[$field_name]) ? $raw_data[$field_name] : '';
	}
}
