<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2018 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\topic\driver;

interface block_interface
{
	/**
	 * Name of content topic block
	 * @return string
	 */
	public function get_name();

	/**
	 * Lang name of content topic block
	 * @return string
	 */
	public function get_langname();

	/**
	 * @param \blitze\content\model\entity\type $entity
	 * @param array $topic_data
	 * @param array $post_data
	 * @param array $user_cache
	 * @return void
	 */
	public function show_block(\blitze\content\model\entity\type $entity, array $topic_data, array $post_data, array $user_cache);
}
