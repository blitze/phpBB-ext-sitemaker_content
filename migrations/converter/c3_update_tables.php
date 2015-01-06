<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\migrations\converter;

/**
 * Initial schema changes needed for Extension installation
 */
class c3_update_tables extends \phpbb\db\migration\migration
{
	/**
	 * Skip this migration if a previous blocks table does not exist
	 *
	 * @return bool True to skip this migration, false to run it
	 * @access public
	 */
	public function effectively_installed()
	{
		return !$this->db_tools->sql_table_exists($this->table_prefix . 'content_types');
	}

	/**
	 * @inheritdoc
	 */
	static public function depends_on()
	{
		return array(
			'\primetime\content\migrations\converter\c2_update_data',
		);
	}

	/**
	 * Update the table name
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function update_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'content_types',
			),
			'drop_columns'	=> array(
				$this->table_prefix . 'topics'		=> array('topic_tag'),
			),
		);
	}
}
