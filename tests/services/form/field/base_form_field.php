<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\services\form\field;

abstract class base_form_field extends \phpbb_test_case
{
	protected $language;
	protected $request;
	protected $user;
	protected $ptemplate;

	/**
	 * Define the extension to be tested.
	 *
	 * @return string[]
	 */
	protected static function setup_extensions()
	{
		return array(
			'blitze/sitemaker',
			'blitze/content',
		);
	}

	/**
	 */
	public function setUp()
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		$this->language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$this->language->expects($this->any())
			->method('lang')
			->willReturnCallback(function($var) {
				return ($var == 'COMMA_SEPARATOR') ? ', ' : implode(' ', func_get_args());
			});

		$this->request = $this->getMock('\phpbb\request\request_interface');

		$this->user = new \phpbb\user($this->language, '\phpbb\datetime');
		$this->user->timezone = new \DateTimeZone('UTC');
		$this->user->lang['datetime'] = array();
		$this->user->page['root_script_path'] = '/phpBB/';
		$this->user->style = array (
			'style_name' => 'prosilver',
			'style_path' => 'prosilver',
		);

		$filesystem = new \phpbb\filesystem\filesystem();

		$path_helper = new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new \phpbb_mock_request()
			),
			$filesystem,
			$this->request,
			$phpbb_root_path,
			$phpEx
		);

		$cache_path = $phpbb_root_path . 'cache/twig';
		$config = new \phpbb\config\config(array());
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();
		$template_context = new \phpbb\template\context();
		$template_loader = new \phpbb\template\twig\loader(new \phpbb\filesystem\filesystem(), '');
		$twig = new \phpbb\template\twig\environment(
			$config,
			$filesystem,
			$path_helper,
			$cache_path,
			null,
			$template_loader,
			$phpbb_dispatcher,
			array(
				'cache'			=> false,
				'debug'			=> false,
				'auto_reload'	=> true,
				'autoescape'	=> false,
			)
		);

		$this->ptemplate = new \blitze\sitemaker\services\template($path_helper, $config, $template_context, $twig, $cache_path, $this->user, array(new \phpbb\template\twig\extension($template_context, $this->user)));
		$twig->setLexer(new \phpbb\template\twig\lexer($twig));

		$this->ptemplate->set_custom_style('all', $phpbb_root_path . 'ext/blitze/sitemaker/styles/all');
	}

	/**
	 * Create the form field service
	 *
	 * @param string $form_field
	 * @param array $variable_map
	 * @return \blitze\content\services\form\field\field_interface
	 */
	protected function get_form_field($form_field, array $variable_map = array())
	{
		$this->request->expects($this->any())
			->method('variable')
			->with($this->anything())
			->will($this->returnValueMap($variable_map));

		$form_field_class = '\\blitze\\content\\services\\form\\field\\' . $form_field;
		return new $form_field_class($this->language, $this->request, $this->ptemplate);
	}

	/**
	 * @param string $type
	 * @param string $name
	 * @param array $data
	 * @param array $default_data
	 * @return array
	 */
	protected function get_data($type, $name, array $data, array $default_data)
	{
		return array_replace_recursive(array(
			'field_id'		=> $name,
			'field_type'	=> $type,
			'field_props'	=> $default_data,
		), $data);
	}
}
