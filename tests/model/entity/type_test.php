<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\model\entity;

use blitze\content\model\entity\type;

class type_test extends \phpbb_test_case
{
	protected $translator;

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
		$required_fields = array('forum_id', 'content_name', 'content_langname', 'content_view');
		$data = array(
			'forum_id'			=> 10,
			'content_name'		=> 'foo',
			'content_langname'	=> 'Foo',
			'content_view'		=> 'blitze.content.view.portal',
		);

		foreach ($required_fields as $field)
		{
			$test_data = $data;
			unset($test_data[$field]);

			$entity = new type($test_data);

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
		$content_type = new type(array());

		$id = 10;
		$content_type->set_content_id($id);
		$this->assertEquals($id, $content_type->get_content_id());

		$another_id = 20;
		$this->assertNotEquals($id, $another_id);

		$content_type->set_content_id($another_id);
		$this->assertEquals($id, $content_type->get_content_id());
	}

	/**
	 * Data set for test_accessors_and_mutators
	 *
	 * @return array
	 */
	public function accessors_and_mutators_test_data()
	{
		return array(
			array('forum_id', null, 1, 1, 2, 2),
			array('content_name', null, 'foo', 'foo', 'bar', 'bar'),
			array('content_langname', null, 'foo', 'Foo', 'bar', 'Bar'),
			array('content_enabled', true, false, false, true, true),
			array('content_colour', '', '333', '333', '#ccc', '#ccc'),
			array('content_desc', '', 'my content type', 'my content type', 'some other type', 'some other type'),
			array('content_desc_bitfield', '', 'ed1', 'ed1', '', ''),
			array('content_desc_options', 7, 2, 2, 5, 5),
			array('content_desc_uid', '', 'dd3d', 'dd3d', '', ''),
			array('content_view', null, 'blog', 'blog', 'portal', 'portal'),
			array('content_view_settings', array(), '', array(), '{"foo": "bar"}', array('foo' => 'bar')), 
			array('req_approval', false, true, true, false, false),
			array('allow_comments', true, false, false, true, true),
			array('allow_views', true, false, false, true, true),
			array('topic_blocks', array(), 'foo.service', array('foo.service'), array('foo.service', 'bar.service'), array('foo.service', 'bar.service')),
			array('show_pagination', true, false, false, true, true),
			array('index_show_desc', false, true, true, false, false),
			array('items_per_page', 10, 1, 1, 5, 5),
			array('summary_tpl', '', 'some template', 'some template', '<img src="{{ image }}" />', '<img src="{{ image }}" />'),
			array('detail_tpl', '', 'detail template', 'detail template', '<a href="">{{ title }}</a>', '<a href="">{{ title }}</a>'),
			array('last_modified', 0, 23423434, 23423434, 0, 0),
		);
	}

	/**
	 * Test entity accessor and mutator
	 *
	 * @dataProvider accessors_and_mutators_test_data
	 */
	public function test_accessors_and_mutators($property, $default, $value1, $expect1, $value2, $expect2)
	{
		$mutator = 'set_' . $property;
		$accessor = 'get_' . $property;

		$content_type = new type(array());

		$this->assertSame($default, $content_type->$accessor());

		$result = $content_type->$mutator($value1);
		$this->assertSame($expect1, $content_type->$accessor());
		$this->assertInstanceOf('\blitze\content\model\entity\type', $result);

		$content_type->$mutator($value2);
		$this->assertNotSame($expect1, $content_type->$accessor());
		$this->assertSame($expect2, $content_type->$accessor());
	}

	function test_bad_get_set_exceptions()
	{
		$content_type = new type(array());

		try
		{
			$this->assertNull($content_type->get_foo());
			$this->fail('no exception thrown');
		}
		catch (\blitze\sitemaker\exception\invalid_argument $e)
		{
			$this->assertEquals('EXCEPTION_INVALID_ARGUMENT-foo-INVALID_PROPERTY', $e->get_message($this->translator));
		}

		try
		{
			$this->assertNull($content_type->set_foo('bar'));
			$this->fail('no exception thrown');
		}
		catch (\blitze\sitemaker\exception\invalid_argument $e)
		{
			$this->assertEquals('EXCEPTION_INVALID_ARGUMENT-foo-INVALID_PROPERTY', $e->get_message($this->translator));
		}
	}

	function test_to_array()
	{
		$content_type = new type(array(
			'forum_id'			=> 5,
			'content_name'		=> 'test',
			'content_langname'	=> 'test',
			'content_view'		=> 'blog',
			'content_fields'	=> array(
				'image'	=> array(
					'field_type'			=> 'image',
					'field_summary_show'	=> true,
					'field_detail_show'		=> true,
				),
				'exerpt'	=> array(
					'field_type'			=> 'textarea',
					'field_summary_show'	=> true,
					'field_detail_show'		=> false,
				),
				'content'	=> array(
					'field_type'			=> 'textarea',
					'field_summary_show'	=> false,
					'field_detail_show'		=> true,
				),
			),
			'topic_blocks'		=> array('foo', 'bar'),
			'summary_tpl'		=> '<img src="{{ image }}" class="leftbox" />{{ exerpt }}',
			'detail_tpl'		=> '<img src="{{ image }} /><br />{{ content }}',
		));

		$to_array_expected = array(
			'content_id'			=> null,
			'forum_id'				=> 5,
			'content_name'			=> 'test',
			'content_langname'		=> 'Test',
			'content_enabled'		=> true,
			'content_colour'		=> '098f6b',
			'content_desc'			=> '',
			'content_desc_bitfield' => '',
			'content_desc_options'	=> 7,
			'content_desc_uid'		=> '',
			'content_view'			=> 'blog',
			'content_view_settings'	=> array(),
			'req_approval'			=> false,
			'allow_comments'		=> true,
			'allow_views'			=> true,
			'show_pagination'		=> true,
			'index_show_desc'		=> false,
			'items_per_page'		=> 10,
			'summary_tpl'			=> '<img src="{{ image }}" class="leftbox" />{{ exerpt }}',
			'detail_tpl'			=> '<img src="{{ image }} /><br />{{ content }}',
			'last_modified'			=> 0,
			'topic_blocks'			=> array('foo', 'bar'),
			'content_fields'		=> array(
				'image'	=> array(
					'field_type'			=> 'image',
					'field_summary_show'	=> true,
					'field_detail_show'		=> true,
				),
				'exerpt'	=> array(
					'field_type'			=> 'textarea',
					'field_summary_show'	=> true,
					'field_detail_show'		=> false,
				),
				'content'	=> array(
					'field_type'			=> 'textarea',
					'field_summary_show'	=> false,
					'field_detail_show'		=> true,
				),
			),
			'field_types'			=> array(
				'image'		=> 'image',
				'exerpt'	=> 'textarea',
				'content'	=> 'textarea',
			),
			'summary_fields'		=> array(
				'image'		=> 'image',
				'exerpt'	=> 'textarea',
			),
			'detail_fields'			=> array(
				'image'		=> 'image',
				'content'	=> 'textarea',
			),
		);

		$to_db_expected = array(
			'forum_id'				=> 5,
			'content_name'			=> 'test',
			'content_langname'		=> 'Test',
			'content_enabled'		=> true,
			'content_colour'		=> '098f6b',
			'content_desc'			=> '',
			'content_desc_bitfield'	=> '',
			'content_desc_options'	=> 7,
			'content_desc_uid'		=> '',
			'content_view'			=> 'blog',
			'content_view_settings'	=> '',
			'req_approval'			=> false,
			'allow_comments'		=> true,
			'allow_views'			=> true,
			'show_pagination'		=> true,
			'index_show_desc'		=> false,
			'items_per_page'		=> 10,
			'summary_tpl'			=> '<img src="{{ image }}" class="leftbox" />{{ exerpt }}',
			'detail_tpl'			=> '<img src="{{ image }} /><br />{{ content }}',
			'last_modified'			=> 0,
			'topic_blocks'			=> 'foo,bar',
		);

		$this->assertSame($to_array_expected, $content_type->to_array());
		$this->assertSame($to_db_expected, $content_type->to_db());
	}
}
