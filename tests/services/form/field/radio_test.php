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

class radio_test extends base_form_field
{
	public function test_name()
	{
		$field = $this->get_form_field('radio');
		$this->assertEquals('radio', $field->get_name());
	}

	public function test_langname()
	{
		$field = $this->get_form_field('radio');
		$this->assertEquals('FORM_FIELD_RADIO', $field->get_langname());
	}

	public function test_default_props()
	{
		$field = $this->get_form_field('radio');
		$this->assertEquals(array(
			'per_col'		=> 1,
			'options'		=> array(),
			'defaults'		=> array(),
			'multi_select'	=> false,
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
			array('foo<br>bar', 'foo, bar'),
			array('foo<br><br>bar<br>', 'foo, bar'),
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
		$field = $this->get_form_field('radio');
		$data = array('field_value' => $field_value);
		$this->assertEquals($expected, $field->display_field($data));
	}

	/**
	 * @return array
	 */
	public function show_radio_field_test_data()
	{
		return array(
			array(
				'foo',
				array(
					'field_value'	=> '',
					'field_props'	=> array(
						'options'		=> array('option1', 'option2', 'option3'),
						'defaults'		=> array(),
					),
				),
				array(
					array('foo', '', true, request_interface::REQUEST, ''),
				),
				'<label for="smc-foo-0"><input type="radio" name="foo[]" id="smc-foo-0" value="option1" /> option1</label>' .
				'<label for="smc-foo-1"><input type="radio" name="foo[]" id="smc-foo-1" value="option2" /> option2</label>' .
				'<label for="smc-foo-2"><input type="radio" name="foo[]" id="smc-foo-2" value="option3" /> option3</label>',
			),
			array(
				'foo',
				array(
					'field_value'	=> '',
					'field_props'	=> array(
						'options'		=> array('option1', 'option2', 'option3'),
						'defaults'		=> array('option2'),
					),
				),
				array(
					array('foo', '', true, request_interface::REQUEST, ''),
				),
				'<label for="smc-foo-0"><input type="radio" name="foo[]" id="smc-foo-0" value="option1" /> option1</label>' .
				'<label for="smc-foo-1"><input type="radio" name="foo[]" id="smc-foo-1" value="option2" checked="checked" /> option2</label>' .
				'<label for="smc-foo-2"><input type="radio" name="foo[]" id="smc-foo-2" value="option3" /> option3</label>',
			),
			array(
				'bar',
				array(
					'field_value'	=> array('option2'),
					'field_props'	=> array(
						'options'		=> array('option1', 'option2', 'option3'),
						'defaults'		=> array('option2'),
					),
				),
				array(
					array('bar', array('option2'), true, request_interface::REQUEST, array('option3')),
				),
				'<label for="smc-bar-0"><input type="radio" name="bar[]" id="smc-bar-0" value="option1" /> option1</label>' .
				'<label for="smc-bar-1"><input type="radio" name="bar[]" id="smc-bar-1" value="option2" /> option2</label>' .
				'<label for="smc-bar-2"><input type="radio" name="bar[]" id="smc-bar-2" value="option3" checked="checked" /> option3</label>',
			),
		);
	}

	/**
	 * @dataProvider show_radio_field_test_data
	 * @param string $name
	 * @param array $data
	 * @param array $variable_map
	 * @param string $expected
	 * @return void
	 */
	public function test_show_radio_field($name, array $data, array $variable_map, $expected)
	{
		$field = $this->get_form_field('radio', $variable_map);
		$data = $this->get_data('radio', $name, $data, $field->get_default_props());

		$this->assertEquals($expected, str_replace(array("\n", "\t"), '', $field->show_form_field($name, $data)));
	}
}
