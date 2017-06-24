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
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\db */
	protected $config;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\template\context */
	protected $template_context;

	/** @var \blitze\sitemaker\services\template */
	protected $ptemplate;

	/** @var \blitze\sitemaker\services\util */
	protected $util;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth							$auth				Auth object
	 * @param \phpbb\config\db							$config				Config object
	 * @param \phpbb\language\language					$language			Language object
	 * @param \phpbb\pagination							$pagination			Pagination object
	 * @param \phpbb\request\request_interface			$request			Request object
	 * @param \phpbb\template\template					$template			Template object
	 * @param \phpbb\template\context					$template_context	Template context object
	 * @param \blitze\sitemaker\services\template		$ptemplate			Sitemaker template object
	 * @param \blitze\sitemaker\services\util			$util				Sitemaker utility object
	 * @param string									$phpbb_root_path	Path to the phpbb includes directory.
	 * @param string									$php_ext			php file extension
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\db $config, \phpbb\language\language $language, \phpbb\pagination $pagination, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\template\context $template_context, \blitze\sitemaker\services\template $ptemplate, \blitze\sitemaker\services\util $util, $phpbb_root_path, $php_ext)
	{
		parent::__construct($language, $request, $ptemplate);

		$this->auth = $auth;
		$this->config = $config;
		$this->pagination = $pagination;
		$this->template = $template;
		$this->template_context = $template_context;
		$this->util = $util;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'textarea';
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'size'		=> 'large',
			'maxlength'	=> '',
			'max_chars'	=> 200,
			'editor'	=> true,
		);
	}

	/**
	 * Display content field
	 *
	 * @param string $field_value
	 * @param array $field_data
	 * @param string $view_mode
	 * @param array $topic_data
	 * @return mixed
	 */
	public function display_field(array $data = array(), $view_mode = 'detail', array $topic_data = array())
	{
		$value = $this->generate_field_pages($data['field_name'], $data['field_value'], $topic_data['TOPIC_URL'], $view_mode);

		if ($view_mode === 'summary' && $data['field_props']['max_chars'])
		{
			$truncateService = new TruncateService();
			$value = $truncateService->truncate($value, $data['field_props']['max_chars']);
		}

		return $value;
	}

	/**
	 * @inheritdoc
	 */
	public function show_form_field($name, array &$data, $forum_id = 0)
	{
		if ($data['field_props']['editor'])
		{
			$asset_path = $this->util->get_web_path();
			$this->util->add_assets(array(
				'js'   => array(
					$asset_path . 'assets/javascript/editor.js',
					'@blitze_content/assets/form/textarea.min.js'
				)
			));

			$data += $this->get_editor($forum_id);
		}

		return parent::show_form_field($name, $data, $forum_id);
	}

	/**
	 * @param int $forum_id
	 * @return array
	 */
	protected function get_editor($forum_id)
	{
		// Assigning custom bbcodes
		if (!function_exists('display_custom_bbcodes'))
		{
			include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
		}

		display_custom_bbcodes();

		$bbcode_status	= ($this->config['allow_bbcode'] && $this->auth->acl_get('f_bbcode', $forum_id)) ? true : false;

		$dataref = $this->template_context->get_data_ref();
		$this->ptemplate->assign_block_vars_array('custom_tags', (isset($dataref['custom_tags'])) ? $dataref['custom_tags'] : array());

		// HTML, BBCode, Smilies, Images and Flash statusf
		return array(
			'S_BBCODE_IMG'			=> ($bbcode_status && $this->auth->acl_get('f_img', $forum_id)) ? true : false,
			'S_LINKS_ALLOWED'		=> ($this->config['allow_post_links']) ? true : false,
			'S_BBCODE_FLASH'		=> ($bbcode_status && $this->auth->acl_get('f_flash', $forum_id) && $this->config['allow_post_flash']) ? true : false,
			'S_BBCODE_QUOTE'		=> false,
			'S_SMILIES_ALLOWED'		=> false,
		);
	}

	/**
	 * Get topic subpages from textarea field
	 *
	 * @param string $field_name
	 * @param string $value
	 * @param string $topic_url
	 * @param string $view_mode
	 * @return void
	 */
	protected function generate_field_pages($field_name, $value, $topic_url, $view_mode)
	{
		if (preg_match_all("#<div data-page=\"(.*?)\">(.*?)</div><br><!-- end page -->#s", $value, $matches))
		{
			$start = 0;
			if ($view_mode === 'detail')
			{
				$start = $this->request->variable('page', 0);
				$this->generate_page_nav($matches[2], $matches[1], $start, $topic_url, $view_mode);
			}

			$value = trim($matches[2][$start]);

			// Hide all other fields if we're looking at page 2+
			if ($start)
			{
				$value = array(
					$field_name => $value,
				);
			}
		}

		return $value;
	}

	/**
	 * Generate pagination for topic subpages
	 *
	 * @param array $pages
	 * @param array $page_titles
	 * @param int $start
	 * @param string $topic_url
	 * @param string $view_mode
	 * @return void
	 */
	protected function generate_page_nav(array $pages, array $page_titles, &$start, $topic_url, $view_mode)
	{
		$total_pages = sizeof($pages);
		$start = $this->pagination->validate_start($start, 1, $total_pages);
		$this->pagination->generate_template_pagination($topic_url, 'page', 'page', $total_pages, 1, $start);
		$this->template->assign_var('S_NOT_LAST_PAGE', !($start === ($total_pages - 1)));

		$this->generate_toc($start, array_filter($page_titles), $topic_url);
		$this->handle_preview($pages);
	}

	/**
	 * Generate Table of contents
	 *
	 * @param int $start
	 * @param $page_titles
	 * @return void
	 */
	protected function generate_toc($start, $page_titles, $topic_url)
	{
		foreach ($page_titles as $page => $title)
		{
			$title = ($title) ? $title : $this->language->lang('CONTENT_TOC_UNTITLED');
			$this->template->assign_block_vars('toc', array(
				'TITLE'		=> $title,
				'S_PAGE'	=> ($page === $start),
				'U_VIEW'	=> append_sid($topic_url, 'page=' . $page),
			));
		}
	}

	/**
	 * When Previewing topic, we show all pages
	 *
	 * @param array $pages
	 * @return void
	 */
	protected function handle_preview(array $pages)
	{
		if ($this->request->is_set('preview'))
		{
			for ($i = 1, $size = sizeof($pages); $i < $size; $i++)
			{
				$this->template->assign_block_vars('pages', array(
					'CONTENT'	=> $pages[$i],
					'PAGE'		=> $i + 1,
				));
			}
		}
	}
}
