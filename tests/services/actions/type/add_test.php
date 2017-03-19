<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\services\actions\type;

use blitze\content\services\actions\type\add;

class add_test extends add_edit_base
{
	/**
	 * @param int $call_count
	 * @return \blitze\content\services\actions\type\add
	 */
	protected function get_command($call_count = 1)
	{
		parent::get_command($call_count);

		return new add($this->auth, $this->language, $this->template, $this->user, $this->auto_lang, $this->fields_factory, $this->views_factory);
	}

	public function test_add_type()
	{
		$command = $this->get_command();
		$command->execute('admin_url', '', 'my.bar.view');

		$expected = array(
			'view'				=> array(
				array(
					'LABEL'			=> 'CONTENT_DISPLAY_BAR',
					'VALUE'			=> 'my.bar.view',
					'S_SELECTED'	=> true,
				),
				array(
					'LABEL'			=> 'CONTENT_DISPLAY_FOO',
					'VALUE'			=> 'my.foo.view',
					'S_SELECTED'	=> false,
				),
			),
		    'POST_AUTHOR'		=> 'admin',
		    'POST_DATE'			=> 'NOW',
		    'ITEMS_PER_PAGE'	=> 10,
		    'TOPICS_PER_GROUP'	=> 4,
		    'U_ACTION'			=> 'admin_url&amp;do=save&amp;type=',
		    'S_TYPE_OPS'		=> '<option value="text">FORM_FIELD_TEXT</option>' .
		    						'<option value="checkbox">FORM_FIELD_CHECKBOX</option>',
		    'S_FORUM_OPTIONS'	=> '<option value="1">Your first category</option>' .
		    						'<option value="2">Your first forum</option>' .
		    						'<option value="6">Sitemaker Content</option>' .
		    						'<option value="7">News</option>',
		    'S_CAN_COPY_PERMISSIONS' => true,
		    'S_EDIT' => true,
		);

		$this->assertEquals($expected, $this->template->assign_display('test'));
	}
}
