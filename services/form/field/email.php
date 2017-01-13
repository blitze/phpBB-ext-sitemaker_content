<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class email extends base
{
	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'field_size'		=> 30,
			'field_minlen'		=> 0,
			'field_maxlen'		=> 200,
			'validation_filter'	=> FILTER_VALIDATE_EMAIL,
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'email';
	}
}
