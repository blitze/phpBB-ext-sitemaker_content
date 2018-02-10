<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2018 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\topic\driver;

class author_info implements block_interface
{
	/** @var\phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \blitze\sitemaker\services\forum\data */
	protected $forum;

	/* @var \blitze\content\services\helper */
	protected $helper;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language					$language			Language Object
	 * @param \phpbb\template\template					$template			Template object
	 * @param \blitze\sitemaker\services\forum\data		$forum				Forum Data object
	 * @param \blitze\content\services\helper			$helper				Content helper object
	*/
	public function __construct(\phpbb\language\language $language, \phpbb\template\template $template, \blitze\sitemaker\services\forum\data $forum, \blitze\content\services\helper $helper)
	{
		$this->language = $language;
		$this->template = $template;
		$this->forum = $forum;
		$this->helper = $helper;
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'author_info';
	}

	/**
	 * @inheritdoc
	 */
	public function get_langname()
	{
		return 'AUTHOR_INFO';
	}

	/**
	 * @param \blitze\content\model\entity\type $entity
	 * @param array $topic_data
	 * @param array $post_data
	 * @param array $user_cache
	 * @return void
	 */
	public function show_block(\blitze\content\model\entity\type $entity, array $topic_data, array $post_data, array $user_cache)
	{
		$forum_id = $topic_data['forum_id'];
		$content_langname = $entity->get_content_langname();

		$this->forum->query()
			->fetch_forum($forum_id)
			->fetch_topic_poster($topic_data['topic_poster'])
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
