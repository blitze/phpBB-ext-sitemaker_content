<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\ucp;

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

		$controller = $phpbb_container->get('blitze.content.ucp.controller');
		$controller->handle($action, $this->u_action);

		$this->tpl_name = 'cp_content';
		$this->page_title = 'CONTENT_TYPES';
	}
}
