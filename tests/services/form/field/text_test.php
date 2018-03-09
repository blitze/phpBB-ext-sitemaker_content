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

class text_test extends base_form_field
{
	public function test_name()
	{
		$field = $this->get_form_field('text');
		$this->assertEquals('text', $field->get_name());
	}

	public function test_langname()
	{
		$field = $this->get_form_field('text');
		$this->assertEquals('FORM_FIELD_TEXT', $field->get_langname());
	}

	public function test_default_props()
	{
		$field = $this->get_form_field('text');
		$this->assertEquals(array(
			'maxlength'	=> 255,
			'size'		=> 40,
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
		$field = $this->get_form_field('text');
		$data = array('field_value' => $field_value);
		$this->assertEquals($expected, $field->display_field($data, array(), 'summary'));
	}

	/**
	 * @return array
	 */
	public function show_text_field_test_data()
	{
		return array(
			array(
				'foo',
				array(
					'field_name'	=> 'foo',
					'field_value'	=> '',
				),
				array(
					array('foo', '', true, request_interface::REQUEST, ''),
				),
				'<div style="width: 40%">' .
					'<input type="text" class="inputbox" id="smc-foo" name="foo" maxlength="" value="" />' .
				'</div>',
			),
			array(
				'foo',
				array(
					'field_name'	=> 'foo',
					'field_value'	=> 'bar',
				),
				array(
					array('foo', 'bar', true, request_interface::REQUEST, 'bar'),
				),
				'<div style="width: 40%">' .
					'<input type="text" class="inputbox" id="smc-foo" name="foo" maxlength="" value="bar" />' .
				'</div>',
			),
			array(
				'foo2',
				array(
					'field_name'	=> 'foo2',
					'field_value'	=> 'bar',
				),
				array(
					array('foo2', 'bar', true, request_interface::REQUEST, 'foo_bar'),
				),
				'<div style="width: 40%">' .
					'<input type="text" class="inputbox" id="smc-foo2" name="foo2" maxlength="" value="foo_bar" />' .
				'</div>',
			),
		);
	}

	/**
	 * @dataProvider show_text_field_test_data
	 * @param string $name
	 * @param array $data
	 * @param array $variable_map
	 * @param string $expected
	 * @return void
	 */
	public function test_show_text_field($name, array $data, array $variable_map, $expected)
	{
		$field = $this->get_form_field('text', $variable_map);
		$data = $this->get_data('text', $name, $data, $field->get_default_props());

		$this->assertEquals($expected, str_replace(array("\n", "\t"), '', $field->show_form_field($name, $data)));
	}

	/**
	 * @return array
	 */
	public function test_field_validation_data()
	{
		return array(
			array(
				array(
					'field_value'	=> 'some random statement',
					'field_label'	=> 'Foo',
					'field_props'	=> array(
						'maxlength'		=> 5,
					),
				),
				'FIELD_TOO_LONG Foo 5',
			),
			array(
				array(
					'field_value'	=> 'some random statement',
					'field_label'	=> 'Foo',
					'field_props'	=> array(
						'maxlength'		=> 55,
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
		$field = $this->get_form_field('text');
		$this->assertEquals($expected, $field->validate_field($data));
	}
}
