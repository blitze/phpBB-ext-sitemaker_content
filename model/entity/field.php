<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\model\entity;

use blitze\sitemaker\model\base_entity;

/**
 * @method integer get_field_id()
 * @method object set_content_id($content_id)
 * @method integer get_content_id()
 * @method object set_field_name($field_name)
 * @method integer get_field_name()
 * @method integer get_field_label()
 * @method object set_field_type($field_type)
 * @method string get_field_type()
 * @method object set_field_mod_only($mod_only)
 * @method boolean get_field_mod_only()
 * @method object set_field_required($required_field)
 * @method boolean get_field_required()
 * @method object set_field_summary_show($summary_show)
 * @method boolean get_field_summary_show()
 * @method object set_field_detail_show($detail_show)
 * @method boolean get_field_detail_show()
 * @method object set_field_summary_ldisp($summary_ldisp)
 * @method boolean get_field_summary_ldisp()
 * @method object set_field_detail_ldisp($detail_ldisp)
 * @method boolean get_field_detail_ldisp()
 * @method object set_exp_uid($uid)
 * @method string get_exp_uid()
 * @method object set_exp_bitfield($bitfield)
 * @method string get_exp_bitfield()
 * @method object set_exp_options($options)
 * @method integer get_exp_options()
 * @method object set_field_order($order)
 * @method string get_field_order()
 */
final class field extends base_entity
{
	/** @var integer */
	protected $field_id;

	/** @var integer */
	protected $content_id;

	/** @var string */
	protected $field_name;

	/** @var string */
	protected $field_label;

	/** @var string */
	protected $field_explain = '';

	/** @var string */
	protected $field_type;

	/** @var string */
	protected $field_settings = '';

	/** @var boolean */
	protected $field_mod_only = false;

	/** @var boolean */
	protected $field_required = false;

	/** @var boolean */
	protected $field_summary_show = false;

	/** @var boolean */
	protected $field_detail_show = false;

	/** @var boolean */
	protected $field_summary_ldisp = false;

	/** @var boolean */
	protected $field_detail_ldisp = false;

	/** @var string */
	protected $field_exp_uid = '';

	/** @var string */
	protected $field_exp_bitfield = '';

	/** @var integer */
	protected $field_exp_options = 7;

	/** @var integer */
	protected $field_order = 0;

	/** @var array */
	protected $required_fields = array('content_id', 'field_name', 'field_label', 'field_type');

	/** @var array */
	protected $db_fields = array(
		'content_id',
		'field_name',
		'field_label',
		'field_explain',
		'field_type',
		'field_settings',
		'field_mod_only',
		'field_required',
		'field_summary_show',
		'field_detail_show',
		'field_summary_ldisp',
		'field_detail_ldisp',
		'field_exp_uid',
		'field_exp_bitfield',
		'field_exp_options',
		'field_order',
	);

	/**
	 * Set field ID
	 * @param int $field_id
	 * @return $this
	 */
	public function set_field_id($field_id)
	{
		if (!$this->field_id)
		{
			$this->field_id = (int) $field_id;
		}
		return $this;
	}

	/**
	 * Set field label
	 * @param string $label
	 * @return $this
	 */
	public function set_field_label($label)
	{
		$this->field_label = utf8_ucfirst(trim($label));
		return $this;
	}

	/**
	 * @param string $explain
	 * @param string $mode
	 * @return $this
	 */
	public function set_field_explain($explain, $mode = '')
	{
		$this->field_explain = $explain;

		if ($this->field_explain && $mode === 'storage')
		{
			generate_text_for_storage($this->field_explain, $this->field_exp_uid, $this->field_exp_bitfield, $this->field_exp_options, true, true, true);
		}
		return $this;
	}

	/**
	 * @param string $mode
	 * @return string|array
	 */
	public function get_field_explain($mode = 'display')
	{
		if ($mode === 'edit')
		{
			return generate_text_for_edit($this->field_explain, $this->field_exp_uid, $this->field_exp_options);
		}
		else
		{
			$parse_flags = ($this->field_exp_bitfield ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
			return generate_text_for_display($this->field_explain, $this->field_exp_uid, $this->field_exp_bitfield, $parse_flags);
		}
	}

	/**
	 * Set field settings
	 * @param array|string $settings
	 * @return $this
	 */
	public function set_field_settings($settings)
	{
		if (!is_array($settings))
		{
			$this->field_settings = $settings;
		}
		else if (sizeof($settings))
		{
			$this->field_settings = json_encode($settings);
		}
		return $this;
	}

	/**
	 * Get field settings
	 * @return array
	 */
	public function get_field_settings()
	{
		return ($this->field_settings) ? json_decode($this->field_settings, true) : array();
	}
}
