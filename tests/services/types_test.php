<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\services;

use blitze\content\services\types;
use blitze\content\model\mapper_factory;

class types_test extends \phpbb_database_test_case
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
	 * Load required fixtures.
	 *
	 * @return mixed
	 */
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/types.xml');
	}

	/**
	 * Create the types service
	 *
	 * @return \blitze\content\services\types
	 */
	protected function get_service()
	{
		global $phpbb_dispatcher, $user;

		$table_prefix = 'phpbb_';
		$tables = array(
			'mapper_tables'		=> array(
				'fields'	=> $table_prefix . 'sm_content_fields',
				'types'		=> $table_prefix . 'sm_content_types',
			)
		);

		$cache = new \phpbb_mock_cache();
		$config = new \phpbb\config\config(array());
		$db = $this->new_dbal();
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		$user = $this->getMockBuilder('\phpbb\user')
			->disableOriginalConstructor()
			->getMock();

		$this->language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$this->language->expects($this->any())
			->method('lang')
			->willReturnCallback(function () {
				return implode('-', func_get_args());
			});

		$mapper_factory = new mapper_factory($db, $tables);

		return new types($cache, $mapper_factory);
	}

	/**
	 * Test get_all_types method
	 */
	public function test_get_all_types()
	{
		$types = $this->get_service();

		$expected_fields = array(
			'news'		=> array('image', 'colors', 'news'),
			'articles'	=> array('article'),
		);

		$data = $types->get_all_types('data');
		foreach ($data as $type => $entity)
		{
			$this->assertInstanceOf('\blitze\content\model\entity\type', $entity);
			$this->assertSame($expected_fields[$type], array_keys($entity->get_content_fields()));
		}

		$expected_forums = array(
			7 => 'news',
			8 => 'articles',
		);

		$forums = $types->get_all_types('forums');
		$this->assertSame($expected_forums, $forums);
	}

	/**
	 * Test data for get_type test
	 */
	public function get_type_test_data()
	{
		return array(
			array('news', false, false),
			array('articles', true, false),
			array('foo', false, true),
			array('foo', true, true),
		);
	}

	/**
	 * Test get specific content type
	 *
	 * @dataProvider get_type_test_data
	 * @param $type
	 * @param $trigger_error
	 * @param $expected_to_fail
	 */
	public function test_get_type($type, $trigger_error, $expected_to_fail)
	{
		$types = $this->get_service();

		try
		{
			$result = $types->get_type($type, $trigger_error);

			if ($trigger_error && $expected_to_fail)
			{
				$this->fail('no exception thrown');
			}
			else if ($expected_to_fail)
			{
				$this->assertFalse($result);
			}
			else
			{
				$this->assertInstanceOf('\blitze\content\model\entity\type', $result);
				$this->assertEquals($type, $result->get_content_name());
			}
		}
		catch (\blitze\sitemaker\exception\out_of_bounds $e)
		{
			$this->assertEquals("EXCEPTION_OUT_OF_BOUNDS-{$type}", $e->get_message($this->language));
		}
	}

	/**
	 * Test the exists method
	 */
	public function test_exits()
	{
		$types = $this->get_service();

		$this->assertTrue($types->exists('articles'));
		$this->assertFalse($types->exists('foo'));
	}

	/**
	 * Test the get_forum_type method
	 */
	public function test_get_forum_type()
	{
		$types = $this->get_service();

		$this->assertEquals('news', $types->get_forum_type(7));
		$this->assertEquals('articles', $types->get_forum_type(8));

		// forum id exists but not a content type forum
		$this->assertFalse($types->get_forum_type(1));

		// forum id does not exist
		$this->assertFalse($types->get_forum_type(20));
	}

	/**
	 * Test the get_forum_type method
	 */
	public function test_get_forum_types()
	{
		$types = $this->get_service();

		$expected = array(
			7 => 'news',
			8 => 'articles',
		);

		$this->assertSame($expected, $types->get_forum_types());
	}
}