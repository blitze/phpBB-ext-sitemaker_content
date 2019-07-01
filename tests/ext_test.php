<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2019 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests;

class ext_test extends \phpbb_test_case
{
	/**
	 * @param string $required_phpbb_version
	 * @param array $required_extensions
	 * @return \blitze\content\ext
	 */
	public function get_ext($required_phpbb_version, array $required_extensions)
	{
		$ext_metadata = array(
			'vendor/foo'	=> ['version' => '2.0.1'],
			'vendor/bar'	=> ['version' => '3.0.0-RC1'],
			'blitze/content'	=> [
				'version'	=> '3.0.0',
				'require'	=> $required_extensions,
				'extra'		=> [
					'soft-require'	=> [
						'phpbb/phpbb'	=> $required_phpbb_version,
					],
				],
			],
		);

		$config = new \phpbb\config\config(['version' => '3.2.7']);

		$translator = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$translator->expects($this->any())
			->method('lang')
			->willReturnCallback(function () {
				return implode('-', func_get_args());
			});

		$this->user = new \phpbb\user($translator, '\phpbb\datetime');
		$this->user->lang = ['EXTENSION_NOT_ENABLEABLE' => ''];

		$container = new \phpbb_mock_container_builder();

		$ext_manager = $this->getMockBuilder('\phpbb\extension\manager')
			->disableOriginalConstructor()
			->getMock();
		$ext_manager->expects($this->any())
			->method('is_enabled')
			->willReturnCallback(function($name) {
				return ($name === 'vendor/foo') ? true : false;
			});
		$ext_manager->expects($this->any())
			->method('is_available')
			->willReturnCallback(function($name) {
				return ($name !== 'vendor/no_exists') ? true : false;
			});
		$ext_manager->expects($this->any())
			->method('create_extension_metadata_manager')
			->willReturnCallback(function($name) use ($ext_metadata) {
				$metadata_manager = new \phpbb_mock_metadata_manager($name, '');
				$metadata = array_merge([
					'name'		=> $name,
					'type'		=> 'phpbb-extension',
					'license'	=> 'GPL2',
					'authors'	=> [['name'	=> 'foo bar']]
				], $ext_metadata[$name]);
				$metadata_manager->set_metadata($metadata);
				return $metadata_manager;
			});

		$container->set('user', $this->user);
		$container->set('config', $config);
		$container->set('ext.manager', $ext_manager);

		$finder = $this->getMockBuilder('\phpbb\finder')
			->disableOriginalConstructor()
			->getMock();

		$migrator = $this->getMockBuilder('\phpbb\db\migrator')
			->disableOriginalConstructor()
			->getMock();

		$ext = new \blitze\content\ext($container, $finder, $migrator, 'some_ext', 'some_path');

		$reflection = new \ReflectionClass($ext);
		$reflection_property = $reflection->getProperty('required_extensions');
		$reflection_property->setAccessible(true);
		$reflection_property->setValue($ext, array_fill_keys(array_keys($required_extensions), '#'));

		return $ext;
	}

	/**
	 * @return array
	 */
	public function ext_test_data()
	{
		return array(
			array(
				['vendor/foo' => '1.0.0'],
				'3.2.8',
				false,
				'<br>PHPBB_VERSION_UNMET-3.2.8',
			),
			array(
				['vendor/foo' => '1.0.0'],
				'>=3.2.8,<3.3.0@dev',
				false,
				'<br>PHPBB_VERSION_UNMET->=3.2.8,<3.3.0@dev',
			),
			array(
				['vendor/foo' => '1.0.0'],
				'>=3.2.7,<3.3.0@dev',
				true,
				'',
			),
			array(
				['vendor/foo' => '3.0.0'],
				'>=3.2.7,<3.3.0@dev',
				false,
				'<br>EXTENSION_VERSION_UNMET-3.0.0-#-vendor/foo-2.0.1',
			),
			array(
				['vendor/bar' => '3.0.0-RC1'],
				'3.2.7',
				true,
				'',
			),
			array(
				['vendor/no_exists' => '2.0.0'],
				'3.2.7',
				false,
				'<br>MISSING_REQUIRED_EXTENSION-vendor/no_exists-#',
			),
		);
	}

	/**
	 * @dataProvider ext_test_data
	 * @param array $required_extensions
	 * @param string $required_phpbb_version
	 * @param bool $expected_result
	 * @param string $expect_message
	 * @return void
	 */
	public function test_is_enableable(array $required_extensions, $required_phpbb_version, $expected_result, $expected_message)
	{
		$ext = $this->get_ext($required_phpbb_version, $required_extensions);
		$result = $ext->is_enableable();

		$this->assertEquals($expected_result, $result);
		$this->assertEquals($expected_message, $this->user->lang['EXTENSION_NOT_ENABLEABLE']);
	}
}
