<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\services\form;

use blitze\content\services\form\fields_factory;
use blitze\content\services\form\field\checkbox;
use blitze\content\services\form\field\hidden;
use blitze\content\services\form\field\number;

class fields_factory_test extends \phpbb_test_case
{
	/**
	 * Define the extension to be tested.
	 *
	 * @return string[]
	 */
	protected static function setup_extensions()
	{
		return array(
			'blitze/sitemaker',
			'blitze/content',
		);
	}

	/**
	 * Configure the test environment.
	 *
	 * @return void
	 */
	public function setUp()
	{
		global $phpbb_container;

		parent::setUp();

		$language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$language->expects($this->any())
			->method('lang')
			->willReturnCallback(function () {
				return implode('-', func_get_args());
			});

		$request = $this->getMock('\phpbb\request\request_interface');

		$ptemplate = $this->getMockBuilder('\blitze\sitemaker\services\template')
			->disableOriginalConstructor()
			->getMock();

		$phpbb_container = new \phpbb_mock_container_builder();
		$phpbb_container->set('my.checkbox.field', new checkbox($language, $request, $ptemplate));
		$phpbb_container->set('my.hidden.field', new hidden($language, $request, $ptemplate));
		$phpbb_container->set('my.number.field', new number($language, $request, $ptemplate));

		$collection = new \phpbb\di\service_collection($phpbb_container);
		$collection->add('my.checkbox.field');
		$collection->add('my.hidden.field');
		$collection->add('my.number.field');

		$this->factory = new fields_factory($language, $collection);
	}

	/**
	 * Test get_all method
	 */
	public function test_get_all_fields()
	{
		$expected = array('checkbox', 'hidden', 'number');
		$this->assertSame($expected, array_keys($this->factory->get_all()));
	}

	/**
	 * @return array
	 */
	public function get_field_test_data()
	{
		return array(
			array(
				'checkbox',
				'\blitze\content\services\form\field\checkbox',
			),
			array(
				'number',
				'\blitze\content\services\form\field\number',
			),
			array(
				'no_exist',
				null,
			),
		);
	}

	/**
	 * @dataProvider get_field_test_data
	 * @param string $field_name
	 * @param string $expected_instance
	 * @return void
	 */
	public function test_get_field($field_name, $expected_instance)
	{
		if ($this->factory->exists($field_name))
		{
			$instance = $this->factory->get($field_name);
			$this->assertInstanceOf($expected_instance, $instance);
		}
		else
		{
			$this->assertNull($expected_instance);
		}
	}

	/**
	 * Test get_options method
	 */
	public function test_get_options()
	{
		$expected = array(
			'checkbox'	=> 'FORM_FIELD_CHECKBOX',
			'hidden'	=> 'FORM_FIELD_HIDDEN',
			'number'	=> 'FORM_FIELD_NUMBER',
		);

		$this->assertEquals($expected, $this->factory->get_options());
	}
}
