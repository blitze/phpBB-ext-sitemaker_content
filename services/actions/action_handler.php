<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\actions;

use Symfony\Component\DependencyInjection\Container;

class action_handler
{
	/** @var Container */
	protected $phpbb_container;

	/**
	 * Constructor
	 *
	 * @param Container		$phpbb_container		Service container
	 */
	public function __construct(Container $phpbb_container)
	{
		$this->phpbb_container = $phpbb_container;
	}

	/**
	 * @param string $mode topic|type
	 * @param string $action
	 * @return \blitze\content\services\actions\action_interface
	 * @throws \blitze\sitemaker\exception\out_of_bounds
	 */
	public function create($mode, $action)
	{
		$service_name = 'blitze.content.actions.' . $mode . '.' . $action;
		if (!$this->phpbb_container->has($service_name))
		{
			throw new \blitze\sitemaker\exception\out_of_bounds(array($action, 'INVALID_REQUEST'));
		}

		$service = $this->phpbb_container->get($service_name);

		/** @var \blitze\content\services\actions\action_interface $service */
		return $service;
	}
}
