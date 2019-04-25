<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2015 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\model\mapper;

use blitze\sitemaker\model\base_mapper;

class fields extends base_mapper
{
	/** @var string */
	protected $entity_class = 'blitze\content\model\entity\field';

	/** @var string */
	protected $entity_pkey = 'field_id';

	/**
	 * @param array $sql_where
	 * @return string
	 */
	protected function find_sql(array $sql_where)
	{
		return 'SELECT * FROM ' . $this->entity_table .
			((sizeof($sql_where)) ? ' WHERE ' . join(' AND ', $sql_where) : '') . '
			ORDER BY field_order ASC';
	}

	/**
	 * @return int
	 */
	public function get_max_field_id()
	{
		$result = $this->db->sql_query('SELECT MAX(' . $this->entity_pkey . ') AS max_id FROM ' . $this->entity_table);
		$max_id = $this->db->sql_fetchfield('max_id');
		$this->db->sql_freeresult($result);

		return (int) $max_id;
	}

	/**
	 * @param array $fields
	 * @return void
	 */
	public function multi_insert(array $fields)
	{
		$this->db->sql_multi_insert($this->entity_table, array_values($fields));
	}
}
