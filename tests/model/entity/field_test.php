<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\model\entity;

use blitze\content\model\entity\field;

class field_test extends \phpbb_test_case
{
	protected $trans;

	/**
	 * Define the extension to be tested.
	 *
	 * @return string[]
	 */
	protected static function setup_extensions()
	{
		return array('blitze/content');
	}

	/**
	 * Configure the test environment.
	 *
	 * @return void
	 */
	public function setUp()
	{
		global $cache, $phpbb_dispatcher, $user;

		parent::setUp();

		$cache = new \phpbb_mock_cache();
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		$this->translator = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$this->translator->expects($this->any())
			->method('lang')
			->willReturnCallback(function () {
				return implode('-', func_get_args());
			});

		$user = new \phpbb\user($this->translator, '\phpbb\datetime');
	}

	/**
	 * Test exception on required fields
	 */
	public function test_required_fields()
	{
		$required_fields = array('content_id', 'field_name', 'field_label', 'field_type');
		$data = array(
			'content_id'			=> 1,
			'field_name'			=> 'test',
			'field_label'			=> 'Test',
			'field_type'			=> 'text',
		);

		foreach ($required_fields as $field)
		{
			$test_data = $data;
			unset($test_data[$field]);

			$entity = new field($test_data);

			try
			{
				$entity->to_db();
				$this->fail('no exception thrown');
			}
			catch (\blitze\sitemaker\exception\invalid_argument $e)
			{
				$this->assertEquals("EXCEPTION_INVALID_ARGUMENT-{$field}-FIELD_MISSING", $e->get_message($this->translator));
			}
		}
	}

	function test_id_only_set_once()
	{
		$field = new field(array());

		$id = 1;
		$field->set_field_id($id);
		$this->assertEquals($id, $field->get_field_id());

		$another_id = 2;
		$this->assertNotEquals($id, $another_id);

		$field->set_field_id($another_id);
		$this->assertEquals($id, $field->get_field_id());
	}

	/**
	 * Data set for test_accessors_and_mutators
	 *
	 * @return array
	 */
	public function accessors_and_mutators_test_data()
	{
		return array(
			array('content_id', null, 1, 1, 2, 2),
			array('field_name', null, 'foo', 'foo', 'bar', 'bar'),
			array('field_label', null, 'foo', 'Foo', 'bar', 'Bar'),
			array('field_explain', '', 'my field', 'my field', 'some field', 'some field'),
			array('field_type', null, 'text', 'text', 'email', 'email'),
			array('field_props', array(), '', array(), '{"size":"large","editor":"1","max_chars":"100"}', array('size' => 'large', 'editor' => '1', 'max_chars' => '100')),
			array('field_mod_only', false, true, true, false, false),
			array('field_required', false, true, true, false, false),
			array('field_summary_show', '', 'body', 'body', 'above', 'above'),
			array('field_detail_show', '', 'body', 'body', 'above', 'above'),
			array('field_summary_ldisp', 1, 2, 2, 0, 0),
			array('field_detail_ldisp', 1, 2, 2, 0, 0),
			array('field_exp_uid', '', 'ee123', 'ee123', '', ''),
			array('field_exp_bitfield', '', 'foo', 'foo', '', ''),
			array('field_exp_options', 7, 0, 0, 9, 9),
			array('field_order', 0, 1, 1, 2, 2),
		);
	}

	/**
	 * Test entity accessor and mutator
	 *
	 * @dataProvider accessors_and_mutators_test_data
	 * @param $property
	 * @param $default
	 * @param $value1
	 * @param $expect1
	 * @param $value2
	 * @param $expect2
	 */
	public function test_accessors_and_mutators($property, $default, $value1, $expect1, $value2, $expect2)
	{
		$mutator = 'set_' . $property;
		$accessor = 'get_' . $property;

		$field = new field(array());

		$this->assertEquals($default, $field->$accessor());

		$result = $field->$mutator($value1);
		$this->assertSame($expect1, $field->$accessor());
		$this->assertInstanceOf('\blitze\content\model\entity\field', $result);

		$field->$mutator($value2);
		$this->assertNotSame($expect1, $field->$accessor());
		$this->assertSame($expect2, $field->$accessor());
	}

	/**
	 *
	 */
	function test_bad_get_set_exceptions()
	{
		$field = new field(array());

		try
		{
			$this->assertNull($field->get_foo());
			$this->fail('no exception thrown');
		}
		catch (\blitze\sitemaker\exception\invalid_argument $e)
		{
			$this->assertEquals('EXCEPTION_INVALID_ARGUMENT-foo-INVALID_PROPERTY', $e->get_message($this->translator));
		}

		try
		{
			$this->assertNull($field->set_foo('bar'));
			$this->fail('no exception thrown');
		}
		catch (\blitze\sitemaker\exception\invalid_argument $e)
		{
			$this->assertEquals('EXCEPTION_INVALID_ARGUMENT-foo-INVALID_PROPERTY', $e->get_message($this->translator));
		}
	}

	/**
	 *
	 */
	function test_to_array()
	{
		$block = new field(array(
			'field_id'				=> 2,
			'content_id'			=> 1,
			'field_name'			=> 'test',
			'field_label'			=> 'test',
			'field_explain'			=> 'my [b]test[/b] field',
			'field_type'			=> 'textarea',
			'field_props'		=> array('size' => 'large', 'editor' => 1, 'max_chars' => 200),
			'field_mod_only'		=> false,
			'field_required'		=> true,
			'field_summary_show'	=> true,
			'field_detail_show'		=> true,
			'field_summary_ldisp'	=> false,
			'field_detail_ldisp'	=> false,
			'field_order'			=> 0,
		));

		$to_array_expected = array(
			'field_id'				=> 2,
			'content_id'			=> 1,
			'field_name'			=> 'test',
			'field_label'			=> 'Test',
			'field_explain'			=> 'my [b]test[/b] field',
			'field_type'			=> 'textarea',
			'field_props'		=> array('size' => 'large', 'editor' => 1, 'max_chars' => 200),
			'field_mod_only'		=> false,
			'field_required'		=> true,
			'field_summary_show'	=> true,
			'field_detail_show'		=> true,
			'field_summary_ldisp'	=> false,
			'field_detail_ldisp'	=> false,
			'field_exp_uid'			=> '',
			'field_exp_bitfield'	=> '',
			'field_exp_options'		=> 7,
			'field_order'			=> 0,
		);

		$to_db_expected = array(
			'content_id'			=> 1,
			'field_name'			=> 'test',
			'field_label'			=> 'Test',
			'field_explain'			=> 'my [b]test[/b] field',
			'field_type'			=> 'textarea',
			'field_props'		=> '{"size":"large","editor":1,"max_chars":200}',
			'field_mod_only'		=> false,
			'field_required'		=> true,
			'field_summary_show'	=> true,
			'field_detail_show'		=> true,
			'field_summary_ldisp'	=> false,
			'field_detail_ldisp'	=> false,
			'field_exp_uid'			=> '',
			'field_exp_bitfield'	=> '',
			'field_exp_options'		=> 7,
			'field_order'			=> 0,
		);

		$this->assertEquals($to_array_expected, $block->to_array());
		$this->assertEquals($to_db_expected, $block->to_db());
	}
}
