<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form;

class fields_factory
{
	/** @var \phpbb\language\language */
	protected $language;

	/** @var array */
	protected $fields = array();

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language					$language				Language object
	 * @param \phpbb\di\service_collection				$field_drivers			Form fields
	 */
	public function __construct(\phpbb\language\language $language, \phpbb\di\service_collection $field_drivers)
	{
		$this->language = $language;

		$this->register_fields($field_drivers);
	}

	/**
	 * Register available form fields
	 * @param \phpbb\di\service_collection $field_drivers
	 */
	protected function register_fields(\phpbb\di\service_collection $field_drivers)
	{
		if (!empty($field_drivers))
		{
			foreach ($field_drivers as $driver)
			{
				$this->fields[$driver->get_name()] = $driver;
			}
		}
	}

	/**
	 * @return array
	 */
	public function get_all()
	{
		return $this->fields;
	}

	/**
	 * Get field object
	 *
	 * @param string $service_name
	 * @return \blitze\content\services\form\field\field_interface
	 */
	public function get($service_name)
	{
		if (!$this->exists($service_name))
		{
			// throw exception
		}

		return $this->fields[$service_name];
	}

	/**
	 * Verify if field exists
	 *
	 * @param string $service_name
	 * @return bool
	 */
	public function exists($service_name)
	{
		return (isset($this->fields[$service_name]));
	}

	/**
	 * Get available content field options
	 * @return array
	 */
	public function get_options()
	{
		$fields = array();
		foreach ($this->fields as $service => $driver)
		{
			$fields[$service] = $this->language->lang($driver->get_langname());
		}

		asort($fields);

		return $fields;
	}
}
