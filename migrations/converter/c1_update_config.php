<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\migrations\converter;

class c1_update_config extends \phpbb\db\migration\migration
{
	/**
	 * Skip this migration if the config variable content_forum_id does not exist
	 *
	 * @return bool True to skip this migration, false to run it
	 * @access public
	 */
	public function effectively_installed()
	{
		return !isset($this->config['content_forum_id']);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('primetime_content_forum_id', (int) $this->config['content_forum_id']));
		);
	}
}
