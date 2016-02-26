<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services;

class types
{
	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\config\db */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var string */
	private $content_types_table;

	/** @var string */
	private $content_fields_table;

	/**
	 * Construct
	 *
	 * @param \phpbb\cache\service					$cache			Cache object
	 * @param \phpbb\config\db						$config			Config object
	 * @param \phpbb\db\driver\driver_interface		$db				Database connection
	 * @param string								$fields_table	Content fields table
	 * @param string								$types_table	Content types table
	 */
	public function __construct(\phpbb\cache\service $cache, \phpbb\config\db $config, \phpbb\db\driver\driver_interface $db, $fields_table, $types_table)
	{
		$this->cache = $cache;
		$this->config = $config;
		$this->db = $db;
		$this->content_fields_table = $fields_table;
		$this->content_types_table = $types_table;
	}

	/**
	 * Get all content types
	 */
	public function get_all_types()
	{
		if (($types = $this->cache->get('_content_types')) === false)
		{
			$types = $this->_query_types();
		}

		return $types;
	}

	/**
	 * Get content type
	 */
	public function get_type($type)
	{
		$content_data = $this->get_all_types();
		return (isset($content_data[$type])) ? $content_data[$type] : array();
	}

	/**
	 * Get content types fields
	 */
	public function get_fields()
	{
		$sql = 'SELECT *
			FROM ' . $this->content_fields_table . '
			ORDER BY field_order ASC';
		$result = $this->db->sql_query($sql);

		$fields_ary = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$settings = ($row['field_settings']) ? unserialize($row['field_settings']) : array();
			unset($row['field_settings']);
			$fields_ary[$row['content_id']][$row['field_name']] = array_merge($row, $settings);
		}
		$this->db->sql_freeresult($result);

		return $fields_ary;
	}

	/**
	 * Get fields data from post
	 */
	public function get_fields_data_from_post($post_text, $fields)
	{
		$fields_data = array();
		$find_tags = join('|', $fields);
		if (preg_match_all("#<div data-field=\"($find_tags)\">(.*?)</div>#s", $post_text, $matches))
		{
			$fields_data = array_combine($matches[1], $matches[2]);
		}

		return $fields_data;
	}

	private function _query_types()
	{
		$fields_row = $this->get_fields();

		$sql = 'SELECT c.*, f.* 
				FROM ' . $this->content_types_table . ' c, ' . FORUMS_TABLE . ' f
				WHERE f.forum_id = c.forum_id';
		$result = $this->db->sql_query($sql);

		$content_data = $forum_ids = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['summary_tags'] = $row['detail_tags'] = '';

			if (isset($fields_row[$row['content_id']]))
			{
				$content_fields = $fields_row[$row['content_id']];
				$forum_ids[$row['forum_id']] = $row['content_name'];
				$fields = strtoupper(join('|', array_keys($content_fields)));

				$field_types = $summary_tags = $detail_tags = array();
				$this->_set_content_tags($content_fields, $field_types, $summary_tags, $detail_tags);

				$summary_tags = $this->_get_template_tags($row['summary_tpl'], $summary_tags, $fields);
				$detail_tags = $this->_get_template_tags($row['detail_tpl'], $detail_tags, $fields);

				$row['summary_tags']	= array_intersect_key($field_types, array_flip($summary_tags));
				$row['detail_tags']		= array_intersect_key($field_types, array_flip($detail_tags));

				$row['summary_tpl']	= htmlspecialchars_decode($row['summary_tpl']);
				$row['detail_tpl']	= htmlspecialchars_decode($row['detail_tpl']);

				$row['content_fields']	= $content_fields;
				$row['field_types']		= $field_types;
			}
			$content_data[$row['content_name']] = $row;
		}
		$this->db->sql_freeresult($result);

		$this->config->set('blitze_content_forums', serialize($forum_ids));
		$this->cache->put('_content_types', $content_data);

		return $content_data;
	}

	private function _set_content_tags($content_fields, array &$field_types, array &$summary_tags, array &$detail_tags)
	{
		foreach ($content_fields as $field => $data)
		{
			$field_types[$field] = $data['field_type'];
			if ($data['field_summary_show'])
			{
				$summary_tags[] = $field;
			}
			if ($data['field_detail_show'])
			{
				$detail_tags[] = $field;
			}
		}
	}

	private function _get_template_tags($template, array $view_tags, $content_fields)
	{
		if ($template)
		{
			preg_match_all("/\{($content_fields)\}/", $template, $view_tags);
			$view_tags = array_map('strtolower', array_pop($view_tags));
		}

		return $view_tags;
	}
}
