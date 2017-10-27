<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\services\actions\type;

use phpbb\request\request_interface;
use blitze\content\services\actions\type\save;

class save_test extends \phpbb_database_test_case
{
	protected $language;
	protected $mapper_factory;

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
	 * @param string $type
	 * @param array $variable_map
	 * @return \blitze\content\services\actions\type\save
	 */
	protected function get_command($type, $variable_map)
	{
		global $db, $request, $phpbb_dispatcher, $user, $phpbb_admin_path, $phpEx;

		$table_prefix = 'phpbb_';
		$tables = array(
			'mapper_tables'		=> array(
				'fields'	=> $table_prefix . 'sm_content_fields',
				'types'		=> $table_prefix . 'sm_content_types',
			)
		);

		$auth = $this->getMock('\phpbb\auth\auth');
		$cache = new \phpbb_mock_cache();
		$config = new \phpbb\config\config(array(
			'blitze_content_forum_id' => 6,
		));
		$db = $this->new_dbal();
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		$user = $this->getMockBuilder('\phpbb\user')
			->disableOriginalConstructor()
			->getMock();

		$this->language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$this->language->expects($this->any())
			->method('lang')
			->willReturnCallback(function() {
				return implode(' ', func_get_args());
			});

		$request = $this->getMock('\phpbb\request\request_interface');
		$request->expects($this->any())
			->method('variable')
			->with($this->anything())
			->will($this->returnValueMap($variable_map));

		$this->mapper_factory = new \blitze\content\model\mapper_factory($db, $tables);

		$types = new \blitze\content\services\types($cache, $this->mapper_factory);

		$forum_manager = $this->getMockBuilder('\blitze\sitemaker\services\forum\manager')
			->disableOriginalConstructor()
			->getMock();
		$forum_manager->expects($this->any())
			->method('add')
			->willReturnCallback(function(&$forum_data, $forum_perm_from) {
				$forum_data['forum_id'] = (isset($forum_data['forum_id'])) ? $forum_data['forum_id'] : 8;
			});

		return new save($auth, $cache, $config, $db, $this->language, $request, $types, $forum_manager, $this->mapper_factory, $phpbb_admin_path, $phpEx, false);
	}

	/**
	 * Data set for test_save_type
	 * @return array
	 */
	public function save_type_test_data()
	{
		$description = '';
		$content_view = 'blitze.content.view.blog';
		$fields_data = array(
			'field1'	=> array(
				'field_label'	=> 'Field 1',
				'field_name'	=> 'field1',
				'field_explain'	=> '',
				'field_type'	=> 'checkbox',
			),
			'field2'	=> array(
				'field_label'	=> 'Field 2',
				'field_name'	=> 'field2',
				'field_explain'	=> '',
				'field_type'	=> 'textarea',
			),
		);
		$field1_options = array('Option 1', 'Option 2', 'Option 3');
		$field1_defaults = array('Option 1', 'Option 3');
		$field2_settings = array(
			'size'		=> 'small',
			'maxlength'	=> '',
			'max_chars'	=> 300,
			'editor'	=> true,
		);

		return array(
			// tried to add new type but no fields
			array(
				'',
				array(
					array('content_name', '', false, request_interface::REQUEST, 'foo'),
					array('content_langname', '', true, request_interface::REQUEST, 'Foo'),
					array('content_desc', '', true, request_interface::REQUEST, ''),
					array('content_enabled', true, false, request_interface::REQUEST, true),
					array('content_view', '', false, request_interface::REQUEST, 'blitze.content.view.blog'),
					array('copy_forum_perm', 0, false, request_interface::REQUEST, 1),
					array('field_data', array('' => array('' => '')), true, request_interface::REQUEST, array()),
				),
				'',
				array(),
				'EXCEPTION_INVALID_ARGUMENT content_fields FIELD_MISSING',
			),
			// edit content but no fields provided
			array(
				'news',
				array(
					array('content_name', '', false, request_interface::REQUEST, 'foo'),
					array('content_langname', '', true, request_interface::REQUEST, 'Foo'),
					array('content_desc', '', true, request_interface::REQUEST, ''),
					array('content_enabled', true, false, request_interface::REQUEST, true),
					array('content_view', '', false, request_interface::REQUEST, 'blitze.content.view.blog'),
					array('copy_forum_perm', 0, false, request_interface::REQUEST, 1),
					array('view_settings', array('' => array('' => '')), true, request_interface::REQUEST, array()),
					array('field_data', array('' => array('' => '')), true, request_interface::REQUEST, array()),
				),
				'',
				array(),
				'EXCEPTION_INVALID_ARGUMENT content_fields FIELD_MISSING',
			),
			array(
				'',
				array(
					array('content_name', '', false, request_interface::REQUEST, 'news'),
					array('content_langname', '', true, request_interface::REQUEST, 'Foo'),
					array('content_desc', '', true, request_interface::REQUEST, ''),
					array('content_enabled', true, false, request_interface::REQUEST, true),
					array('content_view', '', false, request_interface::REQUEST, 'blitze.content.view.blog'),
					array('copy_forum_perm', 0, false, request_interface::REQUEST, 1),
					array('view_settings', array('' => array('' => '')), true, request_interface::REQUEST, array()),
					array('field_data', array('' => array('' => '')), true, request_interface::REQUEST, $fields_data),
					array('field_props', array('' => array('' => '')), true, request_interface::REQUEST, $fields_settings),
					array('field_defaults', array('' => array(0 => '')), true, request_interface::REQUEST, $fields_defaults),
					array('field_options', array('' => array(0 => '')), true, request_interface::REQUEST, $fields_options),
				),
				2,
				array(),
				'EXCEPTION_INVALID_ARGUMENT news CONTENT_NAME_EXISTS',
			),
			// save new content type
			array(
				'',
				array(
					array('content_name', '', false, request_interface::REQUEST, 'foo'),
					array('content_langname', '', true, request_interface::REQUEST, 'Foo'),
					array('content_desc', '', true, request_interface::REQUEST, ''),
					array('content_enabled', true, false, request_interface::REQUEST, true),
					array('content_view', '', false, request_interface::REQUEST, 'blitze.content.view.blog'),
					array('copy_forum_perm', 0, false, request_interface::REQUEST, 1),
					array(array('view_settings', $content_view), array('' => ''), false, request_interface::REQUEST, array('' => '')),
					array('field_data', array('' => array('' => '')), true, request_interface::REQUEST, $fields_data),
					array(array('field_props', 'field1'), array('' => ''), false, request_interface::REQUEST, array('' => '')),
					array(array('field_defaults', 'field1'), array(0 => ''), true, request_interface::REQUEST, $field1_defaults),
					array(array('field_options', 'field1'), array(0 => ''), true, request_interface::REQUEST, $field1_options),
					array(array('field_props', 'field2'), array('' => ''), false, request_interface::REQUEST, $field2_settings),
					array(array('field_defaults', 'field2'), array(0 => ''), true, request_interface::REQUEST, array('' => '')),
					array(array('field_options', 'field2'), array(0 => ''), true, request_interface::REQUEST, array('' => '')),
				),
				2,
				array(
					'forum_id'			=> 8,
					'content_id'		=> 2,
					'content_name'		=> 'foo',
					'content_langname'	=> 'Foo',
					'content_fields'	=> array(
						'field1'	=> array(
							'content_id'		=> 2,
							'field_id'			=> 3,
							'field_order'		=> 0,
							'field_props'	=> array(
								'options'	=> array('Option 1', 'Option 2', 'Option 3'),
								'defaults'	=> array('Option 1', 'Option 3'),
							),
						),
						'field2'	=> array(
							'content_id'		=> 2,
							'field_id'			=> 4,
							'field_order'		=> 1,
							'field_props'	=> array(
								'size'			=> 'small',
								'max_chars'		=> 300,
								'editor'		=> true,
							),
						),
					),
				),
				'CONTENT_TYPE_CREATED <a href="index.php?i=permissions&mode=setting_forum_local&forum_id[]=8"> </a><br /><br /><a href="admin_url">&laquo; </a>',
			),
			array(
				'news',
				array(
					array('content_name', '', false, request_interface::REQUEST, 'foo'),
					array('content_langname', '', true, request_interface::REQUEST, 'Foo'),
					array('content_desc', '', true, request_interface::REQUEST, ''),
					array('content_enabled', true, false, request_interface::REQUEST, true),
					array('content_view', '', false, request_interface::REQUEST, $content_view),
					array('copy_forum_perm', 0, false, request_interface::REQUEST, 0),
					array(array('view_settings', $content_view), array('' => ''), false, request_interface::REQUEST, array('' => '')),
					array('field_data', array('' => array('' => '')), true, request_interface::REQUEST, $fields_data),
					array(array('field_props', 'field1'), array('' => ''), false, request_interface::REQUEST, array('' => '')),
					array(array('field_defaults', 'field1'), array(0 => ''), true, request_interface::REQUEST, $field1_defaults),
					array(array('field_options', 'field1'), array(0 => ''), true, request_interface::REQUEST, $field1_options),
					array(array('field_props', 'field2'), array('' => ''), false, request_interface::REQUEST, $field2_settings),
					array(array('field_defaults', 'field2'), array(0 => ''), true, request_interface::REQUEST, array('' => '')),
					array(array('field_options', 'field2'), array(0 => ''), true, request_interface::REQUEST, array('' => '')),
				),
				1,
				array(
					'forum_id'			=> 7,
					'content_id'		=> 1,
					'content_name'		=> 'foo',
					'content_langname'	=> 'Foo',
					'content_fields'	=> array(
						'field1'	=> array(
							'content_id'		=> 1,
							'field_order'		=> 0,
							'field_props'		=> array(
								'options'	=> array('Option 1', 'Option 2', 'Option 3'),
								'defaults'	=> array('Option 1', 'Option 3'),
							),
						),
						'field2'	=> array(
							'content_id'	=> 1,
							'field_order'	=> 1,
							'field_props'	=> array(
								'size'			=> 'small',
								'max_chars'		=> 300,
								'editor'		=> true,
							),
						),
					),
				),
				'CONTENT_TYPE_UPDATED<br /><br /><a href="admin_url">&laquo; </a>',
			),
		);
	}

	/**
	 * Test save content type
	 *
	 * @dataProvider save_type_test_data
	 * @param string $type
	 * @param array $variable_map
	 * @param int $expected_content_id
	 * @param array $expected_data
	 * @param string $expected_message
	 */
	public function test_save_type($type, $variable_map, $expected_content_id, $expected_data, $expected_message)
	{
		$command = $this->get_command($type, $variable_map);

		try
		{
			if (sizeof($expected_data))
			{
				$this->setExpectedTriggerError(E_USER_NOTICE, $expected_message);
			}

			$command->execute('admin_url', $type);

			$types_mapper = $this->mapper_factory->create('types');

			$entity = $types_mapper->load(array('content_id', '=', $expected_content_id));
			$result = $entity->to_array();

			if (isset($expected_data['content_fields']))
			{
				$content_fields = $expected_data['content_fields'];
				foreach ($content_fields as $field => $expected)
				{
					$this->assertEquals($expected, array_intersect_key($result['content_fields'][$field], $expected));
				}
				unset($expected_data['content_fields']);
			}

			$this->assertEquals($expected_data, array_intersect_key($result, $expected_data));
		}
		catch (\blitze\sitemaker\exception\base $e)
		{
			$this->assertEquals($expected_message, $e->get_message($this->language));
		}
	}
}
