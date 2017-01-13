<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\template;

class twig extends \phpbb\template\twig\twig
{

	/**
	* Constructor.
	*
	* @param \phpbb\path_helper $path_helper
	* @param \phpbb\config\config $config
	* @param \phpbb\template\context $context template context
	* @param \phpbb\template\twig\environment $twig_environment
	* @param string $cache_path
	* @param \phpbb\user|null $user
	* @param array|\ArrayAccess $extensions
	* @param \phpbb\extension\manager $extension_manager extension manager, if null then template events will not be invoked
	*/
	public function __construct(\phpbb\path_helper $path_helper, $config, \phpbb\template\context $context, \phpbb\template\twig\environment $twig_environment, $cache_path, \phpbb\user $user = null, $extensions = array(), \phpbb\extension\manager $extension_manager = null)
	{
		$this->path_helper = $path_helper;
		$this->phpbb_root_path = $path_helper->get_phpbb_root_path();
		$this->php_ext = $path_helper->get_php_ext();
		$this->config = $config;
		$this->user = $user;
		$this->context = $context;
		$this->extension_manager = $extension_manager;
		$this->cachepath = $cache_path;
		$this->twig = $twig_environment;

		foreach ($extensions as $extension)
		{
			$this->twig->addExtension($extension);
		}

		// Add admin namespace
		if ($this->path_helper->get_adm_relative_path() !== null && is_dir($this->phpbb_root_path . $this->path_helper->get_adm_relative_path() . 'style/'))
		{
			$this->twig->getLoader()->setPaths($this->phpbb_root_path . $this->path_helper->get_adm_relative_path() . 'style/', 'admin');
		}
	}
}
