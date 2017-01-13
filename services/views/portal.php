<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\views;

class portal extends base_view
{
	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'portal';
	}

	/**
	 * @inheritdoc
	 */
	public function get_langname()
	{
		return 'CONTENT_DISPLAY_PORTAL';
	}

	/**
	 * @inheritdoc
	 */
	public function get_index_template()
	{
		return 'views/content_portal.html';
	}
}
