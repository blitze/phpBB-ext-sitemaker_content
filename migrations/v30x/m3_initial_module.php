<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\migrations\v30x;

/**
 * Initial schema changes needed for Extension installation
 */
class m3_initial_module extends \phpbb\db\migration\migration
{
	/**
	 * @inheritdoc
	 */
	static public function depends_on()
	{
		return array(
			'\blitze\content\migrations\v30x\m2_initial_data',
			'\blitze\content\migrations\converter\c2_update_data',
			'\blitze\content\migrations\converter\c3_update_tables',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function update_data()
	{
		return array(
			array('module.add', array(
				'acp', 'ACP_SITEMAKER', array(
					'module_basename'	=> '\blitze\content\acp\content_module',
					'modes'				=> array('content'),
				),
			)),
			array('if', array(
				!array('module.exists', array('mcp', false, 'MCP_SITEMAKER_CONTENT')),
				array('module.add', array('mcp', '', 'MCP_SITEMAKER_CONTENT')),
			)),
			array('module.add', array(
				'mcp', 'MCP_SITEMAKER_CONTENT', array(
					'module_basename'	=> '\blitze\content\mcp\content_module',
					'modes'				=> array('content'),
				),
			)),
			array('if', array(
				!array('module.exists', array('mcp', false, 'UCP_SITEMAKER_CONTENT')),
				array('module.add', array('mcp', '', 'UCP_SITEMAKER_CONTENT')),
			)),
			array('module.add', array(
				'ucp', 'UCP_SITEMAKER_CONTENT', array(
					'module_basename'	=> '\blitze\content\ucp\content_module',
					'modes'				=> array('content'),
				),
			)),
		);
	}
}
