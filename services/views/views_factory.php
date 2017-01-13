<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\views;

class views_factory
{
	/** @var \phpbb\language\language */
	protected $language;

	/** @var array */
	private $views;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language				$language			Language object
	 * @param \phpbb\di\service_collection			$views				Service Collection
	 */
	public function __construct(\phpbb\language\language $language, \phpbb\di\service_collection $views)
	{
		$this->language = $language;
		$this->register_views($views);
	}

	/**
	 * Register available content views
	 * @param \phpbb\di\service_collection $views
	 */
	protected function register_views(\phpbb\di\service_collection $views)
	{
		$this->views = array();
		foreach ($views as $service => $driver)
		{
			$this->views[$service] = $driver;
		}
	}

	/**
	 * Get view handler
	 *
	 * @param string $service_name
	 * @param string $fallback
	 * @return \blitze\content\services\views\views_interface
	 */
	public function get($service_name, $fallback = 'blitze.content.view.portal')
	{
		if (!isset($this->views[$service_name]))
		{
			$service_name = $fallback;
		}

		return $this->views[$service_name];
	}

	/**
	 * Get available sitemaker content views
	 * @return array
	 */
	public function get_all_views()
	{
		$views = array();
		foreach ($this->views as $service => $driver)
		{
			$views[$service] = $this->language->lang($driver->get_langname());
		}

		asort($views);

		return $views;
	}
}
