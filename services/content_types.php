<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\services;

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package phpBB Primetime
*/
class content_types
{
	/**
	 * Cache
	 * @var \phpbb\cache\service
	 */
	protected $cache;

	/**
	* Construct
	*
	 * @param \phpbb\cache\service					$cache			Cache object
	* @param \phpbb\db\driver\factory				$db             Database connection
	*/
	public function __construct(\phpbb\cache\driver\driver_interface $cache, \phpbb\db\driver\factory $db)
	{
		$this->cache = $cache;
		$this->db = $db;
	}

	/**
	* Get all content types
	*/
	function get_all_types()
	{
		$sql = 'SELECT * 
			FROM ' . CONTENT_TYPES_TABLE;
		$result = $this->db->sql_query($sql);

		$types = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$types[$row['content_name']] = $row;
		}
		$this->db->sql_freeresult($result);

		return $types;
	}

	/**
	* Get content type
	*/
	function get_type($type)
	{
		$content_data = array();
		if (($content_data = $this->cache->get('_content_type_' . $type)) === false)
		{
			$sql = 'SELECT c.*, f.* 
				FROM ' . CONTENT_TYPES_TABLE . ' c, ' . FORUMS_TABLE . " f
				WHERE f.forum_id = c.forum_id
					AND c.content_name = '" . $this->db->sql_escape($type) . "'";
			$result = $this->db->sql_query($sql);
			$content_data = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$content_data['summary_tags'] = $content_data['detail_tags'] = '';
			$content_fields = $this->get_fields($content_data['content_id']);

			if (sizeof($content_fields))
			{
				$fields = strtoupper(join('|', array_keys($content_fields)));

				preg_match_all("#$fields#", $content_data['summary_tpl'], $summary_tags);
				preg_match_all("#$fields#", $content_data['detail_tpl'], $detail_tags);

				$content_data['summary_tags'] = array_map('strtolower', array_shift($summary_tags));
				$content_data['detail_tags'] = array_map('strtolower', array_shift($detail_tags));

				$content_data['summary_tpl'] = htmlspecialchars_decode($content_data['summary_tpl']);
				$content_data['detail_tpl'] = htmlspecialchars_decode($content_data['detail_tpl']);

				$content_data['content_fields'] = $content_fields;
			}

			$this->cache->put('_content_type_' . $type, $content_data);
		}

		return $content_data;
	}

	/**
	* Get content types fields
	*/
	function get_fields($content_id)
	{
		$sql = 'SELECT *
			FROM ' . CONTENT_FIELDS_TABLE . '
			WHERE content_id = ' . (int) $content_id . '
			ORDER BY field_order ASC';
		$result = $this->db->sql_query($sql);

		$fields_ary = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$fields_ary[$row['field_name']] = $row;
		}
		$this->db->sql_freeresult($result);

		return $fields_ary;
	}
}