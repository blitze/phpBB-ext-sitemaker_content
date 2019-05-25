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
	public function display_field(array $data, array $topic_data, $view_mode)
	{
		return make_clickable($data['field_value']);
	}

	/**
	 * @inheritdoc
	 */
	public function get_validation_rules(array $data)
	{
		return array(
			'filter'	=> FILTER_VALIDATE_REGEXP,
			'sanitize'	=> FILTER_SANITIZE_URL,
			'options'	=> array(
				'options'	=> array(
					'regexp'	=> '#^' . get_preg_expression('url_http') . '$#iu',
				),
			),
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
