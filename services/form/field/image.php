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
	/** @var \blitze\sitemaker\services\filemanager\setup */
	protected $filemanager;

	/** @var \blitze\sitemaker\services\util */
	protected $util;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language                  	$language       	Language object
	 * @param \phpbb\request\request_interface				$request			Request object
	 * @param \blitze\sitemaker\services\template			$ptemplate			Sitemaker template object
	 * @param \blitze\sitemaker\services\filemanager\setup	$filemanager		Filemanager object
	 * @param \blitze\sitemaker\services\util				$util       		Sitemaker utility object
	 * @param string										$phpbb_root_path	phpBB root path
	 * @param string										$php_ext			php file extension
	 */
	public function __construct(\phpbb\language\language $language, \phpbb\request\request_interface $request, \blitze\sitemaker\services\template $ptemplate, \blitze\sitemaker\services\filemanager\setup $filemanager, \blitze\sitemaker\services\util $util, $phpbb_root_path, $php_ext)
	{
		parent::__construct($language, $request, $ptemplate);

		$this->filemanager = $filemanager;
		$this->util = $util;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

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
	public function display_field(array $data, array $topic_data, $view_mode)
	{
		if ($data['field_value'])
		{
			return $this->get_image_html($data['field_value'], $view_mode, $data['field_label'], $data['field_props']);
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

		$this->util->add_assets(array(
			'js'	=> array(
				'@blitze_content/vendor/fancybox/dist/jquery.fancybox.min.js',
				100 => '@blitze_content/assets/fields/form.min.js',
			),
			'css'	=> array(
				'@blitze_content/vendor/fancybox/dist/jquery.fancybox.min.css',
			),
		));

		$this->set_filemanager($data);
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

	/**
	 * @param string $image
	 * @param string $mode
	 * @param string $title
	 * @param array $field_props
	 * @return string
	 */
	private function get_image_html($image, $mode, $title, array $field_props)
	{
		$image = '<img src="' . $image . '" class="postimage" alt="' . $title . '" />';

		$html = '<figure class="img-ui">' . $image . '</figure>';
		if ($mode !== 'block')
		{
			$view_props = array_fill_keys(array($mode . '_size', $mode . '_align'), '');
			$image_props = array_filter(array_intersect_key($field_props, $view_props));
			$html = '<div class="' . join(' ', $image_props) . '">' . $html . '</div>';
		}
		return $html;
	}

	/**
	 * @param array $data
	 * @return void
	 */
	protected function set_filemanager(array &$data)
	{
		if ($this->filemanager->is_enabled())
		{
			$this->filemanager->ensure_config_is_ready();

			$data['filemanager_path'] = append_sid("{$this->phpbb_root_path}ResponsiveFilemanager/filemanager/dialog.$this->php_ext", array(
				'type'			=> 1,
				'field_id'		=> 'smc-' . $data['field_name'],
				'akey'			=> $this->filemanager->get_access_key(),
			));
		}
	}
}
