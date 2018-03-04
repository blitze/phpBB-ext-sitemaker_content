<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\controller;

class admin_controller
{
	/** @var \phpbb\language\language */
	protected $language;

	/** @var \blitze\content\services\actions\action_handler */
	protected $action_handler;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language							$language			Language object
	 * @param \blitze\content\services\actions\action_handler	$action_handler		Handles actions
	*/
	public function __construct(\phpbb\language\language $language, \blitze\content\services\actions\action_handler $action_handler)
	{
		$this->language = $language;
		$this->action_handler = $action_handler;
	}

	/**
	 * Handle admin actions
	 *
	 * @param string $action
	 * @param string $type
	 * @param string $base_url
	 * @return void
	 */
	public function handle($action, $type, $base_url)
	{
		$this->language->add_lang('admin', 'blitze/content');

		try
		{
			$command = $this->action_handler->create('type', $action);
			$command->execute($base_url, $type);
		}
		catch (\blitze\sitemaker\exception\base $e)
		{
			$message = $e->get_message($this->language);
			trigger_error($this->language->lang($message) . adm_back_link($base_url), E_USER_WARNING);
		}
	}
}
