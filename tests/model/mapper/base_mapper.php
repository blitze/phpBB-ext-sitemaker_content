<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\model\mapper;

abstract class base_mapper extends \phpbb_database_test_case
{
	protected $config;
	protected $translator;

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
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/content.xml');
	}

	public function setUp()
	{
		global $cache, $config, $phpbb_dispatcher, $request, $user;

		parent::setUp();

		$cache = new \phpbb_mock_cache();
		$config = $this->config = new \phpbb\config\config(array());
		//$phpbb_container = new \phpbb_mock_container_builder();
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		$request = $this->getMock('\phpbb\request\request_interface');

		$user = $this->getMockBuilder('\phpbb\user')
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * Create the mapper service
	 *
	 * @param string $type
	 * @return mixed
	 */
	protected function get_mapper($type)
	{
		global $db;

		$this->translator = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$this->translator->expects($this->any())
			->method('lang')
			->willReturnCallback(function () {
				return implode('-', func_get_args());
			});

		$table_prefix = 'phpbb_';
		$collection_class = '\\blitze\\content\\model\\collections\\' . $type;
		$mapper_class = '\\blitze\\content\\model\\mapper\\' . $type;
		$tables = array(
			'mapper_tables'	=> array(
				'types'		=> $table_prefix . 'sm_content_types',
				'fields'	=> $table_prefix . 'sm_content_fields',
			)
		);

		$db = $this->new_dbal();

		$mapper_factory = new \blitze\content\model\mapper_factory($db, $tables);
		$collection = new $collection_class;

		return new $mapper_class($db, $collection, $mapper_factory, $tables['mapper_tables'][$type], $this->config);
	}
}
