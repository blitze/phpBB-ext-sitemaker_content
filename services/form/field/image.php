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
	/**
	 * @inheritdoc
	 */
	public function get_field_value(array $data)
	{
		if ($data['field_value'])
		{
			preg_match('/src="(.*?)"/i', $data['field_value'], $match);
			return (!empty($match[1])) ? $match[1] : '';
		}

		return $data['field_props']['default'];
	}

	/**
	 * @inheritdoc
	 */
	public function display_field(array $data, array $topic_data, $display_mode, $view_mode)
	{
		if ($data['field_value'])
		{
			$image = '<img src="' . $data['field_value'] . '" class="postimage" alt="' . $data['field_label'] . '" />';
			$html = '<figure class="img-ui">' . $image . '</figure>';

			if ($display_mode !== 'block')
			{
				$view_props = array_fill_keys(array($view_mode . '_size', $view_mode . '_align'), '');
				$image_props = array_filter(array_intersect_key($data['field_props'], $view_props));
				$html = '<div class="' . join(' ', $image_props) . '">' . $html . '</div>';
			}
			return $html;
		}
		return '';
	}

	/**
	 * @inheritdoc
	 */
	public function get_submitted_value(array $data)
	{
		$value = $this->request->variable($data['field_name'], $data['field_value']);

		// we wrap this in image bbcode so that images will still work even if this extension is uninstalled
		// this complicates things for us as we have to remove the bbcode when showing the form field
		// and match the image source (url) from the image html when displaying the field
		return ($value) ? '[img]' . $value . '[/img]' : '';
	}

	/**
	 * @inheritdoc
	 */
	public function show_form_field(array &$data)
	{
		$data['field_value'] = $this->strip_image_bbcode($data['field_value']);

		$this->ptemplate->assign_vars($data);

		return $this->ptemplate->render_view('blitze/content', "fields/image.html", $this->get_name() . '_field');
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'default'		=> '',
			'detail_align'	=> '',
			'detail_size'	=> '',
			'summary_align'	=> '',
			'summary_size'	=> '',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'image';
	}

	/**
	 * @param string $bbcode_string
	 * @return string
	 */
	private function strip_image_bbcode($bbcode_string)
	{
		return str_replace(array('[img]', '[/img]'), '', $bbcode_string);
	}
}
