<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\services\form\field;

use phpbb\request\request_interface;

class email_test extends base_form_field
{
	public function test_name()
	{
		$field = $this->get_form_field('email');
		$this->assertEquals('email', $field->get_name());
	}

	public function test_langname()
	{
		$field = $this->get_form_field('email');
		$this->assertEquals('FORM_FIELD_EMAIL', $field->get_langname());
	}

	public function test_default_props()
	{
		$field = $this->get_form_field('email');
		$this->assertEquals(array(
			'size'	=> 45,
			'min'	=> 0,
			'max'	=> 255,
		), $field->get_default_props());
	}

	/**
	 * @return array
	 */
	public function display_field_test_data()
	{
		return array(
			array('', ''),
			array('foo', 'foo'),
		);
	}

	/**
	 * @dataProvider display_field_test_data
	 * @param string $field_value
	 * @param string $expected
	 * @return void
	 */
	public function test_display_field($field_value, $expected)
	{
		$field = $this->get_form_field('email');
		$data = array('field_value' => $field_value);
		$this->assertEquals($expected, $field->display_field($data));
	}

	/**
	 * @return array
	 */
	public function show_email_field_test_data()
	{
		return array(
			array(
				'foo',
				array(
					'field_value'	=> '',
				),
				array(
					array('foo', '', true, request_interface::REQUEST, ''),
				),
				'<input type="email" class="inputbox autowidth" id="foo" name="foo" size="45" maxlength="255" value="" />'
			),
			array(
				'foo',
				array(
					'field_value'	=> 'bar',
				),
				array(
					array('foo', 'bar', true, request_interface::REQUEST, 'bar'),
				),
				'<input type="email" class="inputbox autowidth" id="foo" name="foo" size="45" maxlength="255" value="bar" />'
			),
			array(
				'foo',
				array(
					'field_value'	=> 'bar',
				),
				array(
					array('foo', 'bar', true, request_interface::REQUEST, 'foo_bar'),
				),
				'<input type="email" class="inputbox autowidth" id="foo" name="foo" size="45" maxlength="255" value="foo_bar" />',
			),
		);
	}

	/**
	 * @dataProvider show_email_field_test_data
	 * @param string $name
	 * @param array $data
	 * @param array $variable_map
	 * @param string $expected
	 * @return void
	 */
	public function test_show_email_field($name, array $data, array $variable_map, $expected)
	{
		$field = $this->get_form_field('email', $variable_map);
		$data = $this->get_data('email', $name, $data, $field->get_default_props());

		$this->assertEquals($expected, $field->show_form_field($name, $data));
	}
}
