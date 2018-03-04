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
		);
	}

	/**
	 * @param array $data
	 * @return mixed
	 */
	protected function get_default_value(array $data)
	{
		$default = $data['field_value'] ?: ($data['field_props']['defaults'] ?: array(0 => ''));
		$default = is_array($default) ? $default : explode("\n", $default);

		return ($data['field_props']['multi_select']) ? $default : array_shift($default);
	}

	/**
	 * @inheritdoc
	 */
	public function get_field_value(array $data)
	{
		$value = $this->get_default_value($data);

		// form has been submitted so get value from request object
		if ($this->request->is_set_post('cp'))
		{
			$value = $this->request->variable($data['field_name'], $value, true);
		}

		return $value;
	}

	/**
	 * @inheritdoc
	 */
	public function display_field(array $data, array $topic_data, $view_mode)
	{
		$field_value = array_filter(explode('<br>', $data['field_value']));
		return sizeof($field_value) ? join($this->language->lang('COMMA_SEPARATOR'), $field_value) : '';
	}

	/**
	 * @inheritdoc
	 */
	public function show_form_field($name, array &$data)
	{
		$selected = (array) $this->get_field_value($data);

		$data['field_name'] = $name;
		$data['field_value'] = join("\n", $selected);

		$this->set_field_options($name, $data, $selected);
		$this->ptemplate->assign_vars($data);

		$tpl_name = ($data['field_type'] === 'select') ? 'select' : 'pickem';
		return $this->ptemplate->render_view('blitze/content', "fields/$tpl_name.html", $data['field_type'] . '_field');
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
			$choices = (array) $data['field_props']['options'];
			foreach ($choices as $value => $option)
			{
				$options[] = array(
					'id'		=> 'smc-'. $name . '-' . $count,
					'label'		=> $this->language->lang($option),
					'selected'	=> (int) (in_array($value, $selected)),
					'value'		=> $value,
				);
				$count++;
			}
		}
		$data['field_props']['options'] = $options;
	}
}
