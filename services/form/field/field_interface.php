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
	 * @param array $field_data		This holds field props and field value after bbcode parsing has occurred
	 *								Which means, line breaks have been replaced with <br>
	 * @return string
	 */
	public function display_field(array $field_data);

	/**
	 * Render content field as form element
	 *
	 * @param string $field_name
	 * @param array $field_data  Ex. array(
	 * 								'field_type'	=> 'foo',		// string
	 *								'field_value'	=> '',			// string/array
	 *								'field_props'	=> array(
	 *									'options'		=> array(),
	 *									'defaults'		=> array(),
	 *								),
	 *							)
	 * @return string
	 */
	public function show_form_field($field_name, array &$field_data);

	/**
	 * Save content field
	 *
	 * @param string $field_name
	 * @param mixed $field_value
	 * @return void
	 */
	public function save_field($field_name, $field_value);

	/**
	 * Validate content field
	 *
	 * @param array $field_data
	 * @return bool
	 */
	public function validate_field(array $field_data);
}
