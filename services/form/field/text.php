<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class text extends base
{
	/** @var int */
	protected $maxlength = 0;

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'text';
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'maxlength'	=> 255,
			'size'		=> 40,
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_validation_rules(array $data)
	{
		$this->maxlength = $data['field_props']['maxlength'];

		return array(
			'filter'	=> FILTER_CALLBACK,
			'options'	=> array(
				'options'	=> array($this, 'is_valid'),
			),
		);
	}

	/**
	 * @param string $value
	 * @return false|string
	 */
	protected function is_valid($value)
	{
		if ($this->maxlength && utf8_strlen($value) > $this->maxlength)
		{
			return false;
		}

		return $value;
	}

	/**
	 * @inheritdoc
	 */
	public function get_error_message(array $data)
	{
		return $this->language->lang('FIELD_TOO_LONG', $data['field_label'], $this->maxlength);
	}
}
