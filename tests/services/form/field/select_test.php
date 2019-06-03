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

class select_test extends base_form_field
{
	public function test_name()
	{
		$field = $this->get_form_field('select');
		$this->assertEquals('select', $field->get_name());
	}

	public function test_langname()
	{
		$field = $this->get_form_field('select');
		$this->assertEquals('FORM_FIELD_SELECT', $field->get_langname());
	}

	public function test_default_props()
	{
		$field = $this->get_form_field('select');
		$this->assertEquals(array(
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
		$field = $this->get_form_field('select');

		$data = array('field_value' => $field_value);
		$data['field_value'] = $field->get_field_value($data);

		$this->assertEquals($expected, $field->display_field($data, array(), 'summary', 'summary'));
	}

	/**
	 * @return array
	 */
	public function show_select_field_test_data()
	{
		return array(
			array(
				array(
					'field_name'	=> 'foo',
					'field_value'	=> '',
					'field_props'	=> array(
						'options'		=> array(
							'option1' => 'option1',
							'option2' => 'option2',
							'option3' => 'option3',
						),
						'defaults'		=> array(),
						'multi_select'	=> false,
					),
				),
				array(
					array('foo', '', true, request_interface::REQUEST, ''),
				),
				'<select id="smc-foo" name="foo" class="inputbox autowidth">' .
					'<option value="option1">option1</option>' .
					'<option value="option2">option2</option>' .
					'<option value="option3">option3</option>' .
				'</select>',
			),
			array(
				array(
					'field_name'	=> 'foo',
					'field_value'	=> '',
					'field_props'	=> array(
						'options'		=> array(
							'option1' => 'option1',
							'option2' => 'option2',
							'option3' => 'option3',
						),
						'defaults'		=> array('option2'),
						'multi_select'	=> false,
					),
				),
				array(),
				'<select id="smc-foo" name="foo" class="inputbox autowidth">' .
					'<option value="option1">option1</option>' .
					'<option value="option2" selected="selected">option2</option>' .
					'<option value="option3">option3</option>' .
				'</select>',
			),
			array(
				array(
					'field_name'	=> 'foo',
					'field_value'	=> 'option2',
					'field_props'	=> array(
						'options'		=> array(
							'option1' => 'option1',
							'option2' => 'option2',
							'option3' => 'option3',
						),
						'defaults'		=> array('option2'),
						'multi_select'	=> false,
					),
				),
				array(
					array('foo', 'option2', true, request_interface::REQUEST, 'option3'),
				),
				'<select id="smc-foo" name="foo" class="inputbox autowidth">' .
					'<option value="option1">option1</option>' .
					'<option value="option2">option2</option>' .
					'<option value="option3" selected="selected">option3</option>' .
				'</select>',
			),
			array(
				array(
					'field_name'	=> 'foo',
					'field_value'	=> "option1\noption2",
					'field_props'	=> array(
						'options'		=> array(
							'option1' => 'option1',
							'option2' => 'option2',
							'option3' => 'option3',
						),
						'defaults'		=> array('option2'),
						'multi_select'	=> true,
					),
				),
				array(),
				'<select id="smc-foo" name="foo[]" multiple="multiple" class="inputbox autowidth">' .
					'<option value="option1" selected="selected">option1</option>' .
					'<option value="option2" selected="selected">option2</option>' .
					'<option value="option3">option3</option>' .
				'</select>',
			),
			array(
				array(
					'field_name'	=> 'bar',
					'field_value'	=> array('option2'),
					'field_props'	=> array(
						'options'		=> array(
							'option1' => 'option1',
							'option2' => 'option2',
							'option3' => 'option3',
						),
						'defaults'		=> array('option2'),
						'multi_select'	=> true,
					),
				),
				array(
					array('bar', array('option2'), true, request_interface::REQUEST, array('option1', 'option3')),
				),
				'<select id="smc-bar" name="bar[]" multiple="multiple" class="inputbox autowidth">' .
					'<option value="option1" selected="selected">option1</option>' .
					'<option value="option2">option2</option>' .
					'<option value="option3" selected="selected">option3</option>' .
				'</select>',
			),
		);
	}

	/**
	 * @dataProvider show_select_field_test_data
	 * @param array $data
	 * @param array $variable_map
	 * @param string $expected
	 * @return void
	 */
	public function test_show_select_field(array $data, array $variable_map, $expected)
	{
		$field = $this->get_form_field('select', $variable_map);

		$data = $this->get_data('select', $data, $field->get_default_props());
		$data['field_value'] = $field->get_submitted_value($data, sizeof($variable_map));

		$this->assertEquals($expected, str_replace(array("\n", "\t"), '', $field->show_form_field($data)));
	}
}
