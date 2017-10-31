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
	 * @return string
	 */
	public function get_name();

	/**
	 * Lang name of content field
	 * @return string
	 */
	public function get_langname();

	/**
	 * Default content field properties
	 * @return array
	 */
	public function get_default_props();

	/**
	 * Returns the value of the field.
	 *
	 * @param array $data	This holds field props and field value before bbcode parsing has occurred
	 * @return mixed
	 */
	public function get_field_value(array $data);

	/**
	 * Display content field
	 *
	 * @param array $data		This holds field props and field value after bbcode parsing has occurred
	 *							Which means, line breaks have been replaced with <br>
	 * @return string
	 */
	public function display_field(array $data);

	/**
	 * Render content field as form element
	 *
	 * @param string $field_name
	 * @param array $data  Ex. array(
	 * 								'field_type'	=> 'foo',		// string
	 *								'field_value'	=> '',			// string/array
	 *								'field_props'	=> array(
	 *									'options'		=> array(),
	 *									'defaults'		=> array(),
	 *								),
	 *							)
	 * @return string
	 */
	public function show_form_field($field_name, array &$data);

	/**
	 * Save content field
	 *
	 * @param int $topic_id
	 * @param mixed $field_value
	 * @param array $field_data
	 * @return void
	 */
	public function save_field($topic_id, $field_value, array $data);

	/**
	 * Validate content field
	 *
	 * @param array $data
	 * @return string
	 */
	public function validate_field(array $data);
}
