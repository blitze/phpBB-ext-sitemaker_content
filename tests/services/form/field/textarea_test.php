<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\services\form\field;

use phpbb\request\request_interface;
use blitze\content\services\form\field\textarea;

class textarea_test extends base_form_field
{
	protected $request;
	protected $template;
	protected $util;

	/**
	 * Configure the test environment.
	 *
	 * @return void
	 */
	public function setUp()
	{
		parent::setUp();

		require_once dirname(__FILE__) . '/../../../../vendor/urodoz/truncate-html/src/TruncateInterface.php';
		require_once dirname(__FILE__) . '/../../../../vendor/urodoz/truncate-html/src/TruncateService.php';
	}

	/**
	 * Create the form field service
	 *
	 * @param array $variable_map
	 * @return \blitze\content\services\form\field\field_interface
	 */
	protected function get_form_field($field, array $variable_map = array(), $previewing = false)
	{
		global $db, $phpbb_dispatcher, $phpbb_path_helper, $template, $phpbb_root_path, $phpEx;

		$auth = $this->getMock('\phpbb\auth\auth');
		$auth->expects($this->any())
			->method('acl_get')
			->with($this->stringContains('_'), $this->anything())
			->willReturn(true);
		$config = new \phpbb\config\config(array(
			'allow_bbcode'	=> true,
			'allow_post_flash'	=> true,
		));
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		$db = $this->getMockBuilder('\phpbb\db\driver\factory')
			->disableOriginalConstructor()
			->getMock();

		$tpl_data = array();
		$template = $this->getMockBuilder('\phpbb\template\template')
			->getMock();
		$template->expects($this->any())
			->method('assign_var')
			->will($this->returnCallback(function($key, $value) use (&$tpl_data) {
				$tpl_data[$key] = $value;
			}));
		$template->expects($this->any())
			->method('assign_vars')
			->will($this->returnCallback(function($data) use (&$tpl_data) {
				$tpl_data = array_merge($tpl_data, $data);
			}));
		$template->expects($this->any())
			->method('assign_block_vars')
			->will($this->returnCallback(function($key, $data) use (&$tpl_data) {
				$tpl_data[$key][] = $data;
			}));
		$template->expects($this->any())
			->method('retrieve_block_vars')
			->willReturn(array());
		$template->expects($this->any())
			->method('assign_display')
			->will($this->returnCallback(function() use (&$tpl_data) {
				return $tpl_data;
			}));
		$this->template =& $template;

		$pagination = $this->getMockBuilder('\phpbb\pagination')
			->disableOriginalConstructor()
			->getMock();
		$pagination->expects($this->any())
			->method('validate_start')
			->will($this->returnCallback(function($start) {
				return $start;
			}));

		$this->request->expects($this->any())
			->method('variable')
			->with($this->anything())
			->will($this->returnValueMap($variable_map));

		$this->request->expects($this->any())
			->method('is_set')
			->with('preview')
			->willReturn($previewing);

		$filesystem = new \phpbb\filesystem\filesystem();
		$phpbb_path_helper = new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new \phpbb_mock_request()
			),
			$filesystem,
			$this->request,
			$phpbb_root_path,
			$phpExt
		);

		$this->util = $this->getMockBuilder('\blitze\sitemaker\services\util')
			->disableOriginalConstructor()
			->getMock();

		return new textarea($this->language, $this->request, $this->ptemplate, $auth, $config, $pagination, $template, $this->util, $phpbb_root_path, $phpEx);
	}

	public function test_name()
	{
		$field = $this->get_form_field('textarea');
		$this->assertEquals('textarea', $field->get_name());
	}

	public function test_langname()
	{
		$field = $this->get_form_field('textarea');
		$this->assertEquals('FORM_FIELD_TEXTAREA', $field->get_langname());
	}

	public function test_default_props()
	{
		$field = $this->get_form_field('textarea');
		$this->assertEquals(array(
			'size'		=> 'large',
			'maxlength'	=> '',
			'max_chars'	=> 200,
			'editor'	=> true,
		), $field->get_default_props());
	}

	/**
	 * @return array
	 */
	public function display_field_test_data()
	{
		$pages_string = 'Page 1 content<p><!-- pagebreak --></p>' .
			'Page 2 content<p><!-- pagebreak --></p>' .
			'Page 3 content<p><!-- pagebreak --></p>'; // invalid xtra page break

		$pages_toc_string = 'Page 1 content<p><!-- pagebreak --></p>' .
			'<h4>Title 2</h4>Page 2 content<p><!-- pagebreak --></p>' .
			'Page 3 content<p><!-- pagebreak --></p>'; // invalid xtra page break

		return array(

		// if no content, should display no content
			array(
				'summary',
				array(
					'field_value'	=> '',
				),
				0,
				'',
				array(),
			),
			array(
				'detail',
				array(
					'field_value'	=> '',
				),
				0,
				'',
				array(
					'S_NOT_LAST_PAGE' => true,
				),
			),

		// content is truncated if summary view and max_chars is provided
			array(
				'summary',
				array(
					'field_value'	=> 'Conveniently incentivize extensive e-commerce vis-a-vis.',
					'field_props'	=> array(
						'max_chars'		=> 0,
					),
				),
				2,
				'Conveniently incentivize extensive e-commerce vis-a-vis.',
				array(),
			),
			array(
				'summary',
				array(
					'field_value'	=> 'Conveniently incentivize extensive e-commerce vis-a-vis.',
					'field_props'	=> array(
						'max_chars'		=> 30,
					),
				),
				0,
				'Conveniently incentivize...',
				array(),
			),
			array(
				'detail',
				array(
					'field_value'	=> 'Conveniently incentivize extensive e-commerce vis-a-vis.',
					'field_props'	=> array(
						'max_chars'		=> 30,
					),
				),
				2,
				'Conveniently incentivize extensive e-commerce vis-a-vis.',
				array(
					'S_NOT_LAST_PAGE' => false,
				),
			),

		// summary view should always display first page with no heading
			array(
				'summary',
				array(
					'field_value'	=> $pages_string,
				),
				2,
				'Page 1 content',
				array(),
			),
			array(
				'summary',
				array(
					'field_value'	=> $pages_toc_string,
				),
				2,
				'Page 1 content',
				array(),
			),

		// detail view should display requested page, if it exists, with heading, if it exists
			array(
				'detail',
				array(
					'field_value'	=> $pages_string,
				),
				3,
				array(
					'foo' => 'Page 3 content',
				),
				array(
					'S_NOT_LAST_PAGE' => false,
				),
			),
			array(
				'detail',
				array(
					'field_value'	=> $pages_toc_string,
				),
				2,
				array(
					'foo' => '<h4>Title 2</h4>Page 2 content',
				),
				array(
					'S_NOT_LAST_PAGE' => true,
					'toc'	=> array(
						0 => array(
							'TITLE' => 'CONTENT_TOC_OVERVIEW',
							'S_PAGE' => false,
							'U_VIEW' => 'phpBB/?',
						),
						1 => array(
							'TITLE' => 'Title 2',
							'S_PAGE' => true,
							'U_VIEW' => 'phpBB/?&amp;page=1',
						),
						2 => array(
							'TITLE' => 'CONTENT_TOC_UNTITLED',
							'S_PAGE' => false,
							'U_VIEW' => 'phpBB/?&amp;page=2',
						),
					),
				),
			),
			array(
				'detail',
				array(
					'field_value'	=> $pages_toc_string,
				),
				3,
				array(
					'foo' => 'Page 3 content',
				),
				array(
					'S_NOT_LAST_PAGE' => false,
					'toc'	=> array(
						0 => array(
							'TITLE' => 'CONTENT_TOC_OVERVIEW',
							'S_PAGE' => false,
							'U_VIEW' => 'phpBB/?',
						),
						1 => array(
							'TITLE' => 'Title 2',
							'S_PAGE' => false,
							'U_VIEW' => 'phpBB/?&amp;page=1',
						),
						2 => array(
							'TITLE' => 'CONTENT_TOC_UNTITLED',
							'S_PAGE' => true,
							'U_VIEW' => 'phpBB/?&amp;page=2',
						),
					),
				),
			),

		// if requested page does not exist, display first page
			array(
				'detail',
				array(
					'field_value'	=> $pages_toc_string,
				),
				5,
				'Page 1 content',
				array(
					'S_NOT_LAST_PAGE' => true,
					'toc'	=> array(
						0 => array(
							'TITLE' => 'CONTENT_TOC_OVERVIEW',
							'S_PAGE' => true,
							'U_VIEW' => 'phpBB/?',
						),
						1 => array(
							'TITLE' => 'Title 2',
							'S_PAGE' => false,
							'U_VIEW' => 'phpBB/?&amp;page=1',
						),
						2 => array(
							'TITLE' => 'CONTENT_TOC_UNTITLED',
							'S_PAGE' => false,
							'U_VIEW' => 'phpBB/?&amp;page=2',
						),
					),
				),
			),

		// Form submitted and we are previewing
			array(
				'detail',
				array(
					'field_value'	=> $pages_toc_string,
				),
				0,
				'Page 1 content<p><hr class="dashed"></p>' .
				'<h4>Title 2</h4>Page 2 content<p><hr class="dashed"></p>' .
				'Page 3 content',
				array(),
				true,
			),
		);
	}

	/**
	 * @dataProvider display_field_test_data
	 * @param string $view
	 * @param array $data
	 * @param int $page
	 * @param string $expected_content
	 * @param array $expected_toc
	 * @return void
	 */
	public function test_display_textarea_field($view, array $data, $page, $expected_content, $expected_toc, $previewing = false)
	{
		$variable_map = array(array('page', 0, false, request_interface::REQUEST, $page - 1));
		$data['field_name'] = 'foo';

		$field = $this->get_form_field('textarea', $variable_map, $previewing);
		$html = $field->display_field($data, array(), $view);

		$this->assertEquals($expected_content, $html);
		$this->assertEquals($expected_toc, $this->template->assign_display('test'));
	}

	/**
	 * @return array
	 */
	public function show_textarea_field_test_data()
	{
		return array(
			array(
				'foo',
				array(
					'field_name'	=> 'foo',
					'field_value'	=> '',
				),
				array(
					array('foo', '', true, request_interface::REQUEST, ''),
				),
				'<textarea id="smc-foo" class="inputbox" name="foo" rows="15" maxlength="" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);" onfocus="initInsertions();" data-bbcode="true"></textarea>',
			),
			array(
				'foo',
				array(
					'field_name'	=> 'foo',
					'field_value'	=> 'bar',
					'field_props'	=> array(
						'size'			=> 'small',
						'maxlength'		=> 100,
						'editor'		=> false,
					),
				),
				array(
					array('foo', 'bar', true, request_interface::REQUEST, 'bar'),
				),
				'<textarea id="smc-foo" class="inputbox" name="foo" rows="5" maxlength="100">bar</textarea>',
			),
		);
	}

	/**
	 * @dataProvider show_textarea_field_test_data
	 * @param string $name
	 * @param array $data
	 * @param array $variable_map
	 * @param string $expected
	 * @return void
	 */
	public function test_show_textarea_field($name, array $data, array $variable_map, $expected)
	{
		$field = $this->get_form_field('textarea', $variable_map);
		$data = $this->get_data('textarea', $name, $data, $field->get_default_props());

		$this->util->expects($this->exactly((int) $data['field_props']['editor']))
			->method('add_assets');

		$this->assertContains($expected, $field->show_form_field($name, $data));
	}

	/**
	 * @return array
	 */
	public function test_field_validation_data()
	{
		return array(
			array(
				array(
					'field_value'	=> 'some random statement',
					'field_label'	=> 'Foo',
					'field_props'	=> array(
						'maxlength'		=> 5,
					),
				),
				'FIELD_TOO_LONG Foo 5',
			),
			array(
				array(
					'field_value'	=> 'some random statement',
					'field_label'	=> 'Foo',
					'field_props'	=> array(
						'maxlength'		=> 55,
					),
				),
				'',
			),
		);
	}

	/**
	 * @dataProvider test_field_validation_data
	 * @param array $data
	 * @param string $expected
	 * @return void
	 */
	public function test_field_validation(array $data, $expected)
	{
		$field = $this->get_form_field('textarea');
		$this->assertEquals($expected, $field->validate_field($data));
	}
}
