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
use blitze\content\services\form\field\color;

class color_test extends base_form_field
{
	protected $util;

	/**
	 * Create the form field service
	 *
	 * @param array $variable_map
	 * @return \blitze\content\services\form\field\field_interface
	 */
	protected function get_form_field($field, array $variable_map = array())
	{
		$this->request->expects($this->any())
			->method('variable')
			->with($this->anything())
			->will($this->returnValueMap($variable_map));

		$this->util = $this->getMockBuilder('\blitze\sitemaker\services\util')
			->disableOriginalConstructor()
			->getMock();

		return new color($this->language, $this->request, $this->ptemplate, $this->util);
	}

	public function test_name()
	{
		$field = $this->get_form_field('color');
		$this->assertEquals('color', $field->get_name());
	}

	public function test_langname()
	{
		$field = $this->get_form_field('color');
		$this->assertEquals('FORM_FIELD_COLOR', $field->get_langname());
	}

	public function test_default_props()
	{
		$field = $this->get_form_field('color');
		$this->assertEquals(array(
			'display'		=> 'box',
			'num_colors'	=> 1,
			'palette'		=> '',
			'palette_only'	=> false,
		), $field->get_default_props());
	}

	/**
	 * @return array
	 */
	public function display_field_test_data()
	{
		return array(
			array(
				array(
					'field_value'	=> '',
				),
				'',
			),
			array(
				array(
					'field_value'	=> '#ccc',
					'field_props'	=> array(
						'display'		=> 'hash',
					),
				),
				'#ccc',
			),
			array(
				array(
					'field_value'	=> '#ccc',
					'field_props'	=> array(
						'display'		=> 'box',
					),
				),
				'<div style="display: inline-block; width: 15px; height: 15px; border: 1 solid #fff; border-radius: 4px; background-color: #ccc" title="#ccc"></div>',
			),
		);
	}

	/**
	 * @dataProvider display_field_test_data
	 * @param array $data
	 * @param string $expected
	 * @return void
	 */
	public function test_display_field(array $data, $expected)
	{
		$field = $this->get_form_field('color');
		$this->assertEquals($expected, $field->display_field($data));
	}

	/**
	 * @return array
	 */
	public function show_color_field_test_data()
	{
		return array(
			array(
				'foo',
				array(
					'field_name'	=> 'foo',
					'field_value'	=> '',
					'field_props'	=> array(),
				),
				array(
					array('foo', '', true, request_interface::REQUEST, ''),
				),
				'<input type="text" class="inputbox autowidth colorpicker" id="smc-foo-1" name="foo[]" value="" data-palette="" data-allow-empty="true" size="7" />',
			),
			array(
				'foo',
				array(
					'field_name'	=> 'foo',
					'field_value'	=> 'bar',
					'field_props'	=> array(
						'num_colors'	=> 2,
						'palette'		=> '',
						'palette_only'	=> false,
					),
				),
				array(
					array('foo', 'bar', true, request_interface::REQUEST, 'bar'),
				),
				'<input type="text" class="inputbox autowidth colorpicker" id="smc-foo-1" name="foo[]" value="bar" data-palette="" data-allow-empty="true" size="7" />' .
				'<input type="text" class="inputbox autowidth colorpicker" id="smc-foo-2" name="foo[]" value="" data-palette="" data-allow-empty="true" size="7" />',
			),
			array(
				'foo2',
				array(
					'field_name'	=> 'foo2',
					'field_value'	=> 'bar',
					'field_props'	=> array(
						'display'		=> 'box',
						'num_colors'	=> 1,
						'palette'		=> '#cc3, #334, #455',
						'palette_only'	=> true,
					),
				),
				array(
					array('foo2', 'bar', true, request_interface::REQUEST, 'foo_bar'),
				),
				'<input type="text" class="inputbox autowidth colorpicker" id="smc-foo2-1" name="foo2[]" value="bar" data-palette="#cc3, #334, #455" data-show-palette-only="1" data-allow-empty="true" size="7" />',
			),
		);
	}

	/**
	 * @dataProvider show_color_field_test_data
	 * @param string $name
	 * @param array $data
	 * @param array $variable_map
	 * @param string $expected
	 * @return void
	 */
	public function test_show_color_field($name, array $data, array $variable_map, $expected)
	{
		$field = $this->get_form_field('color', $variable_map);
		$data = $this->get_data('color', $name, $data, $field->get_default_props());

		$this->util->expects($this->once())
			->method('add_assets');

		$this->assertEquals($expected, $field->show_form_field($name, $data));
	}
}
