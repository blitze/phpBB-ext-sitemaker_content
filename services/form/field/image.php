<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class image extends base
{
	/**
	 * @inheritdoc
	 */
	public function get_field_value($name, $value)
	{
		$value = $this->request->variable($name, $value);
		$value = $this->get_image_src($value);

		return ($value) ? '[img]' . $value . '[/img]' : '';
	}

	/**
	 * @inheritdoc
	 */
	public function show_form_field($name, array &$data)
	{
		$bbcode_value = $this->get_field_value($name, $data['field_value']);

		$field = $this->get_name();
		$data['field_name'] = $name;
		$data['field_value'] = $this->get_image_src($bbcode_value);

		$this->ptemplate->assign_vars(array_change_key_case($data, CASE_UPPER));
		$field = $this->ptemplate->render_view('blitze/content', "fields/$field.html", $field . '_field');

		$data['field_value'] = $bbcode_value;

		return $field;
	}

	/**
	 * @inheritdoc
	 */
	public function display_field($value)
	{
		return ($value) ? '<div class="img-ui">' . $value . '</div>' : '';
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'field_size'		=> 45,
			'field_minlen'		=> 0,
			'field_maxlen'		=> 200,
			'validation_filter'	=> FILTER_VALIDATE_URL,
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'image';
	}

	/**
	 * @param string $bbcode_string
	 * @return string
	 */
	private function get_image_src($bbcode_string)
	{
		return str_replace(array('[img]', '[/img]'), '', $bbcode_string);
	}
}
