<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\model\mapper;

class types_test extends base_mapper
{
	/**
	 * Test the load method
	 */
	public function test_load()
	{
		$fields_mapper = $this->get_mapper('fields');
		$types_mapper = $this->get_mapper('types');

		$condition = array(
			array('content_id', '=', 1),
		);

		$content_type = $types_mapper->load($condition);
		$fields_collection = $fields_mapper->find($condition);

		$this->assertInstanceOf('\blitze\content\model\entity\type', $content_type);
		$this->assertEquals('news', $content_type->get_content_name());
		$this->assertEquals($fields_collection->count(), count($content_type->get_content_fields()));
	}

	/**
	 * Test the find method
	 */
	public function test_find()
	{
		$mapper = $this->get_mapper('types');

		// it should return all routes if no condition is specified
		$collection = $mapper->find();
		$this->assertEquals(2, $collection->count());

		// it should return 1 entities in the collection
		$collection = $mapper->find(array('content_name', '=', 'articles'));

		$this->assertInstanceOf('\blitze\content\model\collections\types', $collection);
		$this->assertEquals(1, $collection->count());

		$collection = $mapper->find(array('content_enabled', '<>', 1));
		$this->assertEquals(0, $collection->count());
	}

	/**
	 * Test delete an entity
	 */
	public function test_delete_entity()
	{
		$fields_mapper = $this->get_mapper('fields');
		$types_mapper = $this->get_mapper('types');

		$condition = array(
			array('content_id', '=', 1),
		);

		$content_type = $types_mapper->load($condition);

		$fields_collection = $fields_mapper->find($condition);
		$this->assertGreaterThan(0, $fields_collection->count());

		$types_mapper->delete($content_type);

		// it should no longer exist
		$this->assertNull($types_mapper->load($condition));

		// it's fields should no longer exist
		$this->assertEquals(0, $fields_mapper->find($condition)->count());
	}
}
