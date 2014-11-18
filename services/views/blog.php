<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\services\views;

class blog extends view
{
	public function get_name()
	{
		return 'blog';
	}

	public function get_langname()
	{
		return 'BLOG';
	}

	public function get_index_template()
	{
		return 'content_blog.html';
	}
}
