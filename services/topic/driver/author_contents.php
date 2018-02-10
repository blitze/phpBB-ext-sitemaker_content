<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2018 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\topic\driver;

class author_contents implements block_interface
{
	/** @var\phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/* @var \blitze\content\services\fields */
	protected $fields;

	/** @var \blitze\sitemaker\services\forum\data */
	protected $forum;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language					$language			Language Object
	 * @param \phpbb\template\template					$template			Template object
	 * @param \blitze\content\services\fields			$fields				Content fields object
	 * @param \blitze\sitemaker\services\forum\data		$forum				Forum Data object
	*/
	public function __construct(\phpbb\language\language $language, \phpbb\template\template $template, \blitze\content\services\fields $fields, \blitze\sitemaker\services\forum\data $forum)
	{
		$this->language = $language;
		$this->template = $template;
		$this->fields = $fields;
		$this->forum = $forum;
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'author_contents';
	}

	/**
	 * @inheritdoc
	 */
	public function get_langname()
	{
		return 'AUTHOR_CONTENTS';
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
		$this->forum->query()
			->fetch_forum($topic_data['forum_id'])
			->fetch_topic_poster($topic_data['topic_poster'])
			->fetch_custom(array(
				'WHERE' => array('t.topic_id <> ' . (int) $topic_data['topic_id'])
			))->build(true, true, false);

		$topics_data = $this->forum->get_topic_data(5);
		$topic_tracking_info = $this->forum->get_topic_tracking_info($topic_data['forum_id']);
		$content_type = $entity->get_content_name();

		$topics = array();
		foreach ($topics_data as $row)
		{
			$topics[] = $this->fields->get_min_topic_info($content_type, $row, $topic_tracking_info);
		}

		$this->template->assign_block_vars('topic_blocks', array(
			'TITLE'		=> $this->language->lang('AUTHOR_CONTENTS', $entity->get_content_langname(), $topic_data['topic_first_poster_name']),
			'TPL_NAME'	=> '@blitze_content/author_contents.html',
			'TOPICS'	=> $topics,
		));
	}
}
