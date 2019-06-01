<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class number extends base
{
	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'number';
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'min'	=> 0,
			'max'	=> 0,
			'step'	=> 1,
			'size'	=> 10,
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_validation_rules(array $data)
	{
		$filter = FILTER_VALIDATE_INT;
		$sanitize = FILTER_SANITIZE_NUMBER_INT;

		if (is_float($data['field_props']['step']))
		{
			$filter = FILTER_VALIDATE_FLOAT;
			$sanitize = FILTER_SANITIZE_NUMBER_FLOAT;
		}

		return array(
			'filter'	=> $filter,
			'sanitize'	=> $sanitize,
			'options'	=> array(
				'options'	=> array_filter(array(
					'min_range'	=> $data['field_props']['min'],
					'max_range'	=> $data['field_props']['max'],
					'decimal'	=> '.',
				)),
				'flags'	=> FILTER_FLAG_ALLOW_THOUSAND,
			),
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_error_message(array $data)
	{
		$props = $data['field_props'];
		$lang_keys = array('FIELD_INVALID');

		$lang_keys[] = ($props['min']) ? 'MIN' : '';
		$lang_keys[] = ($props['max']) ? 'MAX' : '';

		return $this->language->lang(join('_', array_filter($lang_keys)), $data['field_label'], $props['min'], $props['max']);
	}

	/**
	 * @inheritdoc
	 * @return int
	 */
	public function get_field_value(array $data)
	{
		return (int) $data['field_value'];
	}

	/**
	 * @inheritdoc
	 * @return int
	 */
	public function get_submitted_value(array $data)
	{
		$value = $this->get_field_value($data);
		return $this->request->variable($data['field_name'], $value);
	}
}
