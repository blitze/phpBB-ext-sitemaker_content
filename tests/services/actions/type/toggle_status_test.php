<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\services\actions\type;

use blitze\content\services\actions\type\toggle_status;

class toggle_statust_test extends \phpbb_database_test_case
{
	protected $mapper_factory;

	/**
	* Define the extensions to be tested
	*
	* @return array vendor/name of extension(s) to test
	*/
	static protected function setup_extensions()
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
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/contents.xml');
	}

	/**
	 * @return \blitze\content\services\actions\type\add
	 */
	protected function get_command()
	{
		$table_prefix = 'phpbb_';
		$tables = array(
			'mapper_tables'		=> array(
				'fields'	=> $table_prefix . 'sm_content_fields',
				'types'		=> $table_prefix . 'sm_content_types',
			)
		);

		$cache = new \phpbb_mock_cache();
		$db = $this->new_dbal();

		$this->mapper_factory = new \blitze\content\model\mapper_factory($db, $tables);

		$types = new \blitze\content\services\types($cache, $this->mapper_factory);

		return new toggle_status($cache, $types, $this->mapper_factory, false);
	}

	/**
	 * Test edit content type with valid type
	 */
	public function test_toggle_status()
	{
		$command = $this->get_command();

		$type = 'bar';
		$types_mapper = $this->mapper_factory->create('types');

		$entity = $types_mapper->load(array('content_name', '=', $type));
		$this->assertFalse($entity->get_content_enabled());

		$command->execute('admin_url', $type);

		$entity = $types_mapper->load(array('content_name', '=', $type));
		$this->assertTrue($entity->get_content_enabled());

		$command->execute('admin_url', $type);

		$entity = $types_mapper->load(array('content_name', '=', $type));
		$this->assertFalse($entity->get_content_enabled());
	}
}
