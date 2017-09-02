<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\blocks;

class archive extends \blitze\sitemaker\services\blocks\driver\block
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \blitze\content\services\types */
	protected $content_types;

	/** @var \blitze\sitemaker\services\forum\data */
	protected $forum;

	/** @var integer */
	protected $cache_time;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface			$db					Database object
	 * @param \phpbb\controller\helper					$helper				Helper object
	 * @param \blitze\content\services\types			$content_types		Content types object
	 * @param \blitze\sitemaker\services\forum\data		$forum				Forum Data object
	 * @param integer									$cache_time			Cache results for 3 hours by default
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \blitze\content\services\types $content_types, \blitze\sitemaker\services\forum\data $forum, $cache_time)
	{
		$this->db = $db;
		$this->helper = $helper;
		$this->content_types = $content_types;
		$this->forum = $forum;
		$this->cache_time = $cache_time;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_config(array $settings)
	{
		$content_type_options = $this->get_content_type_options();
		$month_dsp_options = array('short' => 'MONTH_FORMAT_SHORT', 'long' => 'MONTH_FORMAT_LONG');

		return array(
			'legend1'		=> 'SETTINGS',
			'forum_id'		=> array('lang' => 'CONTENT_TYPE', 'validate' => 'string', 'type' => 'select', 'options' => $content_type_options, 'default' => 0, 'explain' => false),
			'show_count'	=> array('lang' => 'SHOW_TOPICS_COUNT', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => false, 'default' => 0),
			'all_months'	=> array('lang' => 'SHOW_ALL_MONTHS', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => false, 'default' => 1),
			'month_dsp'		=> array('lang' => 'MONTH_FORMAT', 'validate' => 'bool', 'type' => 'radio', 'options' => $month_dsp_options, 'explain' => false, 'default' => 'long'),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function display(array $bdata, $edit_mode = false)
	{
		extract($this->get_query_params($bdata['settings']));

		$sql = $this->db->sql_build_query('SELECT', $this->get_sql_array($forum_ids));
		$result = $this->db->sql_query($sql, $this->cache_time);

		$archive = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$archive[$row['year']]['name'] = $row['year'];
			$archive[$row['year']]['months'][$row['month'] - 1] = array(
				'count'	=> $row['total'],
				'url'	=> $this->helper->route($route_name, $route_params + array(
					'filter_type'	=> 'archive',
					'filter_value'	=> $row['year'] . '-' . $row['month'],
				)),
			);
		}
		$this->db->sql_freeresult($result);

		$this->ptemplate->assign_vars(array_merge($bdata['settings'], array('archive' => $archive)));

		return array(
			'title'		=> 'ARCHIVES',
			'content'	=> $this->ptemplate->render_view('blitze/content', 'blocks/archive.html', 'archive_block'),
		);
	}

	/**
	 * @param array $settings
	 * @return array
	 */
	protected function get_query_params(array $settings)
	{
		if ($settings['forum_id'])
		{
			return array(
				'forum_ids'		=> (array) $settings['forum_id'],
				'route_name'	=> 'blitze_content_index',
				'route_params'	=> array('type' => $this->content_types->get_forum_type($settings['forum_id'])),
			);
		}
		else
		{
			return array(
				'forum_ids'		=> array_keys($this->content_types->get_forum_types()),
				'route_name'	=> 'blitze_content_index_filter',
				'route_params'	=> array(),
			);
		}
	}

	/**
	 * @param array $forum_ids
	 * @return array
	 */
	protected function get_sql_array(array $forum_ids)
	{
		$sql_array = array(
			'SELECT'	=> array('YEAR(FROM_UNIXTIME(t.topic_time)) AS year, MONTH(FROM_UNIXTIME(t.topic_time)) AS month, COUNT(t.topic_id) AS total'),
			'WHERE'		=> array($this->db->sql_in_set('t.forum_id', $forum_ids)),
			'GROUP_BY'	=> 'year, month',
			'ORDER_BY'	=> 'year DESC',
		);

		return $this->forum->query(false, false)
			->fetch_custom($sql_array, array('SELECT'))
			->build(true, false, false)
			->get_sql_array();
	}

	/**
	 * @return string[]
	 */
	protected function get_content_type_options()
	{
		$content_types = $this->content_types->get_all_types();

		$content_type_options = array('' => 'CONTENT_TYPE_ANY');
		foreach ($content_types as $type => $entity)
		{
			/** @var \blitze\content\model\entity\type $entity */
			$content_type_options[$entity->get_forum_id()] = $entity->get_content_langname();
		}

		return $content_type_options;
	}
}
