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
	public function display_field(array $data, array $topic_data, $display_mode, $view_mode)
	{
		$value = $this->ensure_is_array($data['field_value']);
		return sizeof($value) ? join($this->language->lang('COMMA_SEPARATOR'), $value) : '';
	}

	/**
	 * @inheritdoc
	 * @return array
	 */
	public function get_submitted_value(array $data, $form_is_submitted = false)
	{
		$default = $this->get_default_value($data);

		if ($form_is_submitted)
		{
			return $this->request->variable($data['field_name'], $default, true);
		}

		return $default;
	}

	/**
	 * @inheritdoc
	 */
	public function show_form_field(array &$data)
	{
		$this->set_field_options($data);
		$this->ptemplate->assign_vars($data);

		$tpl_name = ($data['field_type'] === 'select') ? 'select' : 'pickem';
		return $this->ptemplate->render_view('blitze/content', "fields/$tpl_name.html", $data['field_type'] . '_field');
	}

	/**
	 * @param array $data
	 * @return mixed
	 */
	protected function get_default_value(array $data)
	{
		$value = $this->ensure_is_array($data['field_value']);
		$default = $value ?: $data['field_props']['defaults'] ?: array(0 => '');
		return ($data['field_props']['multi_select']) ? $default : array_shift($default);
	}

	/**
	 * @param array $data
	 * @return void
	 */
	protected function set_field_options(array &$data)
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
					'id'		=> 'smc-'. $data['field_name'] . '-' . $count,
					'label'		=> $this->language->lang($option),
					'selected'	=> (int) (in_array($value, (array) $data['field_value'])),
					'value'		=> $value,
				);
				$count++;
			}
		}
		$data['field_props']['options'] = $options;
	}
}
