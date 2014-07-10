<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\ucp;

class content_info
{
	function module()
	{
		global $db;

		$sql = 'SELECT module_langname, module_mode, module_auth
			FROM ' . MODULES_TABLE . "
			WHERE module_basename = '" . $db->sql_escape('\primetime\content\ucp\content_module') . "'
				AND module_class = 'ucp'
				AND module_mode <> ''
			ORDER BY left_id ASC";
		$result = $db->sql_query($sql);

		$modes = array();
		while($row = $db->sql_fetchrow($result))
		{
			$modes[$row['module_mode']] = array('title' => $row['module_langname'], 'auth' => $row['module_auth'], 'cat' => array('CONTENT_CP'));
		}
		$db->sql_freeresult($result);

		$module_info = array(
			'filename'	=> '\primetime\content\ucp\content_module',
			'title'		=> 'CONTENT_CP',
			'version'	=> '1.0.0',
			'modes'		=> $modes,
		);

		return sizeof($modes) ? $module_info : array();
	}
}
