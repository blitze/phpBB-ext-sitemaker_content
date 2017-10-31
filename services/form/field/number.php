<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class number extends base
{
	/**
	 * @inheritdoc
	 */
	public function get_field_value(array $data)
	{
		return $this->request->variable($data['field_name'], (int) $data['field_value']);
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'min'	=> 0,
			'max'	=> 0,
			'step'	=> 1,
			'size'	=> 10,
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'number';
	}
}
