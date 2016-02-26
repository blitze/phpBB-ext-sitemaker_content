<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

abstract class duration extends base
{
	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \blitze\sitemaker\services\util */
	protected $sitemaker;

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request_interface		$request		Request object
	 * @param \phpbb\user							$user			User object
	 * @param \blitze\sitemaker\services\template		$ptemplate		Sitemaker template object
	 * @param \blitze\sitemaker\services\util			$sitemaker		Sitemaker object
	 */
	public function __construct(\phpbb\request\request_interface $request, \phpbb\user $user, \blitze\sitemaker\services\template $ptemplate, \blitze\sitemaker\services\util $sitemaker)
	{
		parent::__construct($user, $ptemplate);

		$this->request = $request;
		$this->sitemaker = $sitemaker;
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
	public function show_form_field($name, &$data, $item_id = 0)
	{
		$this->sitemaker->add_assets(array(
			'js'   => array(
				'@blitze_content/vendor/datetimepicker/build/jquery.datetimepicker.full.min.js',
				'@blitze_content/assets/datetimepicker.min.js',
			),
			'css'   => array(
				'@blitze_content/vendor/datetimepicker/jquery.datetimepicker.min.css',
			)
		));

		return parent::show_form_field($name, $data, $item_id);
	}
}
