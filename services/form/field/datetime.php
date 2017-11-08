<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class datetime extends base
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \blitze\sitemaker\services\util */
	protected $util;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language                  $language       Language object
	 * @param \phpbb\request\request_interface			$request		Request object
	 * @param \blitze\sitemaker\services\template		$ptemplate		Sitemaker template object
	 * @param \phpbb\user								$user			User object
	 * @param \blitze\sitemaker\services\util			$util       	Sitemaker utility object
	 */
	public function __construct(\phpbb\language\language $language, \phpbb\request\request_interface $request, \blitze\sitemaker\services\template $ptemplate, \phpbb\user $user, \blitze\sitemaker\services\util $util)
	{
		parent::__construct($language, $request, $ptemplate);

		$this->user = $user;
		$this->util = $util;
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'datetime';
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'type'		=> 'datetime',
			'range'		=> false,
			'num_dates'	=> 1,
			'min_date'	=> '',
			'max_date'	=> '',
			'oformat'	=> '',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function show_form_field($name, array &$data)
	{
		$this->util->add_assets(array(
			'js'	=> array(
				99	=> '@blitze_content/vendor/air-datepicker/dist/js/datepicker.min.js',
				100	=> '@blitze_content/assets/form/fields.min.js',
			),
			'css'	=> array(
				'@blitze_content/vendor/air-datepicker/dist/css/datepicker.min.css',
			)
		));

		return parent::show_form_field($name, $data);
	}
}
