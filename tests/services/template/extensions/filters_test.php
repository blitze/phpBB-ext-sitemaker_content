<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2019 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\services\template\extensions;

class filters_test extends \phpbb_test_case
{
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
	 * Create the template service
	 *
	 * @return \blitze\sitemaker\services\template
	 */
	public function get_template()
	{
		global $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		$config = new \phpbb\config\config(array());

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);

		$user = new \phpbb\user($lang, '\phpbb\datetime');

		$filesystem = new \phpbb\filesystem\filesystem();

		$path_helper =  new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new \phpbb_mock_request()
			),
			$filesystem,
			$this->getMock('\phpbb\request\request_interface'),
			$phpbb_root_path,
			$phpEx
		);

		$container = new \phpbb_mock_container_builder();
		$context = new \phpbb\template\context();
		$cache_path = $phpbb_root_path . 'cache/twig';
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();
		$template_loader = new \phpbb\template\twig\loader($filesystem, '');
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
		$phpbb_extension_manager = new \phpbb_mock_extension_manager($phpbb_root_path, array());
		$twig_extensions = array(new \blitze\content\services\template\extensions\filters());

		$template = new \phpbb\template\twig\twig($path_helper, $config, $context, $twig, $cache_path, $user, $twig_extensions);
		$twig->setLexer(new \phpbb\template\twig\lexer($twig));
		$template->set_custom_style('tests', __DIR__ . '/templates');

		return $template;
	}

	public function test_field_twig_filter()
	{
		$template = $this->get_template();
		
		$test_data = array(
			'FIELDS'	=> array(
				'body'		=> array(
					'foo_image'		=> '<img src="./foo_image.jpg" />',
					'bar_category'	=> 'foo cat',
					'foo2_image'	=> '<img src="./foo2_image.jpg" />',
				),
			),
		);

		$template->set_filenames(array('test' => 'fields.html'));

		$template->assign_var('FIELD_TYPES', array(
			'foo_image'		=> 'image',
			'bar_category'	=> 'category',
			'foo2_image'	=> 'image',
		));

		$template->assign_vars($test_data);
		$template->assign_block_vars_array('topicrow', [$test_data]);

		$result = $template->assign_display('test', '', true);
		$expected = '<img src="./foo_image.jpg" />
bar_category, foo2_image

<img src="./foo_image.jpg" />
foo_image, bar_category, foo2_image

Seamlessly innovate in...

<p>Seamlessly <a href="foo.php">innovate</a>...</p>';

		$this->assertEquals($expected, $result);
	}
}
