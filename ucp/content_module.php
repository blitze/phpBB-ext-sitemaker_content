<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\ucp;

class content_module
{
	/** @var string */
	public $u_action;

	function main($id, $mode)
	{
		global $phpbb_container;

		$this->tpl_name = 'cp_content';
		$phpbb_container->get('primetime.content.manager')->handle_crud('ucp', $this->u_action);
	}
}
