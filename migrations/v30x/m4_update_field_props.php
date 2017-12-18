<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\migrations\v30x;

/**
 * Initial schema changes needed for Extension installation
 */
class m4_update_field_props extends \phpbb\db\migration\migration
{
	/**
	 * @inheritdoc
	 */
	public static function depends_on()
	{
		return array(
			'\blitze\content\migrations\v30x\m1_initial_schema',
		);
	}

	/**
	 * Update the sm_content_fields schema
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function update_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'sm_content_fields'	=> array(
					'field_props'		=> array('MTEXT_UNI', ''),
				),
			),
		);
	}
}
