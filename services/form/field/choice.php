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
			'defaults'		=> array(),
			'options'		=> array(),
			'multi_select'	=> false,
			'per_col'		=> 1,
		);
	}

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
	public function display_field(array $data)
	{
		$field_value = array_filter(explode('<br>', $data['field_value']));
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

		if (isset($data['field_props']['per_col']) && $data['field_props']['per_col'] < 1)
		{
			$data['field_props']['per_col'] = 1;
		}

		$this->set_field_options($name, $data, $selected);
		$this->ptemplate->assign_vars($data);

		return $this->ptemplate->render_view('blitze/content', "fields/$field.twig", $field . '_field');
	}

	/**
	 * @param $name
	 * @param array $data
	 * @param array $selected
	 */
	protected function set_field_options($name, array &$data, array $selected)
	{
		if ($data['field_type'] === 'radio' || $data['field_type'] === 'checkbox')
		{
			$data['field_id'] .= '-0';
		}

		$count = 0;
		$options = array();
		if (is_array($data['field_props']['options']))
		{
			foreach ($data['field_props']['options'] as $option)
			{
				$options[] = array(
					'id'		=> 'smc-'. $name . '-' . $count,
					'label'		=> $this->language->lang($option),
					'selected'	=> (in_array($option, $selected, true)) ? true : false,
					'value'		=> $option,
				);
				$count++;
			}
		}
		$data['field_props']['options'] = $options;
	}

	/**
	 * @param string $name
	 * @param array $data
	 * @return array
	 */
	protected function get_selected_options($name, array $data)
	{
		$selected = $this->get_field_value($name, ($data['field_value']) ? $data['field_value'] : $data['field_props']['defaults']);
		return (is_array($selected)) ? $selected : array($selected);
	}
}
