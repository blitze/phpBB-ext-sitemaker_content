<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class hidden extends base
{
	/* @var \phpbb\user */
	protected $user;

	/** @var \blitze\sitemaker\services\template */
	protected $ptemplate;

	/**
	 * Constructor
	 *
	 * @param \phpbb\user							$user			User object
	 * @param \blitze\sitemaker\services\template		$ptemplate		Sitemaker template object
	 */
	public function __construct(\phpbb\user $user, \blitze\sitemaker\services\template $ptemplate)
	{
		$this->user = $user;
		$this->ptemplate = $ptemplate;
	}

	/**
	 * @inheritdoc
	 */
	public function display_field($value, $data = array(), $view = 'detail', $item_id = 0)
	{
		return '';
	}

	/**
	 * @inheritdoc
	 */
	public function get_field_value($name, $value, $mode = 'form')
	{
		return $value;
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'requires_item_id'	=> false
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'hidden';
	}
}
