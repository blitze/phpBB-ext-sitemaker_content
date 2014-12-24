<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\services\form\field;

use Urodoz\Truncate\TruncateService;

class textarea extends base
{
	/** @var \phpbb\request\request_interface */
	protected $request;

	/* @var \phpbb\user */
	protected $user;

	/** @var \primetime\primetime\core\template */
	protected $ptemplate;

	/** @var \primetime\primetime\core\primetime */
	protected $primetime;

	/** @var Urodoz\Truncate\TruncateService */
	protected $truncate;

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request_interface		$request			Request object
	 * @param \phpbb\user							$user				User object
	 * @param \primetime\primetime\core\template	$ptemplate			Primetime template object
	 * @param \primetime\primetime\core\primetime	$primetime			Primetime object
	 */
	public function __construct(\phpbb\request\request_interface $request, \phpbb\user $user, \primetime\primetime\core\template $ptemplate, \primetime\primetime\core\primetime $primetime)
	{
		$this->request = $request;
		$this->user = $user;
		$this->ptemplate = $ptemplate;
		$this->primetime = $primetime;
		$this->truncate = new TruncateService();
	}

	/**
	 * @inheritdoc
	 */
	public function get_field_value($name, $value)
	{
		return $this->request->variable($name, (string) $value, true);
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
	public function render_view($name, &$data, $item_id = 0)
	{
		if ($data['editor'])
		{
			$asset_path = $this->primetime->asset_path;
			$this->primetime->add_assets(array(
				'js'   => array(
					$asset_path . 'assets/javascript/editor.js',
					$asset_path . 'ext/primetime/content/assets/scripts/content_posting.min.js'
				)
			));
		}

		if ($data['size'] == 'large')
		{
			$data['full_width'] = true;
			$data['field_rows'] = 25;
		}

		return parent::render_view($name, $data, $item_id);
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
