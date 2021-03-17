<?php

/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\services\form;

use blitze\content\services\form\fields_factory;
use blitze\content\services\form\field\checkbox;
use blitze\content\services\form\field\hidden;
use blitze\content\services\form\field\number;
use blitze\content\services\form\form;

class form_test extends \phpbb_test_case
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
	 * Configure the test environment.
	 *
	 * @return void
	 */
	public function setUp()
	{
		global $phpbb_container;

		parent::setUp();

		$language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$language->expects($this->any())
			->method('lang')
			->willReturnCallback(function ()
			{
				return implode('-', func_get_args());
			});

		$request = $this->getMock('\phpbb\request\request_interface');

		$template_context = $this->getMockBuilder('phpbb\template\context')
			->getMock();
		$template_context->expects($this->any())
			->method('get_root_ref')
			->willReturn(array('S_FORM_TOKEN' => 'foo_key'));

		$tpl_data = array();
		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->disableOriginalConstructor()
			->getMock();
		$this->template->expects($this->any())
			->method('assign_vars')
			->will($this->returnCallback(function ($data) use (&$tpl_data)
			{
				$tpl_data = array_merge($tpl_data, $data);
			}));
		$this->template->expects($this->any())
			->method('assign_block_vars')
			->will($this->returnCallback(function ($block, $data) use (&$tpl_data)
			{
				$tpl_data[$block][] = $data;
			}));
		$this->template->expects($this->any())
			->method('assign_block_vars_array')
			->will($this->returnCallback(function ($block, $data) use (&$tpl_data)
			{
				$tpl_data[$block] = $data;
			}));
		$this->template->expects($this->any())
			->method('render_view')
			->will($this->returnCallback(function () use (&$tpl_data)
			{
				return $tpl_data;
			}));

		$this->auto_lang = $this->getMockBuilder('\blitze\sitemaker\services\auto_lang')
			->disableOriginalConstructor()
			->getMock();
		/*
		$this->auto_lang->expects($this->exactly($call_count))
			->method('add')
			->with('form_fields');
		*/

		$phpbb_container = new \phpbb_mock_container_builder();
		$phpbb_container->set('my.checkbox.field', new checkbox($language, $request, $this->template));
		$phpbb_container->set('my.hidden.field', new hidden($language, $request, $this->template));
		$phpbb_container->set('my.number.field', new number($language, $request, $this->template));

		$collection = new \phpbb\di\service_collection($phpbb_container);
		$collection->add('my.checkbox.field');
		$collection->add('my.hidden.field');
		$collection->add('my.number.field');

		$fields_factory = new fields_factory($language, $collection);
		$this->form = new form($request, $template_context, $language, $this->auto_lang, $fields_factory, $this->template);
	}

	/**
	 * Test get_all method
	 */
	public function test_get_form()
	{
	}
}
