<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2018 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\topic;

class blocks_factory
{
	/** @var array */
	private $blocks;

	/**
	 * Constructor
	 *
	 * @param \phpbb\di\service_collection			$blocks			Service Collection
	 */
	public function __construct(\phpbb\di\service_collection $blocks)
	{
		$this->register_blocks($blocks);
	}

	/**
	 * Register available topic blocks
	 * @param \phpbb\di\service_collection $blocks
	 */
	protected function register_blocks(\phpbb\di\service_collection $blocks)
	{
		$this->blocks = array();
		foreach ($blocks as $driver)
		{
			$this->blocks[$driver->get_name()] = $driver;
		}
	}

	/**
	 * Get topic block object
	 *
	 * @param string $service_name
	 * @return null|\blitze\content\services\topic\block_interface
	 */
	public function get($service_name)
	{
		return $this->blocks[$service_name] ?: null;
	}

	/**
	 * Get available content topic blocks
	 * @return array
	 */
	public function get_all()
	{
		$blocks = array();
		foreach ($this->blocks as $service => $driver)
		{
			$blocks[$service] = $driver->get_langname();
		}

		asort($blocks);

		return $blocks;
	}
}
