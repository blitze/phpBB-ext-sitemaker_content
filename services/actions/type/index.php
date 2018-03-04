<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\actions\type;

use blitze\content\services\actions\action_interface;

class index implements action_interface
{
	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var\phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \blitze\content\services\types */
	protected $content_types;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $phpbb_admin_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\controller\helper				$controller_helper		Controller Helper object
	 * @param \phpbb\language\language				$language				Language Object
	 * @param \phpbb\template\template				$template				Template object
	 * @param \blitze\content\services\types		$content_types			Content types object
	 * @param string								$phpbb_root_path		Path to the phpbb includes directory.
	 * @param string								$relative_admin_path	Relative admin root path
	 * @param string								$php_ext				php file extension
	*/
	public function __construct(\phpbb\controller\helper $controller_helper, \phpbb\language\language $language, \phpbb\template\template $template, \blitze\content\services\types $content_types, $phpbb_root_path, $relative_admin_path, $php_ext)
	{
		$this->controller_helper = $controller_helper;
		$this->language = $language;
		$this->template = $template;
		$this->content_types = $content_types;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpbb_admin_path = $this->phpbb_root_path . $relative_admin_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @inheritdoc
	 */
	public function execute($u_action, $type = '')
	{
		$types_list = array();
		$types = $this->content_types->get_all_types();

		/** @var \blitze\content\model\entity\type $entity */
		foreach ($types as $type => $entity)
		{
			$forum_id = $entity->get_forum_id();
			$langname = $entity->get_content_langname();

			$types_list[] = array(
				'CONTENT_TYPE'	=> $langname,
				'FORUM_PERMS'	=> $this->language->lang('EDIT_FORUM_PERMISSIONS', $langname),

				'S_ENABLED'		=> $entity->get_content_enabled(),

				'U_DELETE'		=> $u_action . '&amp;do=pre_delete&amp;type=' . $type,
				'U_EDIT'		=> $u_action . '&amp;do=edit&amp;type=' . $type,
				'U_STATUS'		=> $u_action . '&amp;do=toggle_status&amp;type=' . $type,
				'U_VIEW'		=> $this->controller_helper->route('blitze_content_type', array('type' => $type)),
				'U_POST'		=> append_sid("{$this->phpbb_root_path}posting." . $this->php_ext, "mode=post&amp;f=$forum_id"),
				'U_GROUP_PERMS'	=> append_sid("{$this->phpbb_admin_path}index." . $this->php_ext, "i=acp_permissions&amp;mode=setting_group_global"),
				'U_FORUM_PERMS'	=> append_sid("{$this->phpbb_admin_path}index." . $this->php_ext, "i=acp_permissions&amp;mode=setting_forum_local&amp;forum_id[]=$forum_id"),
			);
		}

		$this->template->assign_vars(array(
			'TYPES'			=> $types_list,
			'U_ADD_TYPE'	=> $u_action . "&amp;do=add",
		));
	}
}
