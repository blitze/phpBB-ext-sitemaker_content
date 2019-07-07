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
	/* @var \phpbb\user */
	protected $user;

	/* @var array */
	protected $required_extensions = [
		'blitze/sitemaker' => 'https://www.phpbb.com/customise/db/extension/phpbb_sitemaker_2'
	];

	/**
	 * Check whether or not the extension can be enabled.
	 *
	 * @return bool
	 */
	public function is_enableable()
	{
		$this->user = $this->container->get('user');
		$this->user->add_lang_ext('blitze/content', 'ext');

		$lang = $this->user->lang;
		$metadata = $this->get_metadata('blitze/content');

		$is_enableable = $this->phpbb_version_is_ok($lang, $metadata) && $this->required_exts_are_ok($lang, $metadata);

		$this->user->lang = $lang;
		return $is_enableable;
	}

	/**
	 * Check whether or not this extension can be enabled on the current phpBB version.
	 *
	 * @param array $lang
	 * @param array $metadata
	 * @return bool
	 */
	protected function phpbb_version_is_ok(array &$lang, array $metadata)
	{
		$req_phpbb_version = $metadata['extra']['soft-require']['phpbb/phpbb'];
		$phpbb_version = $this->container->get('config')['version'];

		if (!$this->version_is_ok($phpbb_version, $req_phpbb_version))
		{
			$lang['EXTENSION_NOT_ENABLEABLE'] .= '<br>' . $this->user->lang('PHPBB_VERSION_UNMET', $req_phpbb_version);
			return false;
		}

		return true;
	}

	/**
	 * Check whether or not the extension's phpBB extension dependencies are available and installed.
	 *
	 * @param array $lang
	 * @param array $metadata
	 * @return bool
	 */
	protected function required_exts_are_ok(array &$lang, array $metadata)
	{
		foreach ($this->required_extensions as $extension => $download_url)
		{
			if (!$this->container->get('ext.manager')->is_available($extension))
			{
				$lang['EXTENSION_NOT_ENABLEABLE'] .= '<br>' . $this->user->lang('MISSING_REQUIRED_EXTENSION', $extension, $download_url);
				return false;
			}

			$required_version = $metadata['require'][$extension];
			$current_version = $this->get_metadata($extension)['version'];

			if (!$this->version_is_ok($current_version, $required_version))
			{
				$lang['EXTENSION_NOT_ENABLEABLE'] .= '<br>' . $this->user->lang('EXTENSION_VERSION_UNMET', $required_version, $download_url, $extension, $current_version);
				return false;
			}

			if (!$this->container->get('ext.manager')->is_enabled($extension))
			{
				$this->container->get('ext.manager')->enable($extension);
			}
		}

		return true;
	}

	/**
	 * @param string $current_version
	 * @param string $required_version
	 * @return bool
	 */
	protected function version_is_ok($current_version, &$required_version)
	{
		$required_version = html_entity_decode($required_version);
		list($min_req_version, $max_req_version) = explode(',', $required_version);

		$constraint = $this->get_version_constraint($min_req_version);
		if (!phpbb_version_compare($current_version, $constraint['version'], $constraint['operator']))
		{
			return false;
		}

		$constraint = $this->get_version_constraint($max_req_version);
		if ($constraint['version'] && !phpbb_version_compare($current_version, $constraint['version'], $constraint['operator']))
		{
			return false;
		}

		return true;
	}

	/**
	 * @param string $version
	 * @return array
	 */
	protected function get_version_constraint($version)
	{
		$operator = '';
		if (preg_match('/^(\D+)(.+)/i', trim($version), $matches))
		{
			list(, $operator, $version) = $matches;
		}

		return [
			'version' => str_replace('@', '', $version),
			'operator' => $operator ?: '>=',
		];
	}

	/**
	 * Get composer metadata information
	 *
	 * @param string $name
	 * @return array
	 */
	protected function get_metadata($name)
	{
		$ext_manager = $this->container->get('ext.manager');
		$metadata_manager = $ext_manager->create_extension_metadata_manager($name);
		return $metadata_manager->get_metadata('all');
	}
}
