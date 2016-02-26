<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

use Urodoz\Truncate\TruncateService;

class textarea extends base
{
	/** @var \phpbb\request\request_interface */
	protected $request;

	/* @var \phpbb\user */
	protected $user;

	/** @var \blitze\sitemaker\services\template */
	protected $ptemplate;

	/** @var \blitze\sitemaker\services\util */
	protected $sitemaker;

	/** @var \Urodoz\Truncate\TruncateService */
	protected $truncate;

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request_interface		$request			Request object
	 * @param \phpbb\user							$user				User object
	 * @param \blitze\sitemaker\services\template		$ptemplate			Sitemaker template object
	 * @param \blitze\sitemaker\services\util			$sitemaker			Sitemaker object
	 */
	public function __construct(\phpbb\request\request_interface $request, \phpbb\user $user, \blitze\sitemaker\services\template $ptemplate, \blitze\sitemaker\services\util $sitemaker)
	{
		$this->request = $request;
		$this->user = $user;
		$this->ptemplate = $ptemplate;
		$this->sitemaker = $sitemaker;
		$this->truncate = new TruncateService();
	}

	/**
	 * @inheritdoc
	 */
	public function display_field($value, $data = array(), $view = 'detail', $item_id = 0)
	{
		if ($view == 'summary' && $data['max_chars'])
		{
			$value = $this->truncate->truncate($value, $data['max_chars']);
		}

		return ($value) ? $value . '<br /><br />' : '';
	}

	/**
	 * @inheritdoc
	 */
	public function show_form_field($name, &$data, $item_id = 0)
	{
		if ($data['editor'])
		{
			$asset_path = $this->sitemaker->asset_path;
			$this->sitemaker->add_assets(array(
				'js'   => array(
					$asset_path . 'assets/javascript/editor.js',
					'@blitze_content/assets/content_posting.min.js'
				)
			));
		}

		if ($data['size'] == 'large')
		{
			$data['full_width'] = true;
			$data['field_rows'] = 25;
		}

		return parent::show_form_field($name, $data, $item_id);
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'field_minlen'		=> 0,
			'field_maxlen'		=> 20,
			'field_rows'		=> 5,
			'field_columns'		=> 25,
			'full_width'		=> false,
			'requires_item_id'	=> false,
			'max_chars'			=> 0,
			'editor'			=> false,
			'size'				=> 'small',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'textarea';
	}
}
