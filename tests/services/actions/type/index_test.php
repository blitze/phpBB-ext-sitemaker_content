<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\services\actions\type;

use blitze\content\services\actions\type\index;

class index_test extends \phpbb_database_test_case
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
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/contents.xml');
	}

	/**
	 * @return \blitze\content\services\actions\type\index
	 */
	protected function get_command()
	{
		global $phpbb_dispatcher, $phpbb_admin_path, $phpbb_root_path, $phpEx;

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

		$controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		$controller_helper->expects($this->any())
			->method('route')
			->willReturnCallback(function($route, $params) {
				return $route . '-' . $params['type'];
			});

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
		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->getMock();
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

		return new index($controller_helper, $language, $this->template, $types, $phpbb_root_path, $phpbb_admin_path, $phpEx);
	}

	/**
	 * Test delete content type
	 */
	public function test_type_index()
	{
		$command = $this->get_command();
		$command->execute('admin_url');

		$result = $this->template->assign_display('test');

		$expected = array(
			array(
				'CONTENT_TYPE'		=> 'Foo',
				'L_FORUM_PERMS'		=> 'EDIT_FORUM_PERMISSIONS Foo',
				'S_ENABLED'			=> true,
				'U_DELETE'			=> 'admin_url&amp;do=pre_delete&amp;type=foo',
				'U_EDIT'			=> 'admin_url&amp;do=edit&amp;type=foo',
				'U_STATUS'			=> 'admin_url&amp;do=toggle_status&amp;type=foo',
				'U_VIEW'			=> 'blitze_content_index-foo',
				'U_POST'			=> 'phpBB/ucp.php?i=-blitze-content-ucp-content_module&amp;mode=content&amp;action=post&amp;type=foo',
				'U_GROUP_PERMS'		=> 'phpBB/index.php?i=acp_permissions&amp;mode=setting_group_global',
				'U_FORUM_PERMS'		=> 'phpBB/index.php?i=acp_permissions&amp;mode=setting_forum_local&amp;forum_id[]=4',
        	),
        	array(
        		'CONTENT_TYPE'		=> 'Bar',
        		'L_FORUM_PERMS'		=> 'EDIT_FORUM_PERMISSIONS Bar',
        		'S_ENABLED'			=> false,
        		'U_DELETE'			=> 'admin_url&amp;do=pre_delete&amp;type=bar',
        		'U_EDIT'			=> 'admin_url&amp;do=edit&amp;type=bar',
        		'U_STATUS'			=> 'admin_url&amp;do=toggle_status&amp;type=bar',
        		'U_VIEW'			=> 'blitze_content_index-bar',
        		'U_POST'			=> 'phpBB/ucp.php?i=-blitze-content-ucp-content_module&amp;mode=content&amp;action=post&amp;type=bar',
        		'U_GROUP_PERMS'		=> 'phpBB/index.php?i=acp_permissions&amp;mode=setting_group_global',
        		'U_FORUM_PERMS'		=> 'phpBB/index.php?i=acp_permissions&amp;mode=setting_forum_local&amp;forum_id[]=5',
        	),
        );

        $this->assertEquals($expected, $result['types']);
	}
}
