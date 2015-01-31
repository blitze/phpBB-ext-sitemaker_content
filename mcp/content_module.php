<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\mcp;

class content_module
{
	/** @var string */
	var $tpl_name;

	/** @var string */
	var $page_title;

	/** @var string */
	var $u_action;

	function main()
	{
		global $phpbb_container, $template;

		$this->tpl_name = 'cp_content';
		$this->page_title = 'MCP_CONTENT';

		$template->assign_var('MODE', 'mcp');
		$phpbb_container->get('primetime.content.manager')->handle_crud('mcp', $this->u_action);
	}
}
