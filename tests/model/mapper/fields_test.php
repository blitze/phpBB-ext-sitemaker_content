<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\model\mapper;

class fields_test extends base_mapper
{
	/**
	 * Test the load method
	 */
	public function test_load()
	{
		$mapper = $this->get_mapper('fields');

		$field = $mapper->load(array('field_id', '=', 50));

		$this->assertInstanceOf('\blitze\content\model\entity\field', $field);
		$this->assertEquals('article', $field->get_field_name());
	}

	/**
	 * Test the find method
	 */
	public function test_find()
	{
		$mapper = $this->get_mapper('fields');

		// it should return all blocks if no condition is specified
		$collection = $mapper->find();
		$this->assertEquals(5, $collection->count());

		// it should return 4 entities in the collection
		$collection = $mapper->find(array(
			array('content_id', '=', 1),
		));

		$this->assertInstanceOf('\blitze\content\model\collections\fields', $collection);
		$this->assertEquals(4, $collection->count());

		$collection = $mapper->find(array('field_type', '=', 'email'));
		$this->assertEquals(0, $collection->count());
	}

	/**
	 * Test get max field id
	 */
	public function test_get_max_field_id()
	{
		$mapper = $this->get_mapper('fields');
		$this->assertEquals(58, $mapper->get_max_field_id());
	}

	/**
	 * Test add (multiple) content fields
	 */
	public function test_multi_insert()
	{
		$mapper = $this->get_mapper('fields');

		$collection = $mapper->find(array(
			array('content_id', '=', 3),
		));
		$this->assertEquals(0, $collection->count());

		$entity1 = $mapper->create_entity(array(
			'content_id'	=> 3,
			'field_name'	=> 'field1',
			'field_label'	=> 'Field 1',
			'field_type'	=> 'email',
		));
		$entity2 = $mapper->create_entity(array(
			'content_id'	=> 3,
			'field_name'	=> 'field2',
			'field_label'	=> 'Field 2',
			'field_type'	=> 'textarea',
		));
		$mapper->multi_insert(array(
			'field1'	=> $entity1->to_db(),
			'field2'	=> $entity2->to_db(),
		));

		$collection = $mapper->find(array(
			array('content_id', '=', 3),
		));
		$this->assertEquals(2, $collection->count());
	}
}
