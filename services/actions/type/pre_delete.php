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

class pre_delete implements action_interface
{
	/** @var\phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \blitze\content\services\types */
	protected $content_types;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language					$language			Language Object
	 * @param \phpbb\template\template					$template			Template object
	 * @param \blitze\content\services\types			$content_types		Content types object
	*/
	public function __construct(\phpbb\language\language $language, \phpbb\template\template $template, \blitze\content\services\types $content_types)
	{
		$this->language = $language;
		$this->template = $template;
		$this->content_types = $content_types;
	}

	/**
	 * @inheritdoc
	 */
	public function execute($u_action, $type = '')
	{
		$entity = $this->content_types->get_type($type);

		add_form_key('delete_content_type');

		$this->template->assign_vars(array(
			'S_DELETE_TYPE'			=> true,
			'CONTENT_TYPE'			=> $type,
			'CONTENT_TYPE_TITLE'	=> $this->language->lang($entity->get_content_langname()),
			'S_MOVE_FORUM_OPTIONS'	=> make_forum_select(false, $entity->get_forum_id(), true, false, false),
			'U_ACTION'				=> $u_action,
		));
	}
}
