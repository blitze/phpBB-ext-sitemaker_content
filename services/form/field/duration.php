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
	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \primetime\core\services\util */
	protected $primetime;

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request_interface		$request		Request object
	 * @param \phpbb\user							$user			User object
	 * @param \primetime\core\services\template		$ptemplate		Primetime template object
	 * @param \primetime\core\services\util			$primetime		Primetime object
	 */
	public function __construct(\phpbb\request\request_interface $request, \phpbb\user $user, \primetime\core\services\template $ptemplate, \primetime\core\services\util $primetime)
	{
		parent::__construct($user, $ptemplate);

		$this->request = $request;
		$this->primetime = $primetime;
	}

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
				$asset_path . 'ext/primetime/content/components/datetimepicker/jquery.datetimepicker.min.js',
				'@primetime_content/assets/datetimepicker.min.js',
			),
			'css'   => array(
				$asset_path . 'ext/primetime/content/components/datetimepicker/jquery.datetimepicker.min.css',
			)
		));

		return parent::render_view($name, $data, $item_id);
	}
}
