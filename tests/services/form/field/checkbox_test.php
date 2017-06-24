<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\services\form\field;

class checkbox_test extends base_form_field
{
	public function test_name()
	{
		$field = $this->get_form_field('checkbox');
		$this->assertEquals('checkbox', $field->get_name());
	}

	public function test_langname()
	{
		$field = $this->get_form_field('checkbox');
		$this->assertEquals('FORM_FIELD_CHECKBOX', $field->get_langname());
	}

	public function test_default_props()
	{
		$field = $this->get_form_field('checkbox');
		$this->assertEquals(array(
			'per_col'		=> 1,
			'options'		=> array(),
			'defaults'		=> array(),
			'multi_select'	=> true,
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
		$field = $this->get_form_field('checkbox');
		$data = array('field_value' => $field_value);
		$this->assertEquals($expected, $field->display_field($data));
	}

	/**
	 * @return array
	 */
	public function show_form_field_test_data()
	{
		return array(
			array(
				'foo',
				array(
					'field_value'	=> '',
					'field_props'	=> array(
						'options'		=> array('option1', 'option2', 'option3'),
						'defaults'		=> array(),
						'per_col'		=> 1,
					),
				),
				'<label for="smc-foo-0"><input type="checkbox" id="smc-foo-0" name="foo[]" value="option1" /> option1</label>' .
				'<label for="smc-foo-1"><input type="checkbox" id="smc-foo-1" name="foo[]" value="option2" /> option2</label>' .
				'<label for="smc-foo-2"><input type="checkbox" id="smc-foo-2" name="foo[]" value="option3" /> option3</label>',
			),
			array(
				'foo',
				array(
					'field_value'	=> "option1\noption2",
					'field_props'	=> array(
						'options'		=> array('option1', 'option2', 'option3'),
						'defaults'		=> array(),
						'per_col'		=> 1,
					),
				),
				'<label for="smc-foo-0"><input type="checkbox" id="smc-foo-0" name="foo[]" value="option1" checked="checked" /> option1</label>' .
				'<label for="smc-foo-1"><input type="checkbox" id="smc-foo-1" name="foo[]" value="option2" checked="checked" /> option2</label>' .
				'<label for="smc-foo-2"><input type="checkbox" id="smc-foo-2" name="foo[]" value="option3" /> option3</label>',
			),
			array(
				'foo',
				array(
					'field_value'	=> '',
					'field_props'	=> array(
						'options'		=> array('option1', 'option2', 'option3'),
						'defaults'		=> array('option1', 'option3'),
						'per_col'		=> 3,
					),
				),
				'<div class="left-box" style="margin-right: 1em">' .
					'<label for="smc-foo-0"><input type="checkbox" id="smc-foo-0" name="foo[]" value="option1" checked="checked" /> option1</label><br />' .
					'<label for="smc-foo-1"><input type="checkbox" id="smc-foo-1" name="foo[]" value="option2" /> option2</label><br />' .
					'<label for="smc-foo-2"><input type="checkbox" id="smc-foo-2" name="foo[]" value="option3" checked="checked" /> option3</label><br />' .
				'</div>',
			),
			array(
				'foo',
				array(
					'field_value'	=> array('option2'),
					'field_props'	=> array(
						'options'		=> array('option1', 'option2', 'option3'),
						'defaults'		=> array('option1', 'option3'),
						'per_col'		=> 2,
					),
				),
				'<div class="left-box" style="margin-right: 1em">' .
					'<label for="smc-foo-0"><input type="checkbox" id="smc-foo-0" name="foo[]" value="option1" /> option1</label><br />' .
					'<label for="smc-foo-1"><input type="checkbox" id="smc-foo-1" name="foo[]" value="option2" checked="checked" /> option2</label><br />' .
				'</div>' .
				'<div class="left-box" style="margin-right: 1em">' .
					'<label for="smc-foo-2"><input type="checkbox" id="smc-foo-2" name="foo[]" value="option3" /> option3</label><br />' .
				'</div>',
			),
		);
	}

	/**
	 * @dataProvider show_form_field_test_data
	 * @param string $name
	 * @param array $data
	 * @param string $expected
	 * @return void
	 */
	public function test_show_checkbox_field($name, array $data, $expected)
	{
		$field = $this->get_form_field('checkbox');
		$data = $this->get_data('checkbox', $name, $data, $field->get_default_props());

		$this->assertEquals($expected, str_replace(array("\n", "\t"), '', $field->show_form_field($name, $data)));
	}
}
