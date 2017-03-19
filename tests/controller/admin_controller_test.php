<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\controller;

use blitze\content\controller\admin_controller;
use blitze\content\tests\framework\trigger_error_db_test_case;

require_once dirname(__FILE__) . '/../../../../../includes/functions_acp.php';

class admin_controller_test extends trigger_error_db_test_case
{
	/**
	 * Load required fixtures.
	 *
	 * @return mixed
	 */
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/empty.xml');
	}

	/**
	 * Create the blocks admin controller
	 *
	 * @param string $action
	 * @param string $type
	 * @param string $base_url
	 * @return \blitze\content\controller\admin_controller
	 */
	protected function get_controller($action, $type, $base_url)
	{
		$language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$language->expects($this->any())
			->method('lang')
			->willReturnCallback(function () {
				return implode(' ', func_get_args());
			});

		$dummy_object = $this->getMockBuilder('\stdClass')
			->setMethods(array('execute'))
			->getMock();

		$dummy_object->expects($this->exactly(1))
			->method('execute')
			->with(
				$this->equalTo($base_url),
				$this->equalTo($type)
			)
			->will($this->returnCallback(function() use (&$dummy_object) {
				if ($dummy_object->action === 'no_exists')
				{
					throw new \blitze\sitemaker\exception\unexpected_value(array($dummy_object->action, 'INVALID_ACTION'));
				}
			}));

		$action_handler = $this->getMockBuilder('\blitze\content\services\action_handler')
			->disableOriginalConstructor()
			->getMock();

		$action_handler->expects($this->exactly(1))
			->method('create')
			->with(
				$this->equalTo('type'),
				$this->equalTo($action)
			)
			->will($this->returnCallback(function() use (&$dummy_object, $action) {
				$dummy_object->action = $action;
				return $dummy_object;
			}));

		return new admin_controller($language, $action_handler);
	}

	/**
	 */
	public function test_controller()
	{
		$action = 'add';
		$type = 'news';
		$base_url = 'admin_url';

		$controller = $this->get_controller($action, $type, $base_url);
		$controller->handle($action, $type, $base_url);

		$action = 'no_exists';
		$controller = $this->get_controller($action, $type, $base_url);
		$controller->handle($action, $type, $base_url);
		$this->assertError('EXCEPTION_UNEXPECTED_VALUE no_exists INVALID_ACTION<br /><br /><a href="admin_url">&laquo; </a>', E_USER_WARNING);
	}
}
