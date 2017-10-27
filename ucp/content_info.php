<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\ucp;

class content_info
{
	public function module()
	{
		return array(
			'filename'	=> '\blitze\content\ucp\content_module',
			'title'		=> 'UCP_SITEMAKER_CONTENT',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'content'		=> array('title' => 'MY_CONTENT', 'auth' => 'ext_blitze/content', 'cat' => array('UCP_SITEMAKER_CONTENT')),
			),
		);
	}
}
