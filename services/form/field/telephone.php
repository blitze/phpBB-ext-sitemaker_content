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
	/**
	 * @inheritdoc
	 */
	public function display_field($value)
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
