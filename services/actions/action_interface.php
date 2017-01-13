<?php
/**
 * Created by PhpStorm.
 * User: FOMAKA
 * Date: 10/11/2016
 * Time: 7:40 PM
 */

namespace blitze\content\services\actions;

interface action_interface
{
	/**
	 * Execute the action
	 *
	 * @param int $u_action
	 * @return array
	 */
	public function execute($u_action);
}
