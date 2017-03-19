<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\actions;

interface action_interface
{
	/**
	 * Execute the action
	 *
	 * @param int $u_action
	 * @return void
	 */
	public function execute($u_action);
}
