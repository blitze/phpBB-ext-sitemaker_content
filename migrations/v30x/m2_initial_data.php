<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\migrations\v30x;

/**
 * Initial schema changes needed for Extension installation
 */
class m2_initial_data extends \phpbb\db\migration\container_aware_migration
{
	/**
	 * @inheritdoc
	 */
	static public function depends_on()
	{
		return array(
			'\blitze\content\migrations\v30x\m1_initial_schema',
			'\blitze\content\migrations\converter\c1_update_config',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'create_forum'))),
			array('config.add', array('blitze_content_forum_id', 0)),
		);
	}

	/**
	 *
	 */
	public function create_forum()
	{
		$forum = $this->container->get('blitze.sitemaker.forum.manager');

		$forum_data = array(
			'forum_type'	=> FORUM_CAT,
			'forum_name'	=> 'Sitemaker Content',
		);

		if (!empty($this->config['blitze_content_forum_id']))
		{
			$forum_data['forum_id'] = (int) $this->config['blitze_content_forum_id'];
		}

		$errors = $forum->add($forum_data);

		if (!sizeof($errors))
		{
			$forum_id = (int) $forum_data['forum_id'];
			$this->config->set('blitze_content_forum_id', $forum_id);
		}
	}
}
