<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\controller;

class display
{
	public function __construct(\phpbb\request\request_interface $request, \phpbb\user $user, \phpbb\template\template $template, \phpbb\db\driver\factory $db, \phpbb\controller\helper $helper)
	{
		$this->request = $request;
		$this->user = $user;
		$this->template = $template;
		$this->db = $db;
		$this->helper = $helper;
	}

	public function handle($page = '')
	{
		return $this->helper->render('content_body.html', 'Example extension bar() method');
	}
}
