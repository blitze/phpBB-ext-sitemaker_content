<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class range extends base
{
	/**
	 * @inheritdoc
	 */
	public function get_field_value($name, $value)
	{
		return $this->request->variable($name, (int) $value);
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'field_minlen'		=> 0,
			'field_maxlen'		=> 200,
			'field_step'		=> 1,
			'field_value'		=> 0,
			'validation_filter'	=> FILTER_VALIDATE_INT,
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'range';
	}
}
