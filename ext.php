<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content;

class ext extends \phpbb\extension\base
{
	/**
	 * Check whether or not the extension can be enabled.
	 *
	 * @return bool
	 */
	public function is_enableable()
	{
		$this->container->get('language')->add_lang('info_acp_content', 'blitze/content');

		return $this->phpbb_version_is_ok() && $this->required_exts_are_ok();
	}

	/**
	 * Check whether or not this extension can be enabled on the current phpBB version.
	 *
	 * @return bool
	 */
	protected function phpbb_version_is_ok()
	{
		$config = $this->container->get('config');
		return phpbb_version_compare($config['version'], '3.2.2', '>=') && phpbb_version_compare($config['version'], '3.3.0', '<');
	}

	/**
	 * Check whether or not the extension's dependencies are available and installed.
	 *
	 * @return bool
	 */
	protected function required_exts_are_ok()
	{
		if (!class_exists('blitze\sitemaker\ext'))
		{
			trigger_error($this->container->get('language')->lang('MISSING_REQUIRED_EXTENSION'), E_USER_WARNING);
		}

		if (!$this->container->get('ext.manager')->is_enabled('blitze/sitemaker'))
		{
			$this->container->get('ext.manager')->enable('blitze/sitemaker');
		}

		return true;
	}
}
