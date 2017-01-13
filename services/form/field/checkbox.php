<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class checkbox extends choice
{
	/**
	 * @inheritdoc
	 */
	public function get_field_value($name, $default)
	{
		$default = is_array($default) ? $default : explode("\n", $default);
		$value =  $this->request->variable($name, $default, true);

		if (empty($value) && $this->request->server('REQUEST_METHOD') !== 'POST')
		{
			$value = $default;
		}

		return $value;
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'checkbox';
	}
}
