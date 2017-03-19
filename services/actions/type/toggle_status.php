<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\actions\type;

use blitze\content\services\actions\action_interface;
use blitze\content\services\actions\action_utils;

class toggle_status extends action_utils implements action_interface
{
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \blitze\content\services\types */
	protected $content_types;

	/** @var \blitze\content\model\mapper_factory */
	protected $mapper_factory;

	/**
	 * Constructor
	 *
	 * @param \phpbb\cache\driver\driver_interface		$cache					Cache object
	 * @param \blitze\content\services\types			$content_types			Content types object
	 * @param \blitze\content\model\mapper_factory		$mapper_factory			Mapper factory object
	 * @param boolean									$redirect				Used for testing
	*/
	public function __construct(\phpbb\cache\driver\driver_interface $cache, \blitze\content\services\types $content_types, \blitze\content\model\mapper_factory $mapper_factory, $redirect = true)
	{
		$this->cache = $cache;
		$this->content_types = $content_types;
		$this->mapper_factory = $mapper_factory;
		$this->redirect = $redirect;
	}

	/**
	 * @inheritdoc
	 */
	public function execute($u_action, $type = '')
	{
		$entity = $this->content_types->get_type($type);
		$entity->set_content_enabled(!$entity->get_content_enabled());

		$this->mapper_factory->create('types')
			->save($entity);
		$this->cache->destroy('_content_types');

		$this->redirect($u_action);
	}
}
