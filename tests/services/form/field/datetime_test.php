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
use blitze\content\services\form\field\datetime;

class datetime_test extends base_form_field
{
	protected $util;

	/**
	 * Create the form field service
	 *
	 * @param array $variable_map
	 * @return \blitze\content\services\form\field\field_interface
	 */
	protected function get_form_field($type, array $variable_map = array())
	{
		$this->request->expects($this->any())
			->method('variable')
			->with($this->anything())
			->will($this->returnValueMap($variable_map));

		$this->util = $this->getMockBuilder('\blitze\sitemaker\services\util')
			->disableOriginalConstructor()
			->getMock();

		return new datetime($this->language, $this->request, $this->ptemplate, $this->user, $this->util);
	}

	public function test_name()
	{
		$field = $this->get_form_field('datetime');
		$this->assertEquals('datetime', $field->get_name());
	}

	public function test_langname()
	{
		$field = $this->get_form_field('datetime');
		$this->assertEquals('FORM_FIELD_DATETIME', $field->get_langname());
	}

	public function test_default_props()
	{
		$field = $this->get_form_field('datetime');
		$this->assertEquals(array(
			'type'		=> 'datetime',
			'range'		=> false,
			'num_dates'	=> 1,
			'min_date'	=> '',
			'max_date'	=> '',
			'oformat'	=> '',
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
		$field = $this->get_form_field('datetime');
		$data = array('field_value' => $field_value);
		$this->assertEquals($expected, $field->display_field($data));
	}

	/**
	 * @return array
	 */
	public function show_datetime_field_test_data()
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
				'<div style="width: 10%; max-width: 100%;">' .
					'<input type="text" id="smc-foo" name="foo" class="inputbox datetimepicker" data-date-format="mm/dd/yyyy" data-timepicker="true" data-range="" value="" />' .
				'</div>'
			),
			array(
				'foo',
				array(
					'field_name'	=> 'foo',
					'field_value'	=> 'bar',
					'field_props'	=> array(
						'type'		=> 'date',
						'range'		=> true,
						'num_dates'	=> 2,
						'min_date'	=> 'today',
						'max_date'	=> '+2 weeks',
					),
				),
				array(
					array('foo', 'bar', true, request_interface::REQUEST, 'bar'),
				),
				'<div style="width: 10%; max-width: 100%;">' .
					'<input type="text" id="smc-foo" name="foo" class="inputbox datetimepicker" data-date-format="mm/dd/yyyy" data-multiple-dates="2" data-range="1" data-min-date="' . date("m/d/Y") . '" data-max-date="'. date("m/d/Y", strtotime('+2 weeks')) . '" value="bar" />' .
				'</div>',
			),
			array(
				'foo',
				array(
					'field_name'	=> 'foo',
					'field_value'	=> 'bar',
					'field_props'	=> array(
						'type'			=> 'timeonly',
					),
				),
				array(
					array('foo', 'bar', true, request_interface::REQUEST, 'foo_bar'),
				),
				'<div style="width: 10%; max-width: 100%;">' .
					'<input type="text" id="smc-foo" name="foo" class="inputbox datetimepicker" data-date-format="" data-timepicker="true" data-only-timepicker="true" data-range="" value="foo_bar" />' .
				'</div>'
			),
			array(
				'foo',
				array(
					'field_name'	=> 'foo',
					'field_value'	=> 'bar',
					'field_props'	=> array(
						'type'			=> 'month',
					),
				),
				array(
					array('foo', 'bar', true, request_interface::REQUEST, 'foo_bar'),
				),
				'<div style="width: 10%; max-width: 100%;">' .
					'<input type="text" id="smc-foo" name="foo" class="inputbox datetimepicker" data-date-format="" data-range="" value="foo_bar" />' . 
				'</div>'
			),
			array(
				'foo2',
				array(
					'field_name'	=> 'foo2',
					'field_value'	=> 'bar',
					'field_props'	=> array(
						'type'			=> 'year',
					),
				),
				array(
					array('foo2', 'bar', true, request_interface::REQUEST, 'foo_bar'),
				),
				'<div style="width: 10%; max-width: 100%;">' .
					'<input type="text" id="smc-foo2" name="foo2" class="inputbox datetimepicker" data-date-format="" data-range="" value="foo_bar" />' . 
				'</div>'
			),
		);
	}

	/**
	 * @dataProvider show_datetime_field_test_data
	 * @param string $name
	 * @param array $data
	 * @param array $variable_map
	 * @param string $expected
	 * @return void
	 */
	public function test_show_datetime_field($name, array $data, array $variable_map, $expected)
	{
		$field = $this->get_form_field('datetime', $variable_map);
		$data = $this->get_data('datetime', $name, $data, $field->get_default_props());

		$this->util->expects($this->once())
			->method('add_assets');

		$this->assertEquals($expected, str_replace(array("\r\n", "\r", "\n", "\t"), '', $field->show_form_field($name, $data)));
	}
}
