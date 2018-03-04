<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\views\driver;

class portal extends base_view
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \blitze\content\services\types */
	protected $content_types;

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
	 * @param \phpbb\config\config								$config					Config object
	 * @param \blitze\content\services\types					$content_types			Content types object
	*/
	public function __construct(\phpbb\event\dispatcher_interface $phpbb_dispatcher, \phpbb\language\language $language, \phpbb\pagination $pagination, \phpbb\template\template $template, \blitze\content\services\fields $fields, \blitze\sitemaker\services\forum\data $forum, \blitze\content\services\helper $helper, \blitze\content\services\quickmod $quickmod, \blitze\content\services\topic\blocks_factory $topic_blocks_factory, \phpbb\config\config $config, \blitze\content\services\types $content_types)
	{
		parent::__construct($phpbb_dispatcher, $language, $pagination, $template, $fields, $forum, $helper, $quickmod, $topic_blocks_factory);

		$this->config = $config;
		$this->content_types = $content_types;
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'portal';
	}

	/**
	 * @inheritdoc
	 */
	public function get_langname()
	{
		return 'CONTENT_DISPLAY_PORTAL';
	}

	/**
	 * @inheritdoc
	 */
	public function get_index_template()
	{
		return 'views/portal.html';
	}

	/**
	 * {@inheritdoc}
	 * @param array $filters
	 * @param integer $page
	 */
	public function render_filter(array $filters, $page)
	{
		$this->build_index_query($filters);

		$max_update_time = 0;
		$total_topics = $this->forum->get_topics_count();
		$items_per_page = (int) $this->config['topics_per_page'];
		$start = ($page - 1) * $items_per_page;
		$topics_data = $this->forum->get_topic_data($items_per_page, $start);
		$this->generate_pagination('summary', $total_topics, $start, $items_per_page, $this->get_filter_params($filters));

		if (sizeof($topics_data))
		{
			$posts_data = $this->forum->get_post_data('first');
			$users_cache = $this->forum->get_posters_info();

			$forums_data = array();
			foreach ($posts_data as $topic_id => $row)
			{
				$post = current($row);
				$forums_data[$post['forum_id']][$topic_id] = $post;
			}

			$this->display_filtered_topics($forums_data, $topics_data, $users_cache, $max_update_time);
		}
		return $max_update_time;
	}

	/**
	 * @param array $forums_data
	 * @param array $topics_data
	 * @param array $users_cache
	 * @param int $max_update_time
	 * @return void
	 */
	protected function display_filtered_topics(array $forums_data, array $topics_data, array $users_cache, &$max_update_time)
	{
		$update_count = array();
		foreach ($forums_data as $forum_id => $posts_data)
		{
			$content_type = $this->content_types->get_forum_type($forum_id);
			if (!$content_type || !($entity = $this->content_types->get_type($content_type)))
			{
				continue;
			}

			$this->fields->prepare_to_show($entity, array_keys($posts_data), $entity->get_summary_fields(), $entity->get_summary_tpl(), 'summary');

			$topic_tracking_info = $this->forum->get_topic_tracking_info($forum_id);
			$attachments = $this->forum->get_attachments($forum_id);

			$this->template->assign_vars($entity->to_array());
			foreach ($posts_data as $topic_id => $post_data)
			{
				$topic_data	= $topics_data[$topic_id];
				$topic_data = $this->fields->show($content_type, $topic_data, $post_data, $users_cache, $attachments, $update_count, $topic_tracking_info);

				$this->template->assign_block_vars('topicrow', $topic_data);
				$max_update_time = max($max_update_time, $topic_data['UPDATED']);
			}
		}
	}

	/**
	 * @param array $filters
	 * @return array
	 */
	protected function get_filter_params(array $filters)
	{
		if (sizeof(array_keys($filters)) > 1)
		{
			return array('filters' => $filters);
		}
		else
		{
			$key = key($filters);
			return array(
				'filter_type'	=> $key,
				'filter_value'	=> current($filters[$key]),
			);
		}
	}
}
