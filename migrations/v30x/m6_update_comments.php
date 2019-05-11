<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2019 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\migrations\v30x;

class m6_update_comments extends \phpbb\db\migration\migration
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
	 * @return array Array of table schema
	 * @access public
	 */
	public function update_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'sm_content_types'	=> array(
					'allow_comments',
				),
			),
			'add_columns'	=> array(
				$this->table_prefix . 'sm_content_types'	=> array(
					'comments'			=> array('VCHAR:155', '', 'after' => 'content_view_settings'),
					'comments_settings'	=> array('VCHAR:255', '', 'after' => 'comments'),
				),
			),
		);
	}

	/**
	 * @return array Array of table schema
	 * @access public
	 */
	public function revert_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'sm_content_types'	=> array(
					'allow_comments'	=> array('BOOL', 1, 'after' => 'req_approval'),
				),
			),
			'drop_columns'	=> array(
				$this->table_prefix . 'sm_content_types'	=> array(
					'comments',
					'comments_settings',
				),
			),
		);
	}
}
