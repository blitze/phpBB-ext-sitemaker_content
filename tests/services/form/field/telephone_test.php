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

class telephone_test extends base_form_field
{
	public function test_name()
	{
		$field = $this->get_form_field('telephone');
		$this->assertEquals('telephone', $field->get_name());
	}

	public function test_langname()
	{
		$field = $this->get_form_field('telephone');
		$this->assertEquals('FORM_FIELD_TELEPHONE', $field->get_langname());
	}

	public function test_default_props()
	{
		$field = $this->get_form_field('telephone');
		$this->assertEquals(array(
			'min'	=> 0,
			'max'	=> 200,
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
			array('1234567890', '<a href="tel:1234567890">123-456-7890</a>'),
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
		$field = $this->get_form_field('telephone');

		$data = array('field_value' => $field_value);
		$data['field_value'] = $field->get_field_value($data);

		$this->assertEquals($expected, $field->display_field($data, array(), 'summary'));
	}

	/**
	 * @return array
	 */
	public function show_telephone_field_test_data()
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
				'<div style="width: 10%">' .
					'<input type="tel" class="inputbox autowidth" id="smc-foo" name="foo" maxlength="200" value="" />' .
				'</div>',
			),
			array(
				array(
					'field_name'	=> 'foo2',
					'field_value'	=> 'bar',
				),
				array(
					array('foo2', 'bar', false, request_interface::REQUEST, '1234567890'),
				),
				'<div style="width: 10%">' .
					'<input type="tel" class="inputbox autowidth" id="smc-foo2" name="foo2" maxlength="200" value="" />' .
				'</div>',
			),
		);
	}

	/**
	 * @dataProvider show_telephone_field_test_data
	 * @param array $data
	 * @param array $variable_map
	 * @param string $expected
	 * @return void
	 */
	public function test_show_telephone_field(array $data, array $variable_map, $expected)
	{
		$field = $this->get_form_field('telephone', $variable_map);

		$data = $this->get_data('telephone', $data, $field->get_default_props());
		$data['field_value'] = $field->get_submitted_value($data);

		$this->assertEquals($expected, str_replace(array("\n", "\t"), '', $field->show_form_field($data)));
	}
}
