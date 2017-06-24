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
			'size'	=> 45,
			'min'	=> 0,
			'max'	=> 255,
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
