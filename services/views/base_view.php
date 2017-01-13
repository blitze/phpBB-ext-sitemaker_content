<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\views;

abstract class base_view implements views_interface
{
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

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language					$language			Language Object
	 * @param \phpbb\pagination							$pagination			Pagination object
	 * @param \phpbb\template\template					$template			Template object
	 * @param \blitze\content\services\fields			$fields				Content fields object
	 * @param \blitze\sitemaker\services\forum\data		$forum				Forum Data object
	 * @param \blitze\content\services\helper			$helper				Content helper object
	 * @param string									$phpbb_root_path	Path to the phpbb includes directory.
	 * @param string									$php_ext			php file extension
	*/
	public function __construct(\phpbb\language\language $language, \phpbb\pagination $pagination, \phpbb\template\template $template, \blitze\content\services\fields $fields, \blitze\sitemaker\services\forum\data $forum, \blitze\content\services\helper $helper, $phpbb_root_path, $php_ext)
	{
		$this->language = $language;
		$this->pagination = $pagination;
		$this->template = $template;
		$this->fields = $fields;
		$this->forum = $forum;
		$this->helper = $helper;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_detail_template()
	{
		return 'views/content_show.html';
	}

	/**
	 * {@inheritdoc}
	 */
	public function build_index_query($forum_id)
	{
		$this->forum->query()
			->fetch_forum($forum_id)
			->set_sorting('t.topic_time')
			->build(true, true, false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_index(\blitze\content\model\entity\type $entity, $page, $filter_type, $filter_value)
	{
		$forum_id = $entity->get_forum_id();
		$content_type = $entity->get_content_name();
		$items_per_page = $entity->get_items_per_page();
		$start = ($page - 1) * $items_per_page;

		$this->build_index_query($forum_id);

		if ($entity->get_show_pagination())
		{
			$total_topics = $this->forum->get_topics_count();
			$this->generate_pagination('summary', $total_topics, $start, $items_per_page, array('type' => $content_type));
		}

		$this->fields->prepare_to_show($content_type, 'summary', $entity->get_summary_tags(), $entity->get_content_fields(), $entity->get_summary_tpl());
		$this->display_topics($content_type, $forum_id, $items_per_page, $start, $entity->get_allow_comments());
	}

	/**
	 * @param string $content_type
	 * @param int $forum_id
	 * @param int $items_per_page
	 * @param int $start
	 * @param bool $allow_comments
	 * @param array $topic_data_overwrite
	 * @return void
	 */
	protected function display_topics($content_type, $forum_id, $items_per_page = 1, $start = 0, $allow_comments, $topic_data_overwrite = array())
	{
		$topics_data = $this->forum->get_topic_data($items_per_page, $start);
		$posts_data = $this->forum->get_post_data('first');
		$topic_tracking_info = $this->forum->get_topic_tracking_info($forum_id);
		$users_cache = $this->forum->get_posters_info();
		$attachments = $this->forum->get_attachments($forum_id);

		$update_count = array();
		$topics_data = array_values($topics_data);

		for ($i = 0, $size = sizeof($topics_data); $i < $size; $i++)
		{
			$topic_data	= $topics_data[$i];
			$topic_id	= $topic_data['topic_id'];
			$post_data	= array_shift($posts_data[$topic_id]);

			$tpl_data = array_merge(
				$this->fields->show($content_type, $topic_data, $post_data, $users_cache, $attachments, $update_count, $topic_tracking_info),
				$topic_data_overwrite
			);

			$this->template->assign_block_vars('topic_row', $tpl_data);
			unset($topics_data[$i], $post_data[$topic_id]);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_detail(\blitze\content\model\entity\type $entity, $topic_id, array &$update_count, $mode = '', array $topic_data_overwrite = array())
	{
		$this->language->add_lang('viewtopic');
		$this->language->add_lang('content', 'blitze/content');

		$forum_id = $entity->get_forum_id();
		$content_type = $entity->get_content_name();

		$this->forum->query()
			->fetch_forum($forum_id)
			->fetch_topic($topic_id)
			->build(true, true, false);

		$this->fields->prepare_to_show($content_type, 'detail', $entity->get_detail_tags(), $entity->get_content_fields(), $entity->get_detail_tpl());
		return $this->display_topic($mode, $content_type, $entity->get_content_langname(), $forum_id, $topic_id, $entity->get_allow_comments(), $entity->get_show_poster_info(), $entity->get_show_poster_contents(), $update_count, $topic_data_overwrite);
	}

	/**
	 * @param string $mode
	 * @param string $content_type
	 * @param string $content_langname
	 * @param int $forum_id
	 * @param int $topic_id
	 * @param bool $allow_comments
	 * @param bool $show_author_info
	 * @param bool $show_author_contents
	 * @param array $update_count
	 * @param array $topic_data_overwrite
	 * @return array
	 * @throws \Exception
	 */
	protected function display_topic($mode, $content_type, $content_langname, $forum_id, $topic_id, $allow_comments, $show_author_info, $show_author_contents, array &$update_count, array $topic_data_overwrite)
	{
		$topic_data = $this->forum->get_topic_data();
		$post_data = $this->forum->get_post_data('first');
		$topic_tracking_info = $this->forum->get_topic_tracking_info($forum_id);
		$users_cache = $this->forum->get_posters_info();
		$attachments = $this->forum->get_attachments($forum_id);

		if (!sizeof($topic_data))
		{
			throw new \Exception($this->language->lang('CONTENT_NO_EXIST'));
		}

		$topic_data = array_shift($topic_data);
		$post_data = array_shift($post_data[$topic_id]);

		$tpl_data = array_merge(
			$topic_data,
			$this->fields->show($content_type, $topic_data, $post_data, $users_cache, $attachments, $update_count, $topic_tracking_info, $topic_data_overwrite)
		);

		$this->template->assign_vars(array_change_key_case($tpl_data, CASE_UPPER));
		$this->show_author_info($forum_id, $post_data['poster_id'], $content_langname, $users_cache[$post_data['poster_id']], $show_author_info);
		$this->show_author_contents($topic_data, $content_type, $content_langname, $show_author_contents);

		return array_merge($topic_data, array(
			'topic_title'		=> $tpl_data['TOPIC_TITLE'],
			'total_comments'	=> $tpl_data['TOPIC_COMMENTS'],
			'topic_url'			=> $tpl_data['TOPIC_URL'],
		));
	}

	/**
	 * @param string $view_mode
	 * @param int $total_topics
	 * @param int $start
	 * @param int $items_per_page
	 * @param array $params
	 */
	public function generate_pagination($view_mode, $total_topics, &$start, $items_per_page, array $params)
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
				'U_SEARCH_CONTENTS'		=> append_sid("{$this->phpbb_root_path}search.{$this->php_ext}", "author={$user_cache['username']}&amp;" . urlencode('fid[]') . "=$forum_id&amp;sc=0&amp;sf=titleonly&amp;sr=topics")
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
}
