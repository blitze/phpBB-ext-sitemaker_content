<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\model\mapper;

use blitze\sitemaker\model\base_mapper;

class types extends base_mapper
{
	/** @var string */
	protected $entity_class = 'blitze\content\model\entity\type';

	/** @var string */
	protected $entity_pkey = 'content_id';

	/**
	 * {@inheritdoc}
	 */
	public function load(array $condition = array())
	{
		$entity = parent::load($condition);

		if ($entity)
		{
			$fields = $this->find_fields($entity->get_content_id());
			$entity->set_content_fields(array_pop($fields));
		}

		return $entity;
	}

	/**
	 * {@inheritdoc}
	 */
	public function find(array $condition = array())
	{
		parent::find($condition);

		if ($this->collection->count())
		{
			$content_fields = $this->find_fields(array_keys($this->collection->get_entities()));

			foreach ($content_fields as $content_id => $fields)
			{
				$this->collection[$content_id]->set_content_fields($fields);
			}
		}

		return $this->collection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete($entity)
	{
		/** @var \blitze\content\model\entity\type $entity */
		parent::delete($entity);

		if ($entity instanceof $this->entity_class)
		{
			$fields_mapper = $this->mapper_factory->create('fields');
			$fields_mapper->delete(array('content_id', '=', $entity->get_content_id()));
		}
	}

	/**
	 * @param int|array $content_ids
	 * @return array
	 */
	protected function find_fields($content_ids)
	{
		$fields_mapper = $this->mapper_factory->create('fields');
		$collection = $fields_mapper->find(array('content_id', '=', $content_ids));

		$content_fields = array();
		foreach ($collection as $entity)
		{
			$content_id = $entity->get_content_id();
			$content_fields[$content_id][$entity->get_field_name()] = $entity->to_array();
		}

		return $content_fields;
	}
}
