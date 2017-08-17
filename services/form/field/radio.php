<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class radio extends choice
{
	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'radio';
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array_merge(parent::get_default_props(), array(
			'multi_select'	=> false,
			'vertical'		=> false,
		));
	}
}
