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

		$this->ptemplate->assign_vars($data);
		$field = $this->ptemplate->render_view('blitze/content', "fields/$field.twig", $field . '_field');

		$data['field_value'] = $bbcode_value;

		return $field;
	}

	/**
	 * @inheritdoc
	 */
	public function display_field(array $data)
	{
		return ($data['field_value']) ? '<div class="img-ui">' . $data['field_value'] . '</div>' : '';
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'min'	=> 0,
			'max'	=> 200,
			'size'	=> 45,
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
