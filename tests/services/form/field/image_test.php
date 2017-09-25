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
use blitze\content\services\form\field\image;

class image_test extends base_form_field
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

		return new image($this->language, $this->request, $this->ptemplate, $this->util);
	}

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
			'default'		=> '',
			'detail_align'	=> '',
			'detail_size'	=> '',
			'summary_align'	=> '',
			'summary_size'	=> '',
		), $field->get_default_props());
	}

	/**
	 * @return array
	 */
	public function display_field_test_data()
	{
		return array(
			array('', 'summary', array(), ''),
			array('', 'detail', array(), ''),
			array('', 'summary', array('summary_size' => 'medium-img'), ''),
			array('', 'detail', array('detail_align' => 'img-align-left'), ''),
			array(
				'',
				'summary',
				array(
					'default'	=> 'bar',
				),
				'<div class=""><figure class="img-ui"><img src="bar" class="postimage" alt="Image" /></figure></div>',
			),
			array(
				'',
				'summary',
				array(
					'default'	=> 'bar',
					'summary_size'	=> 'fullwidth-img',
				),
				'<div class="fullwidth-img"><figure class="img-ui"><img src="bar" class="postimage" alt="Image" /></figure></div>',
			),
			array(
				'<img src="foo" class="postimage" alt="Image" />',
				'summary',
				array(),
				'<div class=""><figure class="img-ui"><img src="foo" class="postimage" alt="Image" /></figure></div>',
			),
			array(
				'<img src="bar" class="postimage" alt="Image" />',
				'detail',
				array(),
				'<div class=""><figure class="img-ui"><img src="bar" class="postimage" alt="Image" /></figure></div>',
			),
			array(
				'<img src="foo" class="postimage" alt="Image" />',
				'summary',
				array(
					'detail_size'	=> 'medium-img',
					'summary_size'	=> 'fullwidth-img',
				),
				'<div class="fullwidth-img"><figure class="img-ui"><img src="foo" class="postimage" alt="Image" /></figure></div>',
			),
			array(
				'<img src="bar" class="postimage" alt="Image" />',
				'detail',
				array(
					'detail_align'	=> 'img-align-left',
					'summary_align'	=> 'img-align-right',
					'detail_size'	=> 'medium-img',
					'summary_size'	=> 'fullwidth-img',
				),
				'<div class="img-align-left medium-img"><figure class="img-ui"><img src="bar" class="postimage" alt="Image" /></figure></div>',
			),
		);
	}

	/**
	 * @dataProvider display_field_test_data
	 * @param string $field_value
	 * @param string $view summary|detail|block
	 * @param array $field_props
	 * @param string $expected
	 * @return void
	 */
	public function test_display_field($field_value, $view, $field_props, $expected)
	{
		$field = $this->get_form_field('image');
		$data = array(
			'field_value' => $field_value,
			'field_props' => $field_props + $field->get_default_props()
		);

		$this->assertEquals($expected, $field->display_field($data, $view));
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
				'<input type="text" class="inputbox autowidth image-field" id="smc-foo" name="foo" value="" size="45" />' .
				'<div class="medium-img"><div id="preview-foo" class="img-ui"></div></div>',
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
				'<input type="text" class="inputbox autowidth image-field" id="smc-foo" name="foo" value="bar" size="45" />' .
				'<div class="medium-img"><div id="preview-foo" class="img-ui"><img src="bar" alt="" /></div></div>',
			),
			array(
				'foo2',
				array(
					'field_value' => 'bar',
				),
				array(
					array('foo2', 'bar', false, request_interface::REQUEST, 'foo_bar'),
				),
				'<input type="text" class="inputbox autowidth image-field" id="smc-foo2" name="foo2" value="foo_bar" size="45" />' .
				'<div class="medium-img"><div id="preview-foo2" class="img-ui"><img src="foo_bar" alt="" /></div></div>',
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
