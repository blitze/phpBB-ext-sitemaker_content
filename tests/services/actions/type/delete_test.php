<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\services\actions\type;

use blitze\content\services\actions\type\delete;

class delete_test extends \phpbb_database_test_case
{
	protected $content_mapper_factory;
	protected $sitemaker_mapper_factory;

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
	 * @return \blitze\content\services\actions\type\delete
	 */
	protected function get_command()
	{
		global $request;

		$table_prefix = 'phpbb_';
		$content_type_tables = array(
			'mapper_tables'		=> array(
				'fields'	=> $table_prefix . 'sm_content_fields',
				'types'		=> $table_prefix . 'sm_content_types',
			)
		);
		$sitemaker_block_tables = array(
			'mapper_tables'		=> array(
				'blocks'	=> $table_prefix . 'sm_blocks',
				'routes'	=> $table_prefix . 'sm_block_routes'
			)
		);

		$cache = new \phpbb_mock_cache();

		$config = new \phpbb\config\config(array());

		$db = $this->new_dbal();

		$language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$language->expects($this->any())
			->method('lang')
			->willReturnCallback(function() {
				return implode(' ', func_get_args());
			});

		$request = $this->getMock('\phpbb\request\request_interface');
		$request->expects($this->any())
			->method('variable')
			->willReturnCallback(function($variable, $default) {
				return $default;
			});

		$this->content_mapper_factory = new \blitze\content\model\mapper_factory($db, $content_type_tables);

		$this->sitemaker_mapper_factory = new \blitze\sitemaker\model\mapper_factory($config, $db, $sitemaker_block_tables);

		$types = new \blitze\content\services\types($cache, $this->content_mapper_factory);

		$forum_manager = $this->getMockBuilder('\blitze\sitemaker\services\forum\manager')
			->disableOriginalConstructor()
			->getMock();
		$forum_manager->expects($this->once())
			->method('remove')
			->with(
				$this->greaterThan(0),
				$this->isType('string'),
				true,
				$this->isType('integer')
			);

		return new delete($cache, $language, $request, $types, $forum_manager, $this->content_mapper_factory, $this->sitemaker_mapper_factory, false, false);
	}

	/**
	 * Test delete content type
	 */
	public function test_delete_type()
	{
		$command = $this->get_command();

		$types_mapper = $this->content_mapper_factory->create('types');
		$block_mapper = $this->sitemaker_mapper_factory->create('blocks');

		$collection = $types_mapper->find(array('content_name', '=', 'foo'));
		$this->assertEquals(1, $collection->count());

		$collection = $block_mapper->find(array('bid', '=', 2));
		$this->assertEquals(1, $collection->count());

		$command->execute('admin_url', 'foo');

		$collection = $types_mapper->find(array('content_name', '=', 'foo'));
		$this->assertEquals(0, $collection->count());

		$collection = $block_mapper->find(array('bid', '=', 2));
		$this->assertEquals(0, $collection->count());
	}
}
