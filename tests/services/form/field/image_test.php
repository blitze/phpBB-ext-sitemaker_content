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

class image_test extends base_form_field
{
	public function test_name()
	{
		$field = $this->get_form_field('image');
		$this->assertEquals('image', $field->get_name());
	}

	public function test_langname()
	{
		$field = $this->get_form_field('image');
		$this->assertEquals('FORM_FIELD_IMAGE', $field->get_langname());
	}

	public function test_default_props()
	{
		$field = $this->get_form_field('image');
		$this->assertEquals(array(
			'min'	=> 0,
			'max'	=> 200,
			'size'	=> 45,
		), $field->get_default_props());
	}

	/**
	 * @return array
	 */
	public function display_field_test_data()
	{
		return array(
			array('', ''),
			array('foo', '<div class="img-ui">foo</div>'),
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
		$field = $this->get_form_field('image');
		$data = array(
			'field_value' => $field_value,
			'field_props' => $field->get_default_props()
		);

		$this->assertEquals($expected, $field->display_field($data));
	}

	/**
	 * @return array
	 */
	public function show_image_field_test_data()
	{
		return array(
			array(
				'foo',
				array(
					'field_value' => '',
				),
				array(
					array('foo', '', false, request_interface::REQUEST, ''),
				),
				'<div style="width: 45%">' .
					'<input type="text" class="inputbox image-field" id="smc-foo" name="foo" maxlength="200" value="" />' .
					'<div id="preview-foo" class="large-img"></div>' .
				'</div>',
			),
			array(
				'foo',
				array(
					'field_value' => '',
					'field_props' => array(
						'size'	=> 65,
					),
				),
				array(
					array('foo', '', false, request_interface::REQUEST, 'bar'),
				),
				'<div style="width: 65%">' .
					'<input type="text" class="inputbox image-field" id="smc-foo" name="foo" maxlength="200" value="bar" />' .
					'<div id="preview-foo" class="large-img"><img class="img-ui" src="bar" alt="" /></div>' .
				'</div>',
			),
			array(
				'foo2',
				array(
					'field_value' => 'bar',
				),
				array(
					array('foo2', 'bar', false, request_interface::REQUEST, 'foo_bar'),
				),
				'<div style="width: 45%">' .
					'<input type="text" class="inputbox image-field" id="smc-foo2" name="foo2" maxlength="200" value="foo_bar" />' .
					'<div id="preview-foo2" class="large-img"><img class="img-ui" src="foo_bar" alt="" /></div>' .
				'</div>',
			),
		);
	}

	/**
	 * @dataProvider show_image_field_test_data
	 * @param string $name
	 * @param array $data
	 * @param array $variable_map
	 * @param string $expected
	 * @return void
	 */
	public function test_show_image_field($name, array $data, array $variable_map, $expected)
	{
		$field = $this->get_form_field('image', $variable_map);
		$data = $this->get_data('image', $name, $data, $field->get_default_props());

		$this->assertEquals($expected, str_replace(array("\n", "\t", "\r"), '', $field->show_form_field($name, $data)));
	}
}
