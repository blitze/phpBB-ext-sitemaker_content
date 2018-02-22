<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services;

class types
{
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \blitze\content\model\mapper_factory */
	protected $mapper_factory;

	/* @var array */
	protected $content_forums;

	/**
	 * Construct
	 *
	 * @param \phpbb\cache\driver\driver_interface		$cache				Cache object
	 * @param \blitze\content\model\mapper_factory		$mapper_factory		Mapper factory object
	 */
	public function __construct(\phpbb\cache\driver\driver_interface $cache, \blitze\content\model\mapper_factory $mapper_factory)
	{
		$this->cache = $cache;
		$this->mapper_factory = $mapper_factory;
	}

	/**
	 * Get all content types
	 *
	 * @param string $mode data|forums
	 * @return array
	 */
	public function get_all_types($mode = 'data')
	{
		if (($types_data = $this->cache->get('_content_types')) === false)
		{
			$types_mapper = $this->mapper_factory->create('types');
			$collection = $types_mapper->find();

			$types_data = array(
				'forums'	=> array(),
				'data'		=> array(),
			);

			foreach ($collection as $entity)
			{
                /** @var \blitze\content\model\entity\type $entity */
                $forum_id = $entity->get_forum_id();
				$content_name = $entity->get_content_name();

				$types_data['forums'][$forum_id] = $content_name;
				$types_data['data'][$content_name] = $entity;
			}

			// we do not cache while in acp to avoid bad relative paths
			if (!defined('ADMIN_START'))
			{
				$this->cache->put('_content_types', $types_data);
			}
		}
		return $types_data[$mode];
	}

	/**
	 * Get content type
	 *
	 * @param string $type
	 * @param bool $trigger_error
	 * @return false|\blitze\content\model\entity\type
	 * @throws \blitze\sitemaker\exception\out_of_bounds
	 */
	public function get_type($type, $trigger_error = true)
	{
		$content_data = $this->get_all_types();

		if (!isset($content_data[$type]))
		{
			if ($trigger_error)
			{
				throw new \blitze\sitemaker\exception\out_of_bounds($type);
			}
			else
			{
				return false;
			}
		}

		return $content_data[$type];
	}

	/**
	 * Content type exists
	 *
	 * @param string $type
	 * @return bool
	 */
	public function exists($type)
	{
		$content_data = $this->get_all_types();
		return (isset($content_data[$type]));
	}

	/**
	 * Get content type from forum_id
	 * @param int $forum_id
	 * @return string|bool|false
	 */
	public function get_forum_type($forum_id)
	{
		$content_data = $this->get_all_types('forums');
		return isset($content_data[$forum_id]) ? $content_data[$forum_id] : false;
	}

	/**
	 * Get all content types by forum_id
	 * @return array
	 */
	public function get_forum_types()
	{
		return $this->get_all_types('forums');
	}
}
