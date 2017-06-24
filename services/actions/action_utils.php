<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\actions;

abstract class action_utils
{
	/** @var bool */
	protected $auto_refresh = true;

	/** @var bool */
	protected $redirect = true;

	/** @var bool */
	protected $trigger_error = true;

	/**
	 * @param string $u_action
	 * @return void
	 */
	protected function redirect($u_action)
	{
		$this->redirect ? redirect($u_action) : null;
	}

	/**
	 * @param int $time
	 * @param string $u_action
	 * @return void
	 */
	protected function meta_refresh($time, $u_action)
	{
		$this->auto_refresh ? meta_refresh(3, $u_action) : null;
	}

	/**
	 * @param string $message
	 * @param string $u_action
	 * @param int $errno
	 * @return void
	 */
	protected function trigger_error($message, $u_action = '', $errno = E_USER_NOTICE)
	{
		$message .= $u_action ? adm_back_link($u_action) : '';
		$this->trigger_error ? trigger_error($message, $errno) : null;
	}
}
