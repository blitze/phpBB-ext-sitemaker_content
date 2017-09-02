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

	/**
	 * Constructor
	 *
	 * @param \phpbb\event\dispatcher_interface			$phpbb_dispatcher	Event dispatcher object
	 * @param \phpbb\language\language					$language			Language Object
	 * @param \phpbb\pagination							$pagination			Pagination object
	 * @param \phpbb\template\template					$template			Template object
	 * @param \blitze\content\services\fields			$fields				Content fields object
	 * @param \blitze\sitemaker\services\forum\data		$forum				Forum Data object
	 * @param \blitze\content\services\helper			$helper				Content helper object
	 * @param \blitze\content\services\quickmod			$quickmod			Quick moderator tools
	*/
	public function __construct(\phpbb\event\dispatcher_interface $phpbb_dispatcher, \phpbb\language\language $language, \phpbb\pagination $pagination, \phpbb\template\template $template, \blitze\content\services\fields $fields, \blitze\sitemaker\services\forum\data $forum, \blitze\content\services\helper $helper, \blitze\content\services\quickmod $quickmod)
	{
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->language = $language;
		$this->pagination = $pagination;
		$this->template = $template;
		$this->fields = $fields;
		$this->forum = $forum;
		$this->helper = $helper;
		$this->quickmod = $quickmod;
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
	public function build_index_query($filter_type, $filter_value, $forum_id = '')
	{
		$sql_array = $this->get_filter_sql($filter_type, $filter_value, $forum_id);

		$this->forum->query()
			->fetch_forum($forum_id)
			->fetch_custom($sql_array)
			->set_sorting('t.topic_time')
			->build(true, false, false);
	}

	/**
	 * {@inheritdoc}
	 * @param array $topic_data_overwrite
	 */
	public function render_index(\blitze\content\model\entity\type $entity, $page, $filter_type, $filter_value, array $topic_data_overwrite = array())
	{
		$content_type = $entity->get_content_name();
		$items_per_page = $entity->get_items_per_page();
		$forum_id = $entity->get_forum_id();
		$start = ($page - 1) * $items_per_page;

		$this->build_index_query($filter_type, $filter_value, $forum_id);
		$this->set_mcp_url($forum_id);

		if ($entity->get_show_pagination())
		{
			$total_topics = $this->forum->get_topics_count();
			$this->generate_pagination('summary', $total_topics, $start, $items_per_page, array(
				'type'			=> $content_type,
				'filter_type'	=> $filter_type,
				'filter_value'	=> $filter_value,
			));
		}

		$this->display_topics($entity, $items_per_page, $start, $topic_data_overwrite);
	}

	/**
	 * @param \blitze\content\model\entity\type $entity
	 * @param int $items_per_page
	 * @param int $start
	 * @param array $topic_data_overwrite
	 * @return void
	 */
	protected function display_topics(\blitze\content\model\entity\type $entity, $items_per_page = 1, $start = 0, array $topic_data_overwrite = array())
	{
		$content_type = $entity->get_content_name();
		$topics_data = $this->forum->get_topic_data($items_per_page, $start);
		$posts_data = $this->forum->get_post_data('first');
		$topic_tracking_info = $this->forum->get_topic_tracking_info($entity->get_forum_id());
		$users_cache = $this->forum->get_posters_info();
		$attachments = $this->forum->get_attachments($entity->get_forum_id());

		$this->fields->prepare_to_show($entity, array_keys($topics_data), $entity->get_summary_tags(), $entity->get_summary_tpl(), 'summary');

		$update_count = array();
		foreach ($posts_data as $topic_id => $posts)
		{
			$post_data	= array_shift($posts);
			$topic_data	= $topics_data[$topic_id];

			$this->template->assign_block_vars('topicrow', array_merge(
				$this->fields->show($content_type, $topic_data, $post_data, $users_cache, $attachments, $update_count, $topic_tracking_info),
				$topic_data_overwrite
			));
		}
		unset($topics_data, $posts_data, $users_cache, $attachments, $topic_tracking_info);
	}

	/**
	 * {@inheritdoc}
	 * @param array $topic_data_overwrite
	 */
	public function render_detail(\blitze\content\model\entity\type $entity, $topic_id, array &$update_count, array $topic_data_overwrite = array())
	{
		$this->language->add_lang('viewtopic');
		$this->language->add_lang('content', 'blitze/content');
		$this->set_mcp_url($entity->get_forum_id(), $topic_id);

		$this->forum->query()
			->fetch_topic($topic_id)
			->fetch_watch_status()
			->fetch_bookmark_status()
			->build(true, true, false);

		return $this->display_topic($topic_id, $entity, $update_count, $topic_data_overwrite);
	}

	/**
	 * @param int $topic_id
	 * @param \blitze\content\model\entity\type $entity
	 * @param array $update_count
	 * @param array $topic_data_overwrite
	 * @return array
	 * @throws \Exception
	 */
	protected function display_topic($topic_id, \blitze\content\model\entity\type $entity, array &$update_count, array $topic_data_overwrite)
	{
		$forum_id = $entity->get_forum_id();
		$content_type = $entity->get_content_name();
		$content_langname = $entity->get_content_name();

		$topics_data = $this->forum->get_topic_data();
		$post_data = $this->forum->get_post_data('first');
		$topic_tracking_info = $this->forum->get_topic_tracking_info($forum_id);
		$users_cache = $this->forum->get_posters_info();
		$attachments = $this->forum->get_attachments($forum_id);

		if (!sizeof($post_data))
		{
			throw new \Exception($this->language->lang('CONTENT_NO_EXIST'));
		}

		$this->fields->prepare_to_show($entity, array_keys($topics_data), $entity->get_detail_tags(), $entity->get_detail_tpl(), 'detail');

		$topic_data = array_shift($topics_data);
		$post_data = array_shift($post_data[$topic_id]);
		$tpl_data = array_merge($topic_data,
			$this->fields->show($content_type, $topic_data, $post_data, $users_cache, $attachments, $update_count, $topic_tracking_info, $topic_data_overwrite),
			$this->fields->get_topic_tools_data($topic_data)
		);

		$this->template->assign_vars(array_change_key_case($tpl_data, CASE_UPPER));
		$this->fields->show_attachments($attachments, $post_data['post_id']);
		$this->show_author_info($forum_id, $post_data['poster_id'], $content_langname, $users_cache[$post_data['poster_id']], $entity->get_show_poster_info());
		$this->show_author_contents($topic_data, $content_type, $content_langname, $entity->get_show_poster_contents());
		$this->quickmod->show_tools($topic_data);

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
		$route = ($view_mode === 'summary') ? 'index' : 'show';
		$start = $this->pagination->validate_start($start, $items_per_page, $total_topics);
		$this->pagination->generate_template_pagination(
			array(
				'routes' => array(
					'blitze_content_' . $route,
					'blitze_content_' . $route . '_page',
				),
				'params' => $params,
			),
			'pagination', 'page', $total_topics, $items_per_page, $start
		);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_filter_sql($filter_type, $filter_value, $forum_id)
	{
		$sql_array = array();

		/**
		 * Event to filter topics by field value e.g category/food
		 *
		 * @event blitze.content.view.filter
		 * @var mixed								forum_id		Forum id, if available
		 * @var string								filter_type		Filter type e.g category|tag
		 * @var string								filter_value	The filter value e.g food
		 * @var array								sql_array		Array to modify sql query to get topics
		 */
		$vars = array('forum_id', 'filter_type', 'filter_value', 'sql_array');
		extract($this->phpbb_dispatcher->trigger_event('blitze.content.view.filter', compact($vars)));

		return $sql_array;
	}

	/**
	 * @param int $forum_id
	 * @param int $poster_id
	 * @param string $content_langname
	 * @param array $user_cache
	 * @param bool $show_author_info
	 * @return void
	 */
	protected function show_author_info($forum_id, $poster_id, $content_langname, array $user_cache, $show_author_info)
	{
		if ($show_author_info)
		{
			$this->forum->query()
				->fetch_forum($forum_id)
				->fetch_topic_poster($poster_id)
				->build(true, true, false);
			$user_content_topics = $this->forum->get_topics_count();

			$this->template->assign_vars(array_merge($user_cache, array(
				'S_USER_INFO'			=> true,
				'L_USER_ABOUT'			=> $this->language->lang('AUTHOR_INFO_EXPLAIN', $user_cache['username_full'], $user_cache['joined'], $user_content_topics, $content_langname, $user_cache['posts']),
				'L_USER_VIEW_ALL'		=> $this->language->lang('VIEW_AUTHOR_CONTENTS', $content_langname, $user_cache['username']),
				'L_SEARCH_USER_POSTS'	=> $this->language->lang('SEARCH_USER_POSTS', $user_cache['username']),
				'U_SEARCH_CONTENTS'		=> $this->helper->get_search_users_posts_url($forum_id, $user_cache['username']),
			)));
		}
	}

	/**
	 * @param array $topic_data
	 * @param string $content_type
	 * @param string $content_langname
	 * @param bool $show_author_contents
	 * @return void
	 */
	protected function show_author_contents($topic_data, $content_type, $content_langname, $show_author_contents)
	{
		if ($show_author_contents)
		{
			$this->forum->query()
				->fetch_forum($topic_data['forum_id'])
				->fetch_topic_poster($topic_data['topic_poster'])
				->fetch_custom(array(
					'WHERE' => array('t.topic_id <> ' . (int) $topic_data['topic_id'])
				))->build(true, true, false);

			$topics_data = $this->forum->get_topic_data(5);
			$topic_tracking_info = $this->forum->get_topic_tracking_info($topic_data['forum_id']);

			$this->template->assign_var('AUTHOR_CONTENT', $this->language->lang('AUTHOR_CONTENTS', $content_langname, $topic_data['topic_first_poster_name']));

			foreach ($topics_data as $row)
			{
				$this->template->assign_block_vars('content', $this->fields->get_min_topic_info($content_type, $row, $topic_tracking_info));
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
}
