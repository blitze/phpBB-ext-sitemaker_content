<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\comments;

interface comments_interface
{
	/**
	 * Lang name of comment type
	 * @return string
	 */
	public function get_langname();

	/**
	 * Get comments count for topic
	 * @param array $topic_data
	 * @return
	 */
	public function count(array $topic_data);

	/**
	 * Show comments for topic
	 *
	 * @param string $content_type
	 * @param array $topic_data
	 * @param array $update_count
	 * @return void
	 */
	public function show_comments($content_type, array $topic_data, array &$update_count);

	/**
	 * @param array $topic_data
	 * @return void
	 */
	public function show_form(array $topic_data);
}
