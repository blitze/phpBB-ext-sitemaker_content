<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\services\form\field;

class datetime extends duration
{
	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'datetime';
	}
}
