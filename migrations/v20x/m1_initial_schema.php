<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\migrations\v20x;

class m1_initial_schema extends \phpbb\db\migration\migration
{
	/**
	 * @inheritdoc
	 */
	static public function depends_on()
	{
		return array(
			'\primetime\primetime\migrations\v20x\m1_initial_schema',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'pt_content_types' => array(
					'COLUMNS'        => array(
						'content_id'			=> array('UINT', null, 'auto_increment'),
						'forum_id'				=> array('UINT', 0),
						'content_name'			=> array('VCHAR:125', ''),
						'content_langname'		=> array('VCHAR:155', ''),
						'content_enabled'		=> array('BOOL', 1),
						'content_colour'		=> array('VCHAR:6', ''),
						'content_desc'			=> array('TEXT_UNI', ''),
						'content_desc_bitfield'	=> array('VCHAR:255', ''),
						'content_desc_options'	=> array('UINT:11', 7),
						'content_desc_uid'		=> array('VCHAR:8', ''),
						'req_approval'			=> array('BOOL', 1),
						'allow_comments'		=> array('BOOL', 1),
						'show_poster_info'		=> array('BOOL', 1),
						'show_poster_contents'	=> array('BOOL', 1),
						'show_pagination'		=> array('BOOL', 1),
						'index_show_desc'		=> array('BOOL', 0),
						'items_per_page'		=> array('TINT:4', 1),
						'topics_per_group'		=> array('TINT:4', 1),
						'display_type'			=> array('VCHAR:155', ''),
						'summary_tpl'			=> array('TEXT_UNI', ''),
						'detail_tpl'			=> array('TEXT_UNI', ''),
						'last_modified'			=> array('TIMESTAMP', 0)
					),
					'PRIMARY_KEY'	=> 'content_id',
					'KEYS'			=> array(
						'name'			=> array('INDEX', 'content_name'),
					),
				),
				$this->table_prefix . 'pt_content_fields' => array(
					'COLUMNS'        => array(
						'field_id'				=> array('UINT', null, 'auto_increment'),
						'content_id'			=> array('UINT', 0),
						'field_name'			=> array('VCHAR:125', ''),
						'field_label'			=> array('VCHAR:125', ''),
						'field_explain'			=> array('VCHAR:255', ''),
						'field_type'			=> array('VCHAR:55', ''),
						'field_settings'		=> array('VCHAR:255', ''),
						'field_mod_only'		=> array('BOOL', 0),
						'field_required'		=> array('BOOL', 0),
						'field_summary_show'	=> array('BOOL', 0),
						'field_detail_show'		=> array('BOOL', 0),
						'field_summary_ldisp'	=> array('BOOL', 0),
						'field_detail_ldisp'	=> array('BOOL', 0),
						'field_exp_uid'			=> array('VCHAR:8', ''),
						'field_exp_bitfield'	=> array('VCHAR:255', ''),
						'field_exp_options'		=> array('UINT:11', 7),
						'field_order'			=> array('TINT:3', 0)
					),
					'PRIMARY_KEY'	=> 'field_id',
					'KEYS'			=> array(
						'cid'			=> array('INDEX', 'content_id'),
					),
				),
			),
			'add_columns'	=> array(
				$this->table_prefix . 'topics'		=> array(
					'topic_slug'		=> array('VCHAR:255', ''),
				)
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'pt_content_types',
				$this->table_prefix . 'pt_content_fields',
			),
			'drop_columns'	=> array(
				$this->table_prefix . 'topics'		=> array(
					'topic_slug',
				)
			),
		);
	}
}
