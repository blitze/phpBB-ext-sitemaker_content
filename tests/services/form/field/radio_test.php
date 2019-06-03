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
			'options'		=> array(),
			'defaults'		=> array(),
			'multi_select'	=> false,
			'vertical'		=> false,
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
		$data['field_value'] = $field->get_field_value($data);

		$this->assertEquals($expected, $field->display_field($data, array(), 'summary', 'summary'));
	}

	/**
	 * @return array
	 */
	public function show_radio_field_test_data()
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
					),
				),
				array(
					array('foo', '', true, request_interface::REQUEST, ''),
				),
				'<label for="smc-foo-0"><input type="radio" id="smc-foo-0" name="foo" value="option1" /> option1</label>' .
				'<label for="smc-foo-1"><input type="radio" id="smc-foo-1" name="foo" value="option2" /> option2</label>' .
				'<label for="smc-foo-2"><input type="radio" id="smc-foo-2" name="foo" value="option3" /> option3</label>',
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
					),
				),
				array(),
				'<label for="smc-foo-0"><input type="radio" id="smc-foo-0" name="foo" value="option1" /> option1</label>' .
				'<label for="smc-foo-1"><input type="radio" id="smc-foo-1" name="foo" value="option2" checked="checked" /> option2</label>' .
				'<label for="smc-foo-2"><input type="radio" id="smc-foo-2" name="foo" value="option3" /> option3</label>',
			),
			array(
				array(
					'field_name'	=> 'bar',
					'field_value'	=> 'option2',
					'field_props'	=> array(
						'options'		=> array(
							'option1' => 'option1',
							'option2' => 'option2',
							'option3' => 'option3',
						),
						'defaults'		=> array('option2'),
					),
				),
				array(
					array('bar', 'option2', true, request_interface::REQUEST, 'option3'),
				),
				'<label for="smc-bar-0"><input type="radio" id="smc-bar-0" name="bar" value="option1" /> option1</label>' .
				'<label for="smc-bar-1"><input type="radio" id="smc-bar-1" name="bar" value="option2" /> option2</label>' .
				'<label for="smc-bar-2"><input type="radio" id="smc-bar-2" name="bar" value="option3" checked="checked" /> option3</label>',
			),
			array(
				array(
					'field_name'	=> 'bar',
					'field_value'	=> 'option2',
					'field_props'	=> array(
						'options'		=> array(
							'option1' => 'option1',
							'option2' => 'option2',
							'option3' => 'option3',
							'option4' => 'option4',
							'option5' => 'option5',
							'option6' => 'option6',
						),
						'defaults'		=> array('option2'),
						'vertical'		=> true,
					),
				),
				array(
					array('bar', 'option2', true, request_interface::REQUEST, 'option3'),
				),
				'<div class="left-box" style="margin-right: 1em">' .
					'<label for="smc-bar-0"><input type="radio" id="smc-bar-0" name="bar" value="option1" /> option1</label><br />' .
					'<label for="smc-bar-1"><input type="radio" id="smc-bar-1" name="bar" value="option2" /> option2</label><br />' .
					'<label for="smc-bar-2"><input type="radio" id="smc-bar-2" name="bar" value="option3" checked="checked" /> option3</label><br />' .
					'<label for="smc-bar-3"><input type="radio" id="smc-bar-3" name="bar" value="option4" /> option4</label><br />' .
					'<label for="smc-bar-4"><input type="radio" id="smc-bar-4" name="bar" value="option5" /> option5</label><br />' .
				'</div>' .
				'<div class="left-box" style="margin-right: 1em">' .
					'<label for="smc-bar-5"><input type="radio" id="smc-bar-5" name="bar" value="option6" /> option6</label><br />' .
				'</div>',
			),
		);
	}

	/**
	 * @dataProvider show_radio_field_test_data
	 * @param array $data
	 * @param array $variable_map
	 * @param string $expected
	 * @return void
	 */
	public function test_show_radio_field(array $data, array $variable_map, $expected)
	{
		$field = $this->get_form_field('radio', $variable_map);

		$data = $this->get_data('radio', $data, $field->get_default_props());
		$data['field_value'] = $field->get_submitted_value($data, sizeof($variable_map));

		$this->assertEquals($expected, str_replace(array("\n", "\t"), '', $field->show_form_field($data)));
	}
}
