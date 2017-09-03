<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\blocks;

class recent extends \blitze\sitemaker\services\blocks\driver\block
{
	/** @var \phpbb\config\db */
	protected $config;

	/** @var\phpbb\language\language */
	protected $language;

	/** @var \blitze\content\services\types */
	protected $content_types;

	/* @var \blitze\content\services\fields */
	protected $fields;

	/** @var \blitze\sitemaker\services\date_range */
	protected $date_range;

	/** @var \blitze\sitemaker\services\forum\data */
	protected $forum;

	/** @var  array */
	protected $settings;

	/** @var array */
	protected $sort_options = array();

	/** @var */
	const SORT_TOPIC_TIME = 0;

	/** @var */
	const SORT_TOPIC_VIEWS = 1;

	/** @var */
	const SORT_TOPIC_READ = 2;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\db							$config				Config object
	 * @param \phpbb\language\language					$language			Language Object
	 * @param \blitze\content\services\types			$content_types		Content types object
	 * @param \blitze\content\services\fields			$fields				Content fields object
	 * @param \blitze\sitemaker\services\date_range		$date_range			Date Range Object
	 * @param \blitze\sitemaker\services\forum\data		$forum				Forum Data object
	 */
	public function __construct(\phpbb\config\db $config, \phpbb\language\language $language, \blitze\content\services\types $content_types, \blitze\content\services\fields $fields, \blitze\sitemaker\services\date_range $date_range, \blitze\sitemaker\services\forum\data $forum)
	{
		$this->config = $config;
		$this->language = $language;
		$this->content_types = $content_types;
		$this->fields = $fields;
		$this->date_range = $date_range;
		$this->forum = $forum;

		$this->sort_options = array(
			self::SORT_TOPIC_TIME	=> 'TOPIC_TIME',
			self::SORT_TOPIC_VIEWS	=> 'TOPIC_VIEWS',
			self::SORT_TOPIC_READ	=> 'LAST_READ_TIME',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_config(array $settings)
	{
		$content_type_options = $field_options = array();
		$default_type = $this->get_content_type_options($content_type_options, $field_options);

		return array(
			'legend1'			=> 'DISPLAY',
			'content_type'		=> array('lang' => 'CONTENT_TYPE', 'validate' => 'string', 'type' => 'select:1:toggable', 'object' => $this, 'method' => 'select_content_type', 'options' => $content_type_options, 'default' => $default_type, 'explain' => false),
			'max_chars'			=> array('lang' => 'FIELD_MAX_CHARS', 'validate' => 'int:0:255', 'type' => 'number:0:255', 'maxlength' => 3, 'explain' => false, 'default' => 125),
			'fields'			=> array('lang' => 'SELECT_FIELDS', 'validate' => 'string', 'type' => 'checkbox', 'options' => $field_options, 'default' => array(), 'explain' => true),
			'block_tpl'			=> array('lang' => 'TEMPLATE', 'validate' => 'string', 'type' => 'textarea:5:50', 'maxlength' => 255, 'explain' => false, 'default' => ''),

			'legend2'			=> 'SETTINGS',
			'topic_type'		=> array('lang' => 'TOPIC_TYPE', 'validate' => 'string', 'type' => 'select', 'options' => $this->get_topic_type_options(), 'default' => POST_NORMAL, 'explain' => false),
			'max_topics'		=> array('lang' => 'MAX_TOPICS', 'validate' => 'int:0:20', 'type' => 'number:0:20', 'maxlength' => 2, 'explain' => false, 'default' => 5),
			'offset_start'		=> array('lang' => 'OFFSET_START', 'validate' => 'int:0:20', 'type' => 'number:0:20', 'maxlength' => 2, 'explain' => false, 'default' => 0),
			'topic_title_limit'	=> array('lang' => 'TOPIC_TITLE_LIMIT', 'validate' => 'int:0:255', 'type' => 'number:0:255', 'maxlength' => 3, 'explain' => false, 'default' => 25),
			'date_range'		=> array('lang' => 'LIMIT_POST_TIME', 'validate' => 'string', 'type' => 'select', 'options' => $this->get_range_options(), 'default' => '', 'explain' => false),
			'sort_key'			=> array('lang' => 'SORT_BY', 'validate' => 'string', 'type' => 'select', 'options' => $this->sort_options, 'default' => self::SORT_TOPIC_TIME, 'explain' => false),
			'enable_tracking'	=> array('lang' => 'ENABLE_TOPIC_TRACKING', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => false, 'default' => 1),
			'last_modified'		=> array('type' => 'hidden', 'default' => time()),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function display(array $bdata, $edit_mode = false)
	{
		$this->settings = $bdata['settings'];
		$type = $this->settings['content_type'];

		if (empty($this->settings['content_type']) || false === $entity = $this->content_types->get_type($type, false))
		{
			return array(
				'title'		=> '',
				'content'	=> ($edit_mode) ? $this->language->lang('NO_CONTENT_TYPE') : '',
			);
		}

		$forum_id = $entity->get_forum_id();
		$this->build_query($forum_id);

		return $this->show_topics($edit_mode, $bdata['bid'], $forum_id, $type, $entity);
	}

	/**
	 * @param int $forum_id
	 * @return void
	 */
	protected function build_query($forum_id)
	{
		$sort_keys = array(
			self::SORT_TOPIC_TIME	=> 't.topic_time',
			self::SORT_TOPIC_VIEWS	=> 't.topic_views',
			self::SORT_TOPIC_READ	=> 't.topic_last_view_time'
		);

		$range_info = $this->date_range->get($this->settings['date_range']);

		$this->forum->query($this->settings['enable_tracking'])
			->fetch_forum($forum_id)
			->fetch_topic_type(array($this->settings['topic_type']))
			->fetch_date_range($range_info['start'], $range_info['stop'])
			->set_sorting($sort_keys[$this->settings['sort_key']])
			->build(true, true, false);
	}

	/**
	 * @param bool $edit_mode
	 * @param int $block_id
	 * @param int $forum_id
	 * @param string $type
	 * @param \blitze\content\model\entity\type $entity
	 * @return array
	 * @internal param int $block_id
	 */
	protected function show_topics($edit_mode, $block_id, $forum_id, $type, \blitze\content\model\entity\type $entity)
	{
		$topics_data = $this->forum->get_topic_data($this->settings['max_topics'], $this->settings['offset_start']);
		$posts_data = $this->forum->get_post_data('first');

		$title = $content = '';
		if (sizeof($posts_data) || $edit_mode !== false)
		{
			$users_cache = $this->forum->get_posters_info();
			$attachments = $this->forum->get_attachments($forum_id);
			$topic_tracking_info = $this->forum->get_topic_tracking_info($forum_id);
			$block_fields = $this->get_block_fields($entity->get_field_types());

			$this->fields->prepare_to_show($entity, array_keys($topics_data), $block_fields, $this->settings['block_tpl'], 'block', $block_id . '_block');

			$update_count = array();
			foreach ($posts_data as $topic_id => $posts)
			{
				$post_data	= array_shift($posts);
				$topic_data	= $topics_data[$topic_id];
				$this->ptemplate->assign_block_vars('topicrow', $this->fields->show($type, $topic_data, $post_data, $users_cache, $attachments, $update_count, $topic_tracking_info));
			}
			unset($topics_data, $posts_data, $users_cache, $attachments, $topic_tracking_info);

			$title = $this->get_block_title($entity->get_content_langname());
			$content = $this->ptemplate->render_view('blitze/content', 'blocks/recent_content.html', 'recent_content_block');
		}

		return array(
			'title'		=> $title,
			'content'	=> $content,
		);
	}

	/**
	 * @param string $content_langname
	 * @return string
	 */
	protected function get_block_title($content_langname)
	{
		$topic_types = array(
			POST_GLOBAL		=> 'CONTENT_GLOBAL_ANNOUNCEMENTS',
			POST_ANNOUNCE	=> 'CONTENT_ANNOUNCEMENTS',
			POST_STICKY		=> 'CONTENT_STICKY_POSTS',
		);

		return (isset($topic_types[$this->settings['topic_type']])) ? $topic_types[$this->settings['topic_types']] :  $this->language->lang('CONTENT_' . $this->sort_options[$this->settings['sort_key']], $content_langname);
	}

	/**
	 * @param array $field_types
	 * @return array
	 */
	protected function get_block_fields(array $field_types)
	{
		$block_fields = (!empty($this->settings['fields'])) ? $this->settings['fields'] : array();
		return array_intersect_key($field_types, array_flip($block_fields));
	}

	/**
	 * @param array $fields
	 * @param array $fields_data
	 * @return array
	 */
	protected function get_block_fields_data(array $fields, array $fields_data)
	{
		$textarea_fields = array_keys($fields, 'textarea');

		foreach ($textarea_fields as $field)
		{
			$fields_data[$field]['field_props']['max_chars'] = $this->settings['max_chars'];
		}

		return $fields_data;
	}

	/**
	 * @param array $type_options
	 * @param array $field_options
	 * @return array
	 */
	protected function get_content_type_options(array &$type_options, array &$field_options)
	{
		$content_types = $this->content_types->get_all_types();

		$type_options = $field_options = array();
		foreach ($content_types as $type => $entity)
		{
			/** @var \blitze\content\model\entity\type $entity */
			$type_options[$type] = $entity->get_content_langname();

			$content_fields = $entity->get_content_fields();
			foreach ($content_fields as $field => $fdata)
			{
				$field_options[$type][$field] = $fdata['field_label'];
			}
		}
		reset($content_types);

		return key($content_types);
	}

	/**
	 * @param array $content_types
	 * @param string $type
	 * @return string
	 */
	public function select_content_type(array $content_types, $type)
	{
		$html = '';
		foreach ($content_types as $value => $title)
		{
			$selected = ($type == $value) ? ' selected="selected"' : '';
			$html .= '<option value="' . $value . '"' . $selected . ' data-toggle-setting="#fields-col-' . $value . '">' . $title . '</option>';
		}

		return $html;
	}

	/**
	 * @return array
	 */
	protected function get_topic_type_options()
	{
		return array(
			POST_NORMAL		=> 'POST_NORMAL',
			POST_STICKY		=> 'POST_STICKY',
			POST_ANNOUNCE	=> 'POST_ANNOUNCEMENT',
			POST_GLOBAL		=> 'POST_GLOBAL',
		);
	}

	/**
	 * @return array
	 */
	protected function get_range_options()
	{
		return array(
			''		=> 'ALL_TIME',
			'today'	=> 'TODAY',
			'week'	=> 'THIS_WEEK',
			'month'	=> 'THIS_MONTH',
			'year'	=> 'THIS_YEAR',
		);
	}
}
