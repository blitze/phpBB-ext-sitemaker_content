<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class select extends choice
{
	/** @var \phpbb\request\request_interface */
	protected $request;

	/* @var \phpbb\user */
	protected $user;

	/** @var \blitze\sitemaker\services\template */
	protected $ptemplate;

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request_interface		$request		Request object
	 * @param \phpbb\user							$user			User object
	 * @param \blitze\sitemaker\services\template		$ptemplate		Sitemaker template object
	 */
	public function __construct(\phpbb\request\request_interface $request, \phpbb\user $user, \blitze\sitemaker\services\template $ptemplate)
	{
		parent::__construct($user, $ptemplate);

		$this->request = $request;
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'select';
	}
}
