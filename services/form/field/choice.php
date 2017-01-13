<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

abstract class choice extends base
{
	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'field_size'		=> 10,
			'field_minlen'		=> 0,
			'field_maxlen'		=> 200,
			'field_options'		=> array(),
			'field_defaults'	=> array(),
			'field_multi'		=> false,
		);
	}

	/**
	 * @inheritdoc
	 */
	public function display_field($field_value)
	{
		$field_value = array_filter(explode("<br>", $field_value));
		return sizeof($field_value) ? join(', ', $field_value) : '';
	}

	/**
	 * @inheritdoc
	 */
	public function show_form_field($name, array &$data)
	{
		$field = $this->get_name();
		$selected = $this->get_selected_options($name, $data);

		$data['field_name'] = $name;
		$data['field_value'] = join("\n", $selected);
		$data['field_size'] = $this->get_field_size($data);

		$this->set_field_options($name, $data, $selected);

		$this->ptemplate->assign_vars(array_change_key_case($data, CASE_UPPER));

		return $this->ptemplate->render_view('blitze/content', "fields/$field.html", $field . '_field');
	}

	/**
	 * @param $name
	 * @param array $data
	 * @param array $selected
	 */
	protected function set_field_options($name, array $data, array $selected)
	{
		if ($data['field_type'] === 'radio' || $data['field_type'] === 'checkbox')
		{
			$data['field_id'] .= '-0';
		}

		$count = 0;
		foreach ($data['field_settings']['field_options'] as $value => $label)
		{
			$this->ptemplate->assign_block_vars('option', array(
				'ID'		=> 'field-'. $name . '-' . $count,
				'LABEL'		=> $this->language->lang($label),
				'SELECTED'	=> (in_array($value, $selected, true)) ? true : false,
				'VALUE'		=> $value
			));
			$count++;
		}
	}

	/**
	 * @param string $name
	 * @param array $data
	 * @return array
	 */
	protected function get_selected_options($name, array $data)
	{
		$selected = $this->get_field_value($name, ($data['field_value']) ? $data['field_value'] : $data['field_settings']['field_defaults']);

		return (is_array($selected)) ? $selected : array($selected);
	}

	/**
	 * @param array $data
	 * @return int
	 */
	protected function get_field_size(array $data)
	{
		$field_options_count = sizeof($data['field_settings']['field_options']);
		return ($field_options_count < $data['field_settings']['field_size']) ? $field_options_count : $data['field_settings']['field_size'];
	}
}
