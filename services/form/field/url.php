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
	public function display_field(array $data)
	{
		return make_clickable($data['field_value']);
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'max'	=> 255,
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
