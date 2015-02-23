<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\mcp;

class content_info
{
	public function module()
	{
		return array(
			'filename'	=> '\primetime\content\mcp\content_module',
			'title'		=> 'MCP_PRIMETIME_CONTENT',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'content'		=> array('title' => 'MCP_CONTENT', 'auth' => 'ext_primetime/content', 'cat' => array('MCP_PRIMETIME_CONTENT')),
			),
		);
	}
}
