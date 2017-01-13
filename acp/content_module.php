<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\acp;

/**
 * @property string tpl_name
 * @property string page_title
 */
class content_module
{
	public $u_action;

	/**
	 *
	 */
	public function main()
	{
		global $phpbb_container, $request;

		$action = $request->variable('do', 'index');
		$content_type = $request->variable('type', '');

		$admin_controller = $phpbb_container->get('blitze.content.admin.controller');

		$admin_controller->handle($action, $content_type, $this->u_action);

		$this->tpl_name = 'acp_content';
		$this->page_title = 'CONTENT_TYPES';
	}
}
