<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\migrations\v10x;

class v100 extends \phpbb\db\migration\migration
{
	/**
	 * @inheritdoc
	 */
	static public function depends_on()
	{
		return array('\primetime\primetime\migrations\v10x\v100');
	}

	/**
	 * @inheritdoc
	 */
	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'content_types'	=> array(
					'COLUMNS'        => array(
						'content_id'			=> array('UINT', null, 'auto_increment'),
						'forum_id'				=> array('UINT', 0),
						'content_name'			=> array('VCHAR:125', ''),
						'content_langname'		=> array('VCHAR:155', ''),
						'content_desc'			=> array('TEXT_UNI', ''),
						'content_status'		=> array('BOOL', 1),
						'req_approval'			=> array('BOOL', 1),
						'req_permission'		=> array('BOOL', 1),
						'allow_comments'		=> array('BOOL', 1),
						'allow_ratings'			=> array('BOOL', 1),
						'allow_keywords'		=> array('BOOL', 0),
						'show_poster_info'		=> array('BOOL', 1),
						'show_poster_contents'	=> array('BOOL', 1),
						'show_pagination'		=> array('BOOL', 1),
						'items_per_page'		=> array('TINT:4', 1),
						'max_display'			=> array('TINT:4', 1),
						'char_limit'			=> array('USINT', 0),
						'display_type'			=> array('BOOL', 0),
						'summary_tpl'			=> array('TEXT_UNI', ''),
						'detail_tpl'			=> array('TEXT_UNI', ''),
						'content_desc_bitfield'	=> array('VCHAR:255', ''),
						'content_desc_options'	=> array('UINT:11', 7),
						'content_desc_uid'		=> array('VCHAR:8', '')
					),
					'PRIMARY_KEY'	=> 'content_id',
					'KEYS'			=> array(
						'name'			=> array('INDEX', 'content_name'),
					),
				),
				$this->table_prefix . 'content_fields'	=> array(
					'COLUMNS'        => array(
						'field_id'				=> array('UINT', null, 'auto_increment'),
						'content_id'			=> array('UINT', 0),
						'field_name'			=> array('VCHAR:125', ''),
						'field_label'			=> array('VCHAR:125', ''),
						'field_description'		=> array('VCHAR:255', ''),
						'field_type'			=> array('VCHAR:55', ''),
						'field_options'			=> array('TEXT_UNI', ''),
						'field_default'			=> array('VCHAR:125', ''),
						'field_multi'			=> array('BOOL', 0),
						'field_mod_only'		=> array('BOOL', 0),
						'field_required'		=> array('BOOL', 0),
						'field_ldisplay'		=> array('BOOL', 0),
						'field_desc_uid'		=> array('VCHAR:8', ''),
						'field_desc_bitfield'	=> array('VCHAR:255', ''),
						'field_desc_options'	=> array('UINT:11', 7),
						'field_order'			=> array('TINT:3', 0)
					),
					'PRIMARY_KEY'	=> 'field_id',
					'KEYS'			=> array(
						'content_id'			=> array('INDEX', 'content_id'),
					),
				),
			),
			'add_columns'	=> array(
				$this->table_prefix . 'topics'		=> array(
					'content_type'		=> array('VCHAR:125', ''),
				)
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'content_types',
				$this->table_prefix . 'content_fields',
			),
			'drop_columns'	=> array(
				$this->table_prefix . 'topics'		=> array(
					'content_type',
				)
			),
		);
	}

	/**
	 * @inheritdoc
	 */
	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'create_forum'))),
			array('config.add', array('primetime_content_forum_id', 0)),
			array('module.add', array('acp', 'ACP_PRIMETIME_EXTENSIONS', array(
					'module_basename'	=> '\primetime\content\acp\content_module',
				),
			)),
			array('module.add', array('mcp', 0, 'CONTENT_CP')),
			array('module.add', array('ucp', 0, 'CONTENT_CP')),
		);
	}

	/**
	 * @inheritdoc
	 */
	public function revert_data()
	{
		$sql = 'SELECT content_name, req_permission
			FROM ' .$this->table_prefix . 'content_types';
		$result = $this->db->sql_query($sql);

		$modes = $permissions = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$modes[] = $row['content_name'];
			if ($row['req_permission'])
			{
				$permissions[] = array('permission.remove', array('u_content_view_' . $row['content_name']));
				$permissions[] = array('permission.remove', array('u_content_post_' . $row['content_name']));
				$permissions[] = array('permission.remove', array('m_content_manage_' . $row['content_name']));
			}
		}
		$this->db->sql_freeresult($result);

		return array(
			array('if', array(
				(sizeof($modes)),
				array('module.remove', array('mcp', 'CONTENT_CP', array(
					'module_basename'	=> '\primetime\content\mcp\content_module',
					'modes'				=> $modes
				))),
			)),
			array('if', array(
				(sizeof($modes)),
				array('module.remove', array('ucp', 'CONTENT_CP', array(
					'module_basename'	=> '\primetime\content\ucp\content_module',
					'modes'				=> $modes
				))),
			)),
			array('module.remove', array('mcp', 0, 'CONTENT_CP')),
			array('module.remove', array('ucp', 0, 'CONTENT_CP')),
			array('if', array((sizeof($permissions)), $permissions))
		);
	}

	public function create_forum()
	{
		global $phpbb_container, $config;

		$forum = $phpbb_container->get('primetime.forum.manager');

		$forum_data = array(
			'forum_type'	=> FORUM_CAT,
			'forum_name'	=> 'Primetime Content',
		);

		$errors = $forum->add($forum_data);

		if (!sizeof($errors))
		{
			$forum_id = (int) $forum_data['forum_id'];
			$this->config->set('primetime_content_forum_id', $forum_id);
		}
	}
}
