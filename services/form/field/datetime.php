<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\services\form\field;

class datetime extends duration
{
	/** @var \phpbb\request\request_interface */
	protected $request;

	/* @var \phpbb\user */
	protected $user;

	/** @var \primetime\core\services\template */
	protected $ptemplate;

	/** @var \primetime\core\services\util */
	protected $primetime;

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request_interface		$request		Request object
	 * @param \phpbb\user							$user			User object
	 * @param \primetime\core\services\template		$ptemplate		Primetime template object
	 * @param \primetime\core\services\util			$primetime		Primetime object
	 */
	public function __construct(\phpbb\request\request_interface $request, \phpbb\user $user, \primetime\core\services\template $ptemplate, \primetime\core\services\util $primetime)
	{
		$this->request = $request;
		$this->user = $user;
		$this->ptemplate = $ptemplate;
		$this->primetime = $primetime;
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'datetime';
	}
}
