<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\services\actions\type;

use blitze\content\services\actions\type\edit;

class edit_test extends add_edit_base
{
	/**
	 * @param int $call_count
	 * @return \blitze\content\services\actions\type\add
	 */
	protected function get_command($call_count = 1)
	{
		parent::get_command($call_count);

		$table_prefix = 'phpbb_';
		$tables = array(
			'mapper_tables'		=> array(
				'fields'	=> $table_prefix . 'sm_content_fields',
				'types'		=> $table_prefix . 'sm_content_types',
			)
		);

		$cache = new \phpbb_mock_cache();
		$mapper_factory = new \blitze\content\model\mapper_factory($this->db, $tables);
		$types = new \blitze\content\services\types($cache, $mapper_factory);

		return new edit($this->auth, $this->language, $this->template, $this->user, $this->auto_lang, $types, $this->fields_factory, $mapper_factory, $this->views_factory);
	}

	/**
	 * Test edit content type with valid type
	 */
	public function test_edit_type()
	{
		$command = $this->get_command();
		$command->execute('admin_url', 'news');
		$result = $this->template->assign_display('test');

		$expected_views = array(
			array(
				'LABEL'			=> 'CONTENT_DISPLAY_BAR',
				'VALUE'			=> 'my.bar.view',
				'S_SELECTED'	=> false,
			),
			array(
				'LABEL'			=> 'CONTENT_DISPLAY_FOO',
				'VALUE'			=> 'my.foo.view',
				'S_SELECTED'	=> true,
			),
		);

		// here we are confirming that the view for the content type was selected
		$this->assertEquals($expected_views, $result['view']);

		$expected_content = array(
			'CONTENT_ID'		=> 1,
    		'FORUM_ID'			=> 7,
    		'CONTENT_NAME'		=> 'news',
    		'CONTENT_LANGNAME'	=> 'News',
			'S_TYPE_OPS'		=> '<option value="text">FORM_FIELD_TEXT</option><option value="checkbox">FORM_FIELD_CHECKBOX</option>',
    		'S_FORUM_OPTIONS'	=> '<option value="1">Your first category</option><option value="2">Your first forum</option><option value="6">Sitemaker Content</option><option value="7" disabled="disabled" class="disabled-option">News</option>',
			'U_ACTION'			=> 'admin_url&amp;do=save&amp;type=news',
		);

		// here we are confirming expected values for some select fields
		$this->assertEquals($expected_content, array_intersect_key($result, $expected_content));

		$expected_fields = array(
            'foo' => array(
            	'field_id'			=> 1,
            	'content_id'		=> 1,
            	'field_name'		=> 'foo',
            	'field_label'		=> 'Foo',
            	'field_type'		=> 'text',
            	'field_settings'	=> array(),
            	'field_order'		=> 0,
            ),
            'bar' => array(
            	'field_id'			=> 2,
            	'content_id'		=> 1,
            	'field_name'		=> 'bar',
            	'field_label'		=> 'Bar',
            	'field_type'		=> 'checkbox',
            	'field_settings'	=> array(
            		'field_options'		=> array(
            			'Red'		=> 'Red',
            			'Blue'		=> 'Blue',
            			'Green'		=> 'Green',
            			'Yellow'	=> 'Yellow',
            		),
            		'field_defaults'	=> array('Blue', 'Yellow'),
            	),
            	'field_order'		=> 1,
            ),
		);

		foreach ($expected_fields as $field => $expected)
		{
			$this->assertEquals($expected, array_intersect_key($result['CONTENT_FIELDS'][$field], $expected));
		}
	}

	/**
	 * Test edit content type when non-existent content type is provided
	 */
	public function test_edit_type_no_exist()
	{
		$command = $this->get_command(0);
		$type = 'foo_bar';

		try
		{
			$command->execute('admin_url', $type);
			$this->fail('no exception thrown');
		}
		catch (\Exception $e)
		{
			$this->assertEquals("EXCEPTION_OUT_OF_BOUNDS {$type}", $e->get_message($this->language));
		}
	}
}
