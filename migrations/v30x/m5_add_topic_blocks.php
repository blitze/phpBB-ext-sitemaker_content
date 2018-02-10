<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2018 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\migrations\v30x;

/**
 */
class m5_add_topic_blocks extends \phpbb\db\migration\migration
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
	 * Update the sm_content_types schema
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function update_schema()
	{
		return array(
			'add_columns'        => array(
				$this->table_prefix . 'sm_content_types'	=> array(
					'topic_blocks'        => array('VCHAR:255', '', 'after' => 'content_view_settings'),
				),
			),
			'drop_columns'	=> array(
				$this->table_prefix . 'sm_content_types'	=> array(
					'show_poster_info',
					'show_poster_contents',
				),
			),
		);
	}

	/**
	 * @inheritdoc
	 */
	public function revert_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'sm_content_types'	=> array(
					'show_poster_info'		=> array('BOOL', 1),
					'show_poster_contents'	=> array('BOOL', 1),
				),
			),
			'drop_columns'	=> array(
				$this->table_prefix . 'sm_content_types'	=> array(
					'topic_blocks',
				),
			),
		);
	}
}
