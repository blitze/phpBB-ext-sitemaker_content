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
 * @method string get_field_summary_show()
 * @method object set_field_detail_show($detail_show)
 * @method string get_field_detail_show()
 * @method object set_field_summary_ldisp($summary_ldisp)
 * @method integer get_field_summary_ldisp()
 * @method object set_field_detail_ldisp($detail_ldisp)
 * @method integer get_field_detail_ldisp()
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
	protected $field_props = '';

	/** @var boolean */
	protected $field_mod_only = false;

	/** @var boolean */
	protected $field_required = false;

	/** @var string */
	protected $field_summary_show = '';

	/** @var string */
	protected $field_detail_show = '';

	/** @var integer */
	protected $field_summary_ldisp = 1;

	/** @var integer */
	protected $field_detail_ldisp = 1;

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
		'field_id',
		'field_name',
		'field_label',
		'field_explain',
		'field_type',
		'field_props',
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
			$data = generate_text_for_edit($this->field_explain, $this->field_exp_uid, $this->field_exp_options);
			return $data['text'];
		}
		else
		{
			$parse_flags = ($this->field_exp_bitfield ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
			return generate_text_for_display($this->field_explain, $this->field_exp_uid, $this->field_exp_bitfield, $parse_flags);
		}
	}

	/**
	 * Set field properties
	 * @param array|string $props
	 * @return $this
	 */
	public function set_field_props($props)
	{
		if (!is_array($props))
		{
			$this->field_props = $props;
		}
		else if (sizeof($props))
		{
			$this->field_props = json_encode($props);
		}
		return $this;
	}

	/**
	 * Get field settings
	 * @return array
	 */
	public function get_field_props()
	{
		$field_props = ($this->field_props) ? json_decode($this->field_props, true) : array();

		if (in_array($this->field_type, array('radio', 'checkbox', 'select')))
		{
			$field_props['options'] = array_filter(array_combine($field_props['options'], $field_props['options']), 'strlen');
		}

		return $field_props;
	}

	/**
	* {@inheritdoc}
	*/
	public function to_db()
	{
		$db_data = parent::to_db();

		// we do this for postgresql since passing a field_id of null will cause a not-null constraint violation
		if (!$db_data['field_id'])
		{
			unset($db_data['field_id']);
		}

		return $db_data;
	}
}
