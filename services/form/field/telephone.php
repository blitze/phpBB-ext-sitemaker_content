<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class telephone extends number
{
	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'telephone';
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'min'	=> 0,
			'max'	=> 200,
			'size'	=> 10,
		);
	}

	/**
	 * @inheritdoc
	 */
	public function display_field(array $data, array $topic_data, $display_mode, $view_mode)
	{
		return $data['field_value'] ? '<a href="tel:' . $data['field_value'] . '">' . preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $data['field_value']) . '</a>' : '';
	}
}
