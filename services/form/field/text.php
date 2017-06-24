<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class text extends base
{
	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'text';
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'maxlength'	=> 255,
			'size'		=> 40,
		);
	}
}
