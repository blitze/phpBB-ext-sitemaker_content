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
			array(33, 33),
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
		$data['field_value'] = $field->get_field_value($data);

		$this->assertEquals($expected, $field->display_field($data, array(), 'summary'));
	}

	/**
	 * @return array
	 */
	public function show_number_field_test_data()
	{
		return array(
			array(
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
				array(
					'field_name'	=> 'bar',
					'field_value'	=> 20,
				),
				array(
					array('bar', 20, false, request_interface::REQUEST, 30),
				),
				'<div style="width: 10%">' .
					'<input type="number" class="inputbox" id="smc-bar" name="bar" step="1" value="30" />' .
				'</div>',
			),
		);
	}

	/**
	 * @dataProvider show_number_field_test_data
	 * @param array $data
	 * @param array $variable_map
	 * @param string $expected
	 * @return void
	 */
	public function test_show_number_field(array $data, array $variable_map, $expected)
	{
		$field = $this->get_form_field('number', $variable_map);

		$data = $this->get_data('number', $data, $field->get_default_props());
		$data['field_value'] = $field->get_submitted_value($data);

		$this->assertEquals($expected, str_replace(array("\n", "\t", "\r"), '', $field->show_form_field($data)));
	}

	/**
	 * @return array
	 */
	public function test_field_validation_data()
	{
		return array(
			array(
				array(
					'field_value'	=> 1,
					'field_label'	=> 'Foo',
					'field_props'	=> array(
						'min'		=> 5,
						'max'		=> 10,
						'step'		=> 1,
					),
				),
				'FIELD_INVALID_MIN_MAX Foo 5 10',
			),
			array(
				array(
					'field_value'	=> 15,
					'field_label'	=> 'Boo',
					'field_props'	=> array(
						'min'		=> 1,
						'max'		=> 5,
						'step'		=> 1,
					),
				),
				'FIELD_INVALID_MIN_MAX Boo 1 5',
			),
			array(
				array(
					'field_value'	=> 5,
					'field_label'	=> 'Boo',
					'field_props'	=> array(
						'min'		=> 10,
						'max'		=> 0,
						'step'		=> 1,
					),
				),
				'FIELD_INVALID_MIN Boo 10 0',
			),
			array(
				array(
					'field_value'	=> 15,
					'field_label'	=> 'Boo',
					'field_props'	=> array(
						'min'		=> 0,
						'max'		=> 10,
						'step'		=> 1,
					),
				),
				'FIELD_INVALID_MAX Boo 0 10',
			),
			array(
				array(
					'field_value'	=> 1.5,
					'field_label'	=> 'Boo',
					'field_props'	=> array(
						'min'		=> 0,
						'max'		=> 10,
						'step'		=> 1,
					),
				),
				'FIELD_INVALID_MAX Boo 0 10',
			),
			array(
				array(
					'field_value'	=> 8,
					'field_label'	=> 'Boo',
					'field_props'	=> array(
						'min'		=> 1,
						'max'		=> 10,
						'step'		=> 1,
					),
				),
				'',
			),
			array(
				array(
					'field_value'	=> 1.5,
					'field_label'	=> 'Boo',
					'field_props'	=> array(
						'min'		=> 0,
						'max'		=> 10,
						'step'		=> 0.1,
					),
				),
				'',
			),
		);
	}

	/**
	 * @dataProvider test_field_validation_data
	 * @param array $data
	 * @param string $expected
	 * @return void
	 */
	public function test_field_validation(array $data, $expected)
	{
		$field = $this->get_form_field('number');
		$message = $field->validate_field($data);

		$this->assertEquals($expected, $message);
	}
}
