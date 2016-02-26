<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

interface field_interface
{
	/**
	 * Short name of content field
	 */
	public function get_name();

	/**
	 * Lang name of content field
	 */
	public function get_langname();

	/**
	 * Default content field properties
	 */
	public function get_default_props();

	/**
	 * Returns the value of the field.
	 *
	 * @param string $field_name
	 * @param mixed $field_value	this is the raw value before bbcode parsing has occurred
	 * @return mixed
	 */
	public function get_field_value($field_name, $field_value);

	/**
	 * Display content field
	 *
	 * @param mixed $field_value	this is the value after bbcode parsing has occurred
	 * @param array $field_data
	 * @param string|detail $view
	 * @param int|0 $item_id
	 * @return string
	 */
	public function display_field($field_value, $field_data, $view = 'detail', $item_id = 0);

	/**
	 * Render content field as form element
	 */
	public function show_form_field($field_name, &$field_data, $item_id = 0);

	/**
	 * Validate content field
	 */
	public function validate_field($field_data);

	/**
	 * Save content field
	 */
	public function save_field($field_name, $field_value, $item_id);
}
