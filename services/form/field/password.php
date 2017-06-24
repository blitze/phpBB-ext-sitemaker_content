<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class password extends base
{
	/**
	 * @inheritdoc
	 */
	public function get_field_value($name, $value)
	{
		return $this->request->variable($name, (string) $value, true);
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'min'	=> 0,
			'max'	=> 200,
			'size'	=> 30,
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'password';
	}
}
