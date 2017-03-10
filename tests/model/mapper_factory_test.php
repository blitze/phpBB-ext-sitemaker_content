<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\model;

use blitze\content\model\mapper_factory;

class mapper_factory_test extends \phpbb_test_case
{
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
	 * Data set for test_create_mapper
	 *
	 * @return array
	 */
	public function create_mapper_test_data()
	{
		return array(
			array('fields', '\blitze\content\model\mapper\fields'),
			array('types', '\blitze\content\model\mapper\types'),
		);
	}

	/**
	 * Test mapper factory
	 *
	 * @dataProvider create_mapper_test_data
	 * @param string $type
	 * @param string $expected_class
	 */
	public function test_create_mapper($type, $expected_class)
	{
		$table_prefix = 'phpbb_';
		$tables = array(
			'mapper_tables'		=> array(
				'fields'	=> $table_prefix . 'sm_content_fields',
				'types'		=> $table_prefix . 'sm_content_types',
			)
		);

		$db = $this->getMock('\phpbb\db\driver\driver_interface');
		$config = new \phpbb\config\config(array());

		$mapper_factory = new mapper_factory($db, $tables);

		$this->assertInstanceOf($expected_class, $mapper_factory->create($type));
	}
}
