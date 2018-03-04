<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\controller;

class mcp_controller
{
	/** @var \phpbb\language\language */
	protected $language;

	/** @var \blitze\content\services\action_handler */
	protected $action_handler;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language					$language			Language object
	 * @param \blitze\content\services\action_handler	$action_handler		Handles actions
	*/
	public function __construct(\phpbb\language\language $language, \blitze\content\services\action_handler $action_handler)
	{
		$this->language = $language;
		$this->action_handler = $action_handler;
	}

	/**
	 * Display list of topics for content type
	 *
	 * @param string $action
	 * @param string $base_url
	 * @return void
	 */
	public function handle($action, $base_url)
	{
		$this->language->add_lang('cp', 'blitze/content');

		try
		{
			$command = $this->action_handler->create('topic', $action);
			$command->execute($base_url, 'mcp');
		}
		catch (\blitze\sitemaker\exception\base $e)
		{
			$message = (array) $e->get_message($this->language);
			trigger_error(join('<br />', $message) . '<br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $base_url . '">', '</a>'));
		}
	}
}
