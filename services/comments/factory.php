<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2019 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\comments;

class factory
{
	/** @var array */
	private $comment_types;

	/**
	 * Constructor
	 *
	 * @param \phpbb\di\service_collection		$types		Service Collection
	 */
	public function __construct(\phpbb\di\service_collection $types)
	{
		$this->register_comment_types($types);
	}

	/**
	 * Register available content views
	 * @param \phpbb\di\service_collection $views
	 */
	protected function register_comment_types(\phpbb\di\service_collection $types)
	{
		$this->comment_types = array();
		foreach ($types as $service => $driver)
		{
			$this->comment_types[$service] = $driver;
		}
	}

	/**
	 * Get comment type
	 *
	 * @param string $service_name
	 * @return null|\blitze\content\services\comments\comments_interface
	 */
	public function get($service_name)
	{
		return isset($this->comment_types[$service_name]) ? $this->comment_types[$service_name] : null;
	}

	/**
	 * Get available comment types
	 * @return array
	 */
	public function get_all_types()
	{
		$types = array();
		foreach ($this->comment_types as $service => $driver)
		{
			$types[$service] = $driver->get_langname();
		}

		return $types;
	}
}
