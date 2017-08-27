<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\acp;

class content_info
{
	public function module()
	{
		return array(
			'filename'	=> '\blitze\content\acp\content_module',
			'title'		=> 'ACP_SITEMAKER',
			'modes'		=> array(
				'content'		=> array('title' => 'CONTENT_TYPES', 'auth' => 'ext_blitze/content', 'before' => 'ACP_MENU', 'cat' => array('ACP_SITEMAKER')),
			),
		);
	}
}
