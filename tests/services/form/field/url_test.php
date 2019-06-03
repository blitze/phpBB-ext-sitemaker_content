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

class url_test extends base_form_field
{
	public function test_name()
	{
		$field = $this->get_form_field('url');
		$this->assertEquals('url', $field->get_name());
	}

	public function test_langname()
	{
		$field = $this->get_form_field('url');
		$this->assertEquals('FORM_FIELD_URL', $field->get_langname());
	}

	public function test_default_props()
	{
		$field = $this->get_form_field('url');
		$this->assertEquals(array(), $field->get_default_props());
	}

	/**
	 * @return array
	 */
	public function display_field_test_data()
	{
		return array(
			array('', ''),
			array('http://www.google.com', '<!-- l --><a class="postlink-local" href="http://www.google.com"><!-- w --><a class="postlink" href="http://www.google.com">www.google.com</a><!-- w --></a><!-- l -->'),
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
		$field = $this->get_form_field('url');

		$data = array('field_value' => $field_value);
		$data['field_value'] = $field->get_field_value($data);

		$this->assertEquals($expected, $field->display_field($data, array(), 'summary', 'summary'));
	}

	/**
	 * @return array
	 */
	public function show_url_field_test_data()
	{
		return array(
			array(
				array(
					'field_name'	=> 'foo',
					'field_value'	=> '',
				),
				array(
					array('foo', '', true, request_interface::REQUEST, ''),
				),
				'<input type="url" class="inputbox autowidth" id="smc-foo" name="foo" size="40" value="" />',
			),
			array(
				array(
					'field_name'	=> 'foo',
					'field_value'	=> 'bar',
				),
				array(
					array('foo', 'bar', true, request_interface::REQUEST, 'bar'),
				),
				'<input type="url" class="inputbox autowidth" id="smc-foo" name="foo" size="40" value="bar" />',
			),
			array(
				array(
					'field_name'	=> 'foo2',
					'field_value'	=> 'bar',
				),
				array(
					array('foo2', 'bar', true, request_interface::REQUEST, 'foo_bar'),
				),
				'<input type="url" class="inputbox autowidth" id="smc-foo2" name="foo2" size="40" value="foo_bar" />',
			),
		);
	}

	/**
	 * @dataProvider show_url_field_test_data
	 * @param array $data
	 * @param array $variable_map
	 * @param string $expected
	 * @return void
	 */
	public function test_show_url_field(array $data, array $variable_map, $expected)
	{
		$field = $this->get_form_field('url', $variable_map);

		$data = $this->get_data('url', $data, $field->get_default_props());
		$data['field_value'] = $field->get_submitted_value($data);

		$this->assertEquals($expected, $field->show_form_field($data));
	}

	/**
	 * @return array
	 */
	public function field_validation_test_data()
	{
		return array(
			array(
				array(
					'field_value'	=> 'invalid',
					'field_label'	=> 'Foo',
				),
				'FIELD_INVALID Foo',
			),
			array(
				array(
					'field_value'	=> './foo.php',
					'field_label'	=> 'Boo',
				),
				'FIELD_INVALID Boo',
			),
			array(
				array(
					'field_value'	=> 'www.foo.com',
					'field_label'	=> 'Bar',
				),
				'FIELD_INVALID Bar',
			),
			array(
				array(
					'field_value'	=> 'http://www.foo.com',
					'field_label'	=> 'Baz',
				),
				'',
			),
		);
	}

	/**
	 * @dataProvider field_validation_test_data
	 * @param array $data
	 * @param string $expected
	 * @return void
	 */
	public function test_field_validation(array $data, $expected)
	{
		$field = $this->get_form_field('url');
		$this->assertEquals($expected, $field->validate_field($data));
	}
}
