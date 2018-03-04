<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\notification;

class topic_in_queue extends \phpbb\notification\type\topic_in_queue
{
	/* @var \blitze\content\services\types */
	protected $types;

	/**
	 * Notification Type Base Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\language\language          $language
	 * @param \phpbb\user                       $user
	 * @param \phpbb\auth\auth                  $auth
	 * @param string                            $phpbb_root_path
	 * @param string                            $php_ext
	 * @param string                            $user_notifications_table
	 * @param \blitze\content\services\types	$types
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\user $user, \phpbb\auth\auth $auth, $phpbb_root_path, $php_ext, $user_notifications_table, \blitze\content\services\types $types)
	{
		parent::__construct($db, $language, $user, $auth, $phpbb_root_path, $php_ext, $user_notifications_table);

		$this->types = $types;
	}

	/**
	* Get the url to this item
	*
	* @property int $item_parent_id
	* @property int $item_id
	* @return string URL
	*/
	public function get_url()
	{
		if ($type = $this->types->get_forum_type($this->item_parent_id))
		{
			return append_sid($this->phpbb_root_path . 'mcp.' . $this->php_ext, "i=-blitze-content-mcp-content_module&amp;mode=content&amp;type=$type&amp;t={$this->item_id}&amp;do=view");
		}

		return parent::get_url();
	}
}
