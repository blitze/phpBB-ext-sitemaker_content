<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\migrations\v20x;

/**
 * Initial schema changes needed for Extension installation
 */
class m2_initial_data extends \phpbb\db\migration\migration
{
	/**
	 * @inheritdoc
	 */
	static public function depends_on()
	{
		return array(
			'\primetime\content\migrations\v20x\m1_initial_schema',
			'\primetime\content\migrations\converter\c1_update_data',
			'\primetime\content\migrations\converter\c2_update_tables',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'create_forum'))),
			array('custom', array(array($this, 'create_bbcode'))),
			array('config.add', array('primetime_content_forums', '')),
			array('config.add', array('primetime_content_forum_id', 0)),
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
			array('config.remove', array('primetime_content_forums')),
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

		if (!empty($this->config['primetime_content_forum_id']))
		{
			$forum_data['forum_id'] = (int) $this->config['primetime_content_forum_id'];
		}

		$errors = $forum->add($forum_data);

		if (!sizeof($errors))
		{
			$forum_id = (int) $forum_data['forum_id'];
			$this->config->set('primetime_content_forum_id', $forum_id);
		}
	}

	public function create_bbcode()
	{
		global $cache;

		if (!class_exists('acp_bbcodes'))
		{
			include($this->phpbb_root_path . 'includes/acp/acp_bbcodes.' . $this->php_ext);
		}

		$display_on_posting = false;
		$bbcode_match = '[tag={IDENTIFIER}]{TEXT}[/tag]';
		$bbcode_tpl = '<div data-field="{IDENTIFIER}">{TEXT}</div><br />';
		$bbcode_helpline = '';

		$bbcode_manager = new \acp_bbcodes();
		$data = $bbcode_manager->build_regexp($bbcode_match, $bbcode_tpl);

		$sql_ary = array(
			'bbcode_tag'				=> $data['bbcode_tag'],
			'bbcode_match'				=> $bbcode_match,
			'bbcode_tpl'				=> $bbcode_tpl,
			'display_on_posting'		=> $display_on_posting,
			'bbcode_helpline'			=> $bbcode_helpline,
			'first_pass_match'			=> $data['first_pass_match'],
			'first_pass_replace'		=> $data['first_pass_replace'],
			'second_pass_match'			=> $data['second_pass_match'],
			'second_pass_replace'		=> $data['second_pass_replace']
		);

		// Does this bbcode already exist?
		$sql = 'SELECT bbcode_id
			FROM ' . BBCODES_TABLE . "
			WHERE bbcode_tag = '" . $this->db->sql_escape($sql_ary['bbcode_tag']) . "'";
		$result = $this->db->sql_query($sql);
		$bbcode_id = $this->db->sql_fetchfield('bbcode_id');
		$this->db->sql_freeresult($result);

		if (!empty($bbcode_id))
		{
			$this->db->sql_query('UPDATE ' . BBCODES_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE bbcode_id = ' . (int) $bbcode_id);
		}
		else
		{
			$sql = 'SELECT MAX(bbcode_id) as max_bbcode_id
				FROM ' . BBCODES_TABLE;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				$bbcode_id = $row['max_bbcode_id'] + 1;

				// Make sure it is greater than the core bbcode ids...
				if ($bbcode_id <= NUM_CORE_BBCODES)
				{
					$bbcode_id = NUM_CORE_BBCODES + 1;
				}
			}
			else
			{
				$bbcode_id = NUM_CORE_BBCODES + 1;
			}

			$sql_ary['bbcode_id'] = (int) $bbcode_id;

			$this->db->sql_query('INSERT INTO ' . BBCODES_TABLE . $this->db->sql_build_array('INSERT', $sql_ary));
		}

		$cache->destroy('sql', BBCODES_TABLE);
	}
}
