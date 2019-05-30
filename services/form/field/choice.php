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
	 * @inheritdoc
	 */
	public function get_field_value(array $data)
	{
		$value = $data['field_value'] ?: $data['field_props']['defaults'] ?: array(0 => '');

		if (!is_array($value))
		{
			$value = array_filter(preg_split("/(\n|<br>)/", $value));
		}

		return ($data['field_props']['multi_select']) ? $value : array_shift($value);
	}

	/**
	 * @inheritdoc
	 */
	public function display_field(array $data, array $topic_data, $view_mode)
	{
		return sizeof($data['field_value']) ? join($this->language->lang('COMMA_SEPARATOR'), $data['field_value']) : '';
	}

	/**
	 * @inheritdoc
	 */
	public function get_submitted_value(array $data)
	{
		$value = $this->request->variable($data['field_name'], array(0 => ''), true);
		return $value ?: $this->get_field_value($data);
	}

	/**
	 * @inheritdoc
	 */
	public function show_form_field($name, array &$data)
	{
		$selected = (array) $this->get_submitted_value($data);

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
