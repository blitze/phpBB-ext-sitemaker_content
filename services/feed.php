<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;

class feed
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\symfony_request */
	protected $symfony_request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config			$config					Config object
	 * @param \phpbb\controller\helper		$controller_helper		Controller Helper object
	 * @param \phpbb\symfony_request		$symfony_request		Symfony request
	 * @param \phpbb\template\template		$template				Template object
	 * @param \phpbb\user					$user					User object
	 * @param string						$php_ext				php file extension
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $controller_helper, \phpbb\symfony_request $symfony_request, \phpbb\template\template $template, \phpbb\user $user, $php_ext)
	{
		$this->config = $config;
		$this->controller_helper = $controller_helper;
		$this->symfony_request = $symfony_request;
		$this->template = $template;
		$this->user = $user;
		$this->php_ext = $php_ext;
	}

	/**
	 * @param int $max_update_time
	 * @return Response
	 */
	public function render($max_update_time)
	{
		$this->board_url = generate_board_url(true);

		$this->template->assign_vars(array(
			'BOARD_URL'				=> $this->board_url,
			'SELF_LINK'				=> $this->controller_helper->route($this->symfony_request->attributes->get('_route'), $this->symfony_request->attributes->get('_route_params'), true, '', UrlGeneratorInterface::ABSOLUTE_URL),
			'FEED_LINK'				=> $this->board_url . $this->user->page['script_path'] . 'index.' . $this->php_ext,
			'FEED_TITLE'			=> $this->config['sitename'],
			'FEED_SUBTITLE'			=> $this->config['site_desc'],
			'FEED_UPDATED'			=> $max_update_time,
			'FEED_LANG'				=> $this->user->lang['USER_LANG'],
			'FEED_AUTHOR'			=> $this->config['sitename'],
		));

		$this->template->set_filenames(array(
			'body'	=> 'feed.xml.twig',
		));

		return $this->get_response($this->template->assign_display('body'), $max_update_time);
	}

	/**
	 * @param string $content
	 * @param int $max_update_time
	 * @return string
	 */
	protected function get_response($content, $max_update_time)
	{
		$response = new Response($this->prepare_content($content));
		$response->headers->set('Content-Type', 'application/atom+xml');
		$response->setCharset('UTF-8');
		$response->setLastModified(new \DateTime('@' . $max_update_time));

		if (!empty($this->user->data['is_bot']))
		{
			// Let reverse proxies know we detected a bot.
			$response->headers->set('X-PHPBB-IS-BOT', 'yes');
		}

		return $response;
	}

	/**
	 * @param string $content
	 * @return string
	 */
	protected function prepare_content($content)
	{
		// convert relative urls to absolute urls
		$script_path = trim($this->user->page['script_path'], '/');
		$full_path = $this->board_url . '/' . $script_path;
		$content = preg_replace('/(href|src)=("|\')((?:\.*\/)+(?:' . $script_path . '\/)?)(.*?)("|\')/i', '$1=$2' . $full_path . '/$4$5', $content);

		// remove hidden field labels
		$content = preg_replace('#<div class="field-label label-hidden">(.*?)</div>#', '', $content);

		// remove session id
		$content = preg_replace('/((?:\?|&)sid=[a-z0-9]+)/', '', $content);

		// Remove Comments from inline attachments [ia]
		$content = preg_replace('#<dd>(.*?)</dd>#','',$content);

		// Replace some entities with their unicode counterpart
		$entities = array(
			'&nbsp;'	=> "\xC2\xA0",
			'&bull;'	=> "\xE2\x80\xA2",
			'&middot;'	=> "\xC2\xB7",
			'&copy;'	=> "\xC2\xA9",
		);

		$content = str_replace(array_keys($entities), array_values($entities), $content);

		return $content;
	}
}
