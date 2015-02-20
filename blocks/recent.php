<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\blocks;

class recent extends \primetime\core\services\blocks\driver\block
{
	/** @var \phpbb\config\db */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/* @var \primetime\content\services\displayer */
	protected $displayer;

	/** @var \primetime\core\services\forum\data */
	protected $forum;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string phpEx */
	protected $php_ext;

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
	 * @param \phpbb\user								$user				User object
	 * @param \primetime\content\services\displayer		$displayer			Content displayer object
	 * @param \primetime\core\services\forum\data		$forum				Forum Data object
	 * @param string									$phpbb_root_path	phpBB root path
	 * @param string									$php_ext			phpEx
	 */
	public function __construct(\phpbb\config\db $config, \phpbb\user $user, \primetime\content\services\displayer $displayer, \primetime\core\services\forum\data $forum, $phpbb_root_path, $php_ext)
	{
		$this->config = $config;
		$this->user = $user;
		$this->displayer = $displayer;
		$this->forum = $forum;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->sort_options = array(
			self::SORT_TOPIC_TIME	=> 'TOPIC_TIME',
			self::SORT_TOPIC_VIEWS	=> 'TOPIC_VIEWS',
			self::SORT_TOPIC_READ	=> 'LAST_READ_TIME',
		);
	}

	/**
	 * Block config
	 */
	public function get_config($settings)
	{
		if (!function_exists('select_content_type'))
		{
			include($this->phpbb_root_path . 'ext/primetime/content/blocks.' . $this->php_ext);
		}

		$content_types = $this->displayer->get_all_types();

		$default_type = '';
		$content_type_options = $field_options = array();
		if (sizeof($content_types))
		{
			foreach ($content_types as $type => $row)
			{
				$content_type_options[$type] = $row['content_langname'];
				foreach ($row['content_fields'] as $field => $fdata)
				{
					$field_options[$type][$field] = $fdata['field_label'];
				}
			}

			$row = array_shift($content_types);
			$default_type = $row['content_name'];
		}

		$topic_type_options = array(POST_NORMAL => 'POST_NORMAL', POST_STICKY => 'POST_STICKY', POST_ANNOUNCE => 'POST_ANNOUNCEMENT', POST_GLOBAL => 'POST_GLOBAL');
		$range_options = array('' => 'ALL_TIME', 'today' => 'TODAY', 'week' => 'THIS_WEEK', 'month' => 'THIS_MONTH', 'year' => 'THIS_YEAR');

		$content_type	= (isset($settings['content_type'])) ? $settings['content_type'] : $default_type;
		$fields		= (isset($settings['fields'])) ? $settings['fields'] : '';
		$topic_type	= (isset($settings['topic_type'])) ? $settings['topic_type'] : POST_NORMAL;
		$date_range	= (isset($settings['date_range'])) ? $settings['date_range'] : '';
		$sort_key	= (isset($settings['sort_key'])) ? $settings['sort_key'] : self::SORT_TOPIC_TIME;

		return array(
			'legend1'			=> $this->user->lang['DISPLAY'],
			'content_type'		=> array('lang' => 'CONTENT_TYPE', 'validate' => 'string', 'type' => 'select:1:toggable', 'function' => 'select_content_type', 'params' => array($content_type_options, $content_type), 'default' => $default_type, 'explain' => false),
			'max_chars'			=> array('lang' => 'FIELD_MAX_CHARS', 'validate' => 'int:0:255', 'type' => 'number:0:255', 'maxlength' => 3, 'explain' => false, 'default' => 125),
			'fields'			=> array('lang' => 'SELECT_FIELDS', 'validate' => 'string', 'type' => 'checkbox', 'params' => array($field_options, $fields), 'default' => '', 'explain' => true),
			'block_tpl'			=> array('lang' => 'TEMPLATE', 'validate' => 'string', 'type' => 'textarea:5:50', 'maxlength' => 255, 'explain' => false, 'default' => ''),

			'legend2'			=> $this->user->lang['SETTINGS'],
			'topic_type'		=> array('lang' => 'TOPIC_TYPE', 'validate' => 'int', 'type' => 'select', 'params' => array($topic_type_options, $topic_type), 'default' => POST_NORMAL, 'explain' => false),
			'max_topics'		=> array('lang' => 'MAX_TOPICS', 'validate' => 'int:0:20', 'type' => 'number:0:20', 'maxlength' => 2, 'explain' => false, 'default' => 5),
			'offset_start'		=> array('lang' => 'OFFSET_START', 'validate' => 'int:0:20', 'type' => 'number:0:20', 'maxlength' => 2, 'explain' => false, 'default' => 0),
			'topic_title_limit'	=> array('lang' => 'TOPIC_TITLE_LIMIT', 'validate' => 'int:0:255', 'type' => 'number:0:255', 'maxlength' => 3, 'explain' => false, 'default' => 25),
			'date_range'		=> array('lang' => 'LIMIT_POST_TIME', 'validate' => 'string', 'type' => 'select', 'params' => array($range_options, $date_range), 'default' => '', 'explain' => false),
			'sort_key'			=> array('lang' => 'SORT_BY', 'validate' => 'string', 'type' => 'select', 'params' => array($this->sort_options, $sort_key), 'default' => self::SORT_TOPIC_TIME, 'explain' => false),
			'enable_tracking'	=> array('lang' => 'ENABLE_TOPIC_TRACKING', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => false, 'default' => 1),
			'last_modified'		=> array('type' => 'hidden', 'default' => time()),
		);
	}

	public function display($bdata, $edit_mode = false)
	{
		$this->settings = $bdata['settings'];

		if (empty($this->settings['content_type']))
		{
			return array(
				'title'		=> '',
				'content'	=> ($edit_mode) ? $this->user->lang['NO_CONTENT_TYPE'] : '',
			);
		}

		$type = $this->settings['content_type'];
		$type_data = $this->displayer->get_type($type);
		$forum_id = $type_data['forum_id'];

		switch ($this->settings['topic_type'])
		{
			case POST_GLOBAL:
				$lang_var = 'CONTENT_GLOBAL_ANNOUNCEMENTS';
			break;
			case POST_ANNOUNCE:
				$lang_var = 'CONTENT_ANNOUNCEMENTS';
			break;
			case POST_STICKY:
				$lang_var = 'CONTENT_STICKY_POSTS';
			break;
			case POST_NORMAL:
			default:
				$lang_var = sprintf($this->user->lang['CONTENT_' . $this->sort_options[$this->settings['sort_key']]], $type_data['content_langname']);
			break;
		}

		$sort_keys = array(
			self::SORT_TOPIC_TIME	=> 't.topic_time',
			self::SORT_TOPIC_VIEWS	=> 't.topic_views',
			self::SORT_TOPIC_READ	=> 't.topic_last_view_time'
		);

		$range_info = $this->primetime->get_date_range($this->settings['date_range']);

		$this->forum->query()
			->fetch_forum($forum_id)
			->fetch_topic_type($this->settings['topic_type'])
			->fetch_tracking_info($this->settings['enable_tracking'])
			->fetch_date_range($range_info['start'], $range_info['stop'])
			->set_sorting($sort_order[$this->settings['sort_key']])
			->build();

		$topics_data = $this->forum->get_topic_data($this->settings['max_topics'], $this->settings['offset_start']);

		if (sizeof($topics_data) || $edit_mode !== false)
		{
			$fields = (!empty($this->settings['fields'])) ? $this->settings['fields'] : array();
			$posts_data = $this->forum->get_post_data('first');
			$users_cache = $this->forum->get_posters_info();
			$topic_tracking_info = $this->forum->get_topic_tracking_info($forum_id);
			$fields = array_intersect_key($type_data['field_types'], array_flip($fields));
			$tpl_name = $bdata['bid'] . '_block';

			$this->displayer->prepare_to_show($type, 'block', $fields, $this->settings['block_tpl'], $tpl_name, $this->settings['max_chars']);

			$update_count = array();
			$topics_data = array_values($topics_data);

			for ($i = 0, $size = sizeof($topics_data); $i < $size; $i++)
			{
				$topic_data	= $topics_data[$i];
				$topic_id	= $topic_data['topic_id'];
				$poster_id	= $topic_data['topic_poster'];
				$post_data	= array_shift($posts_data[$topic_id]);
				$title		= censor_text($topic_data['topic_title']);

				$tpl_data = $this->displayer->show($type, $title, $topic_data, $post_data, $users_cache[$poster_id], array(), $update_count, $topic_tracking_info);

				$this->ptemplate->assign_block_vars('topic_row', $tpl_data);
				unset($topics_data[$i], $post_data[$topic_id]);
			}

			$this->ptemplate->assign_vars(array(
				'S_IS_BOT'	=> $this->user->data['is_bot']
			));

			return array(
				'title'		=> $lang_var,
				'content'	=> $this->ptemplate->render_view('primetime/content', 'blocks/recent_content.html', 'recent_content_block')
			);
		}
	}
}
