<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class url extends base
{
	/**
	 * @inheritdoc
	 */
	public function display_field($value)
	{
		return make_clickable($value);
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'field_size'		=> 40,
			'field_minlen'		=> 0,
			'field_maxlen'		=> 200,
			'validation_filter'	=> FILTER_VALIDATE_URL,
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'url';
	}
}
