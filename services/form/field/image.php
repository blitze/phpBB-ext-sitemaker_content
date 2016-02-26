<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class image extends base
{
	/** @var \phpbb\request\request_interface */
	protected $request;

	/* @var \phpbb\user */
	protected $user;

	/** @var \blitze\sitemaker\services\template */
	protected $ptemplate;

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request_interface		$request		Request object
	 * @param \phpbb\user							$user			User object
	 * @param \blitze\sitemaker\services\template	$ptemplate		Sitemaker template object
	 */
	public function __construct(\phpbb\request\request_interface $request, \phpbb\user $user, \blitze\sitemaker\services\template $ptemplate)
	{
		$this->request = $request;
		$this->user = $user;
		$this->ptemplate = $ptemplate;
	}

	/**
	 * @inheritdoc
	 */
	public function get_field_value($name, $value)
	{
		$value = $this->request->variable($name, $this->get_image_src($value));

		return ($value) ? '[img]' . $value . '[/img]' : '';
	}

	/**
	 * @inheritdoc
	 */
	public function show_form_field($name, &$data, $item_id = 0)
	{
		$bbcode_value = $this->get_field_value($name, $data['field_value']);

		$field = $this->get_name();
		$data['field_name'] = $name;
		$data['field_value'] = ($bbcode_value) ? $this->get_image_src($bbcode_value) : '';
		$data['field_required']	= ($data['field_required']) ? ' required' : '';

		$this->ptemplate->assign_vars(array_change_key_case($data, CASE_UPPER));
		$field = $this->ptemplate->render_view('blitze/content', "fields/$field.html", $field . '_field');

		$data['field_value'] = $bbcode_value;

		return $field;
	}

	/**
	 * @inheritdoc
	 */
	public function display_field($value, $data = array(), $view = 'detail', $item_id = 0)
	{
		return ($value) ? '<div class="img-ui">' . $value . '</div>' : '';
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'field_size'		=> 40,
			'field_minlen'		=> 0,
			'field_maxlen'		=> 200,
			//'validation_filter'	=> FILTER_VALIDATE_URL,
			'requires_item_id'	=> false,
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'image';
	}

	private function get_image_src($bbcode_string)
	{
		$match = array();
		preg_match('#\[img](.*?)\[\/img]#', $bbcode_string, $match);

		return isset($match[1]) ? $match[1] : '';
	}
}
