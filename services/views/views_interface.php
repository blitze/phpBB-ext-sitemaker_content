<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\views;

interface views_interface
{
	/**
	 * Short name of content view
	 */
	public function get_name();

	/**
	 * Lang name of content view
	 */
	public function get_langname();

	/**
	 * Template file of content index
	 */
	public function get_index_template();

	/**
	 * Template file of content details
	 */
	public function get_detail_template();

	/**
	 * Modify sql to retrieve topics
	 */
	public function customize_view(&$sql_topics_count, &$sql_topics_data, &$type_data, &$limit);

	/**
	 * Get topics count
	 */
	public function get_total_topics($forum_id, $sql_array);

	/**
	 * Display topics on content index
	 */
	public function display_topics($type, $topics_data, $posts_data, $users_cache, $attachments, $topic_tracking_info = array());

	/**
	 * Show topic details
	 */
	public function show_topic($type, $topic_title, $topic_data, $post_data, $users_cache, $attachments, $topic_tracking_info = array(), $page = 1);
}
