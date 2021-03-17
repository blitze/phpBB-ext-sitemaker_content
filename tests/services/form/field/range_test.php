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
use blitze\content\services\form\field\range;

class range_test extends base_form_field
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

		return new range($this->language, $this->request, $this->template, $this->util);
	}

	public function test_name()
	{
		$field = $this->get_form_field('range');
		$this->assertEquals('range', $field->get_name());
	}

	public function test_langname()
	{
		$field = $this->get_form_field('range');
		$this->assertEquals('FORM_FIELD_RANGE', $field->get_langname());
	}

	public function test_default_props()
	{
		$field = $this->get_form_field('range');
		$this->assertEquals(array(
			'display'	=> 'text',
			'type'		=> 'single',
			'skin'		=> 'flat',
			'size'		=> 100,
			'values'	=> '',
			'prefix'	=> '',
			'postfix'	=> '',
			'min'		=> '',
			'max'		=> '',
			'from'		=> '',
			'to'		=> '',
			'step'		=> 1,
			'grid'		=> false,
		), $field->get_default_props());
	}

	/**
	 * @return array
	 */
	public function display_range_field_test_data()
	{
		return array(
			array(
				'summary',
				array(
					'field_value'	=> '',
					'field_props'	=> array(),
				),
				'',
			),
			array(
				'summary',
				array(
					'field_value'	=> '19',
					'field_props'	=> array(
						'display'	=> 'text',
						'type'		=> 'single',
						'prefix'	=> '',
						'postfix'	=> '',
					),
				),
				19,
			),
			array(
				'summary',
				array(
					'field_value'	=> '19',
					'field_props'	=> array(
						'display'	=> 'text',
						'type'		=> 'single',
						'prefix'	=> '$',
						'postfix'	=> ' per oz',
					),
				),
				'$19 per oz',
			),
			array(
				'summary',
				array(
					'field_value'	=> '19',
					'field_props'	=> array(
						'display'	=> 'text',
						'type'		=> 'double',
						'prefix'	=> '$',
						'postfix'	=> '',
					),
				),
				'$19',
			),
			array(
				'summary',
				array(
					'field_value'	=> '25;55',
					'field_props'	=> array(
						'display'	=> 'text',
						'type'		=> 'double',
						'prefix'	=> '$',
						'postfix'	=> ' per oz',
					),
				),
				'$25 - $55 per oz',
			),
			array(
				'print',
				array(
					'field_name'	=> 'foo',
					'field_value'	=> '15;35',
					'field_props'	=> array(
						'display'	=> 'slider',
						'type'		=> 'double',
						'skin'		=> 'modern',
						'size'		=> 50,
						'values'	=> '',
						'prefix'	=> '$',
						'postfix'	=> ' per Ib',
						'min'		=> '0',
						'max'		=> '100',
						'step'		=> 1,
						'grid'		=> false,
					),
				),
				'$15 - $35 per Ib',
			),
			array(
				'summary',
				array(
					'field_name'	=> 'foo',
					'field_value'	=> '15;35',
					'field_props'	=> array(
						'display'	=> 'slider',
						'type'		=> 'double',
						'skin'		=> 'modern',
						'size'		=> 50,
						'values'	=> '',
						'prefix'	=> '$',
						'postfix'	=> ' per Ib',
						'min'		=> '0',
						'max'		=> '100',
						'step'		=> 1,
						'grid'		=> false,
					),
				),
				'<div style="width: 50%">' .
					'<input type="text" class="inputbox autowidth rangepicker" id="smc-foo" name="foo" data-type="double" data-values="" data-prefix="$" data-postfix=" per Ib" data-min="0" data-max="100" data-step="1" data-grid="false" data-from="" data-to="" data-force-edges="true" data-disable="true" data-skin="modern" value="15;35" />' .
					'</div>',
			),
		);
	}

	/**
	 * @dataProvider display_range_field_test_data
	 * @param string $display_mode
	 * @param array $data
	 * @param string $expected
	 * @return void
	 */
	public function test_display_range_field($display_mode, array $data, $expected)
	{
		$field = $this->get_form_field('range');

		$data = $this->get_data('range', $data, $field->get_default_props());
		$data['field_value'] = $field->get_field_value($data);

		$this->util->expects($this->exactly(($data['field_props']['display'] === 'slider' && $display_mode !== 'print') ? 2 : 0))
			->method('add_assets');

		$this->assertEquals($expected, str_replace(array("\n", "\t"), '', $field->display_field($data, array(), $display_mode, 'summary')));
	}

	/**
	 * @return array
	 */
	public function show_range_field_test_data()
	{
		return array(
			array(
				array(
					'field_name'	=> 'foo',
					'field_value'	=> '',
					'field_props'	=> array(),
				),
				array(
					array('foo', '', true, request_interface::REQUEST, ''),
				),
				'<div style="width: 100%">' .
					'<input type="text" class="inputbox autowidth rangepicker" id="smc-foo" name="foo" data-type="single" data-values="" data-prefix="" data-postfix="" data-min="0" data-max="" data-step="1" data-grid="false" data-from="" data-to="" data-force-edges="true" data-skin="flat" value="" />' .
					'</div>',
			),
			array(
				array(
					'field_name'	=> 'foo',
					'field_value'	=> '15;35',
					'field_props'	=> array(
						'type'		=> 'double',
						'skin'		=> 'big',
						'size'		=> 50,
						'values'	=> '',
						'prefix'	=> '$',
						'postfix'	=> ' per Ib',
						'min'		=> '0',
						'max'		=> '100',
						'step'		=> 1,
						'grid'		=> false,
					),
				),
				array(
					array('foo', '15;35', true, request_interface::REQUEST, '25;55'),
				),
				'<div style="width: 50%">' .
					'<input type="text" class="inputbox autowidth rangepicker" id="smc-foo" name="foo" data-type="double" data-values="" data-prefix="$" data-postfix=" per Ib" data-min="0" data-max="100" data-step="1" data-grid="false" data-from="" data-to="" data-force-edges="true" data-skin="big" value="25;55" />' .
					'</div>',
			),
			array(
				array(
					'field_name'	=> 'bar',
					'field_value'	=> 'test2',
					'field_props'	=> array(
						'type'		=> 'single',
						'size'		=> 75,
						'values'	=> 'test1, test2, test3',
						'grid'		=> true,
					),
				),
				array(
					array('bar', 'test2', true, request_interface::REQUEST, 'test3'),
				),
				'<div style="width: 75%">' .
					'<input type="text" class="inputbox autowidth rangepicker" id="smc-bar" name="bar" data-type="single" data-values="test1, test2, test3" data-prefix="" data-postfix="" data-min="0" data-max="" data-step="1" data-grid="1" data-from="2" data-to="" data-force-edges="true" data-skin="flat" value="test3" />' .
					'</div>',
			),
		);
	}

	/**
	 * @dataProvider show_range_field_test_data
	 * @param array $data
	 * @param array $variable_map
	 * @param string $expected
	 * @return void
	 */
	public function test_show_range_field(array $data, array $variable_map, $expected)
	{
		$field = $this->get_form_field('range', $variable_map);

		$data = $this->get_data('range', $data, $field->get_default_props());
		$data['field_value'] = $field->get_submitted_value($data);

		$this->util->expects($this->exactly(2))
			->method('add_assets');

		$this->assertEquals($expected, str_replace(array("\n", "\t"), '', $field->show_form_field($data)));
	}
}
