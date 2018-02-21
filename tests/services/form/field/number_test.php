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

class number_test extends base_form_field
{
	public function test_name()
	{
		$field = $this->get_form_field('number');
		$this->assertEquals('number', $field->get_name());
	}

	public function test_langname()
	{
		$field = $this->get_form_field('number');
		$this->assertEquals('FORM_FIELD_NUMBER', $field->get_langname());
	}

	public function test_default_props()
	{
		$field = $this->get_form_field('number');
		$this->assertEquals(array(
			'min'	=> 0,
			'max'	=> 0,
			'step'	=> 1,
			'size'	=> 10,
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
		$field = $this->get_form_field('number');
		$data = array('field_value' => $field_value);
		$this->assertEquals($expected, $field->display_field($data, array(), 'summary'));
	}

	/**
	 * @return array
	 */
	public function show_number_field_test_data()
	{
		return array(
			array(
				'foo',
				array(
					'field_name'	=> 'foo',
					'field_value'	=> '',
				),
				array(
					array('foo', '', false, request_interface::REQUEST, ''),
				),
				'<div style="width: 10%"><input type="number" class="inputbox" id="smc-foo" name="foo" step="1" value="" /></div>',
			),
			array(
				'foo',
				array(
					'field_name'	=> 'foo',
					'field_value'	=> 10,
					'field_props'	=> array(
						'min'	=> 5,
						'max'	=> 20,
					),
				),
				array(
					array('foo', 10, false, request_interface::REQUEST, 10),
				),
				'<div style="width: 10%">' .
					'<input type="number" class="inputbox" id="smc-foo" name="foo" step="1" min="5" max="20" value="10" />' .
				'</div>',
			),
			array(
				'bar',
				array(
					'field_name'	=> 'bar',
					'field_value'	=> 20,
				),
				array(
					array('bar', 20, false, request_interface::REQUEST, 20),
				),
				'<div style="width: 10%">' .
					'<input type="number" class="inputbox" id="smc-bar" name="bar" step="1" value="20" />' .
				'</div>',
			),
		);
	}

	/**
	 * @dataProvider show_number_field_test_data
	 * @param string $name
	 * @param array $data
	 * @param array $variable_map
	 * @param string $expected
	 * @return void
	 */
	public function test_show_number_field($name, array $data, array $variable_map, $expected)
	{
		$field = $this->get_form_field('number', $variable_map);
		$data = $this->get_data('number', $name, $data, $field->get_default_props());

		$this->assertEquals($expected, str_replace(array("\n", "\t", "\r"), '', $field->show_form_field($name, $data)));
	}
}
