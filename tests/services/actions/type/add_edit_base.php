<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\services\actions\type;

require_once dirname(__FILE__) . '/../../../../../../../includes/functions_admin.php';

class add_edit_base extends \phpbb_database_test_case
{
	protected $auth;
	protected $controller_helper;
	protected $db;
	protected $language;
	protected $template;
	protected $user;
	protected $auto_lang;
	protected $fields_factory;
	protected $topic_blocks_factory;
	protected $views_factory;

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
	 * @param int $call_count
	 */
	protected function get_command($call_count = 1)
	{
		global $db, $phpbb_dispatcher;

		$this->auth = $this->getMock('\phpbb\auth\auth');
		$this->auth->expects($this->any())
			->method('acl_get')
			->with($this->stringContains('_'), $this->anything())
			->willReturn(true);

		$this->controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		$this->controller_helper->expects($this->any())
			->method('route')
			->with($this->equalTo('blitze_content_field_settings'))
			->willReturn('phpBB/app.php/content/admin/field');

		$this->db = $db = $this->new_dbal();
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		$this->language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$this->language->expects($this->any())
			->method('lang')
			->willReturnCallback(function () {
				return implode(' ', func_get_args());
			});

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

		$this->user = $this->getMockBuilder('\phpbb\user')
			->disableOriginalConstructor()
			->getMock();
		$this->user->expects($this->any())
			->method('format_date')
			->willReturn('NOW');
		$this->user->data['username'] = 'admin';

		$this->auto_lang = $this->getMockBuilder('\blitze\sitemaker\services\auto_lang')
			->disableOriginalConstructor()
			->getMock();
		$this->auto_lang->expects($this->exactly($call_count))
			->method('add')
			->with('form_fields');

		$request = $this->getMock('\phpbb\request\request_interface');

		$ptemplate = $this->getMockBuilder('\blitze\sitemaker\services\template')
			->disableOriginalConstructor()
			->getMock();

		$text_field = new \blitze\content\services\form\field\text($this->language, $request, $ptemplate);
		$checkbox_field = new \blitze\content\services\form\field\checkbox($this->language, $request, $ptemplate);

		// Let's create some fake objects
		$foo_view = $this->getMockBuilder('\stdClass')
			->setMethods(array('get_langname'))
			->getMock();
		$foo_view->expects($this->exactly($call_count))
			->method('get_langname')
			->will($this->returnValue('CONTENT_DISPLAY_FOO'));

		$bar_view = $this->getMockBuilder('\stdClass')
			->setMethods(array('get_langname'))
			->getMock();
		$bar_view->expects($this->exactly($call_count))
			->method('get_langname')
			->will($this->returnValue('CONTENT_DISPLAY_BAR'));

		$foo_topic_block = $this->getMockBuilder('\stdClass')
			->setMethods(array('get_name', 'get_langname'))
			->getMock();
		$foo_topic_block->expects($this->any())
			->method('get_name')
			->will($this->returnValue('foo'));
		$foo_topic_block->expects($this->any())
			->method('get_langname')
			->will($this->returnValue('TOPIC_BLOCK_FOO'));

		$bar_topic_block = $this->getMockBuilder('\stdClass')
			->setMethods(array('get_name', 'get_langname'))
			->getMock();
		$bar_topic_block->expects($this->any())
			->method('get_name')
			->will($this->returnValue('bar'));
		$bar_topic_block->expects($this->any())
			->method('get_langname')
			->will($this->returnValue('TOPIC_BLOCK_BAR'));

		$phpbb_container = new \phpbb_mock_container_builder();
		$phpbb_container->set('my.foo.field', $text_field);
		$phpbb_container->set('my.bar.field', $checkbox_field);
		$phpbb_container->set('my.foo.view', $foo_view);
		$phpbb_container->set('my.bar.view', $bar_view);
		$phpbb_container->set('my.foo.topic_block', $foo_topic_block);
		$phpbb_container->set('my.bar.topic_block', $bar_topic_block);

		$form_fields = new \phpbb\di\service_collection($phpbb_container);
		$form_fields->add('my.foo.field');
		$form_fields->add('my.bar.field');
		$this->fields_factory = new \blitze\content\services\form\fields_factory($this->language, $form_fields);

		$views = new \phpbb\di\service_collection($phpbb_container);
		$views->add('my.foo.view');
		$views->add('my.bar.view');
		$this->views_factory = new \blitze\content\services\views\views_factory($this->language, $views);

		$topic_blocks = new \phpbb\di\service_collection($phpbb_container);
		$topic_blocks->add('my.foo.topic_block');
		$topic_blocks->add('my.bar.topic_block');
		$this->topic_blocks_factory = new \blitze\content\services\topic\blocks_factory($topic_blocks);
	}
}
