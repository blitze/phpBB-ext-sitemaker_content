<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\mcp;

class content_module
{
	/** @var string */
	public $tpl_name;

	/** @var string */
	public $page_title;

	/** @var string */
	public $u_action;

	public function main()
	{
		global $phpbb_container, $request;

		$action = $request->variable('do', 'index');

		try
		{
			$controller = $phpbb_container->get('blitze.content.mcp.controller');
			$controller->handle($action, $this->u_action);
		}
		catch (\Exception $e)
		{
			trigger_error($e->getMessage());
		}

		$this->tpl_name = 'cp_content';
		$this->page_title = 'CONTENT_TYPES';
	}
}
