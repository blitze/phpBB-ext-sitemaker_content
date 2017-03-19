<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\services\actions\type;

use blitze\content\services\actions\type\pre_delete;

require_once dirname(__FILE__) . '/../../../../../../../includes/functions_admin.php';

class pre_delete_test extends \phpbb_database_test_case
{
	/**
	* Define the extensions to be tested
	*
	* @return array vendor/name of extension(s) to test
	*/
	static protected function setup_extensions()
	{
		return array(
			'blitze/sitemaker',
			'blitze/content',
		);
	}

	/**
	 * Load required fixtures.
	 *
	 * @return mixed
	 */
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/forum.xml');
	}

	/**
	 * @return \blitze\content\services\actions\type\index
	 */
	protected function get_command()
	{
		global $db, $phpbb_dispatcher, $template;

		$table_prefix = 'phpbb_';
		$tables = array(
			'mapper_tables'		=> array(
				'fields'	=> $table_prefix . 'sm_content_fields',
				'types'		=> $table_prefix . 'sm_content_types',
			)
		);

		$cache = new \phpbb_mock_cache();
		$db = $this->new_dbal();
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		$language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$language->expects($this->any())
			->method('lang')
			->willReturnCallback(function() {
				return implode(' ', func_get_args());
			});

		$mapper_factory = new \blitze\content\model\mapper_factory($db, $tables);

		$types = new \blitze\content\services\types($cache, $mapper_factory);

		$tpl_data = array();
		$this->template = $template = $this->getMockBuilder('\phpbb\template\template')
			->getMock();
		$this->template->expects($this->any())
			->method('assign_var')
			->will($this->returnCallback(function($key, $value) use (&$tpl_data) {
				$tpl_data[$key] = $value;
			}));
		$this->template->expects($this->any())
			->method('assign_vars')
			->will($this->returnCallback(function($data) use (&$tpl_data) {
				$tpl_data = array_merge($tpl_data, $data);
			}));
		$this->template->expects($this->any())
			->method('assign_block_vars')
			->will($this->returnCallback(function($block, $data) use (&$tpl_data) {
				$tpl_data[$block][] = $data;
			}));
		$this->template->expects($this->any())
			->method('assign_display')
			->will($this->returnCallback(function() use (&$tpl_data) {
				return $tpl_data;
			}));

		return new pre_delete($language, $this->template, $types);
	}

	/**
	 * Test delete content type
	 */
	public function test_pre_delete()
	{
		$command = $this->get_command();
		$command->execute('admin_url', 'news');

		$result = $this->template->assign_display('test');

		$expected = array(
			'S_DELETE_TYPE'			=> true,
			'CONTENT_TYPE'			=> 'news',
			'CONTENT_TYPE_TITLE'	=> 'News',
			'S_MOVE_FORUM_OPTIONS'	=> '<option value="1">Your first category</option>' .
										'<option value="2">Your first forum</option>' .
										'<option value="6">Sitemaker Content</option>' .
										'<option value="7" disabled="disabled" class="disabled-option">News</option>',
			'U_ACTION'				=> 'admin_url',
        );

		$this->assertTrue(isset($result['S_FORM_TOKEN']));
        $this->assertEquals($expected, array_intersect_key($result, $expected));
	}
}
