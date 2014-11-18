<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\services\form\field;

abstract class duration extends base
{
	/**
	 * @inheritdoc
	 */
	public function get_field_value($name, $value)
	{
		return $this->request->variable($name, $value);
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'field_min_date'	=> 'false',
			'field_max_date'	=> 'false',
			'field_min_time'	=> 'false',
			'field_max_time'	=> 'false',
			'requires_item_id'	=> false,
		);
	}

	/**
	 * @inheritdoc
	 */
	public function render_view($name, &$data, $item_id = 0)
	{
		$asset_path = $this->primetime->asset_path;
		$this->primetime->add_assets(array(
			'js'   => array(
				$asset_path . 'ext/primetime/content/assets/vendor/datetimepicker/jquery.datetimepicker.min.js',
				$asset_path . 'ext/primetime/content/assets/js/datetimepicker.min.js',
			),
			'css'   => array(
				$asset_path . 'ext/primetime/content/assets/vendor/datetimepicker/jquery.datetimepicker.min.css',
			)
		));

		$data += $this->get_default_props();

		$field = $this->get_name();
		$data['field_name'] = $name;
		$data['field_value'] = $this->get_field_value($name, $data['field_value']);
		$data['field_required']	= ($data['field_required']) ? ' required' : '';

		$this->ptemplate->assign_vars(array_change_key_case($data, CASE_UPPER));

		return $this->ptemplate->render_view('primetime/content', "fields/$field.html", $field . '_field');
	}
}
