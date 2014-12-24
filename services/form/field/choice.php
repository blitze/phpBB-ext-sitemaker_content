<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\services\form\field;

abstract class choice extends base
{
	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'field_size'		=> 10,
			'field_minlen'		=> 0,
			'field_maxlen'		=> 200,
			'field_options'		=> array(),
			'field_multi'		=> false,
			'requires_item_id'	=> false,
		);
	}

	/**
	 * @inheritdoc
	 */
	public function display_field($field_value, $fields_data = array(), $view = 'detail', $item_id = 0)
	{
		$field_value = array_filter(explode("<br />", $field_value));
		return sizeof($field_value) ? join(', ', $field_value) . '<br /><br />' : '';
	}

	/**
	 * @inheritdoc
	 */
	public function render_view($name, &$data, $item_id = 0)
	{
		$field = $this->get_name();
		$selected = $this->get_field_value($name, $data['field_value']);
		$selected = (is_array($selected)) ? $selected : array($selected);

		$data['field_name'] = $name;
		$data['field_value'] = join("\n", $selected);
		$data['field_required']	= ($data['field_required']) ? ' required' : '';
		$data['field_size'] = (sizeof($data['field_options']) < $data['field_size']) ? sizeof($data['field_options']) : $data['field_size'];

		if ($data['field_type'] == 'radio' || $data['field_type'] == 'checkbox')
		{
			$data['field_id'] .= '-0';
		}

		$count = 0;
		foreach ($data['field_options'] as $value => $label)
		{
			$this->ptemplate->assign_block_vars('option', array(
				'ID'		=> 'field-'. $name . '-' . $count,
				'LABEL'		=> $label,
				'SELECTED'	=> (in_array($value, $selected)) ? true : false,
				'VALUE'		=> $value)
			);
			$count++;
		}

		$this->ptemplate->assign_vars(array_change_key_case($data, CASE_UPPER));

		return $this->ptemplate->render_view('primetime/content', "fields/$field.html", $field . '_field');
	}
}
