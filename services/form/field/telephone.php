<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class telephone extends base
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
		$this->request = $request;
		$this->user = $user;
		$this->ptemplate = $ptemplate;
	}

	/**
	 * @inheritdoc
	 */
	public function display_field($value, $data = array(), $view = 'detail', $item_id = 0)
	{
		return '<a href="tel:' . $value . '">' . preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $value) . '</a>';
	}

	/**
	 * @inheritdoc
	 */
	public function get_field_value($name, $value)
	{
		$value = $this->request->variable($name, (int) $value);
		return ($value) ? $value : '';
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'field_size'		=> 10,
			'field_minlen'		=> 0,
			'field_maxlen'		=> 200,
			'field_value'		=> '',
			'requires_item_id'	=> false,
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'telephone';
	}
}
