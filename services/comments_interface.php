<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services;

interface comments_interface
{
	/**
	 * Get comments count for topic
	 */
	public function count($topic_data);

	/**
	 * Show comments for topic
	 */
	public function show($content_type, $topic_data, $page);
}
