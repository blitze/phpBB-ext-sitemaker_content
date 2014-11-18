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
class m3_initial_module extends \phpbb\db\migration\migration
{
	/**
	 * @inheritdoc
	 */
	static public function depends_on()
	{
		return array(
			'\primetime\content\migrations\v20x\m2_initial_data',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function update_data()
	{
		return array(
			array('module.add', array('acp', 'ACP_PRIMETIME_EXTENSIONS', array(
					'module_basename'	=> '\primetime\content\acp\content_module',
				),
			)),
			array('module.add', array('mcp', 'MCP_PRIMETIME_CONTENT', array(
					'module_basename'	=> '\primetime\content\mcp\content_module',
				),
			)),
			array('module.add', array('ucp', 'UCP_PRIMETIME_CONTENT', array(
					'module_basename'	=> '\primetime\content\ucp\content_module',
				),
			)),
		);
	}
}
