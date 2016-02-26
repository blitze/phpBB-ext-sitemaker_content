<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\views;

class tiles extends view
{
	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \blitze\sitemaker\services\util */
	protected $sitemaker;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth							$auth				Auth object
	 * @param \phpbb\cache\service						$cache				Cache object
	 * @param \phpbb\config\db							$config				Config object
	 * @param \phpbb\db\driver\driver_interface			$db					Database object
	 * @param \phpbb\request\request_interface			$request			Request object
	 * @param \phpbb\template\template					$template			Template object
	 * @param \phpbb\user								$user				User object
	 * @param \blitze\content\services\displayer		$displayer			Content displayer object
	 * @param \blitze\sitemaker\services\util				$sitemaker			Sitemaker object
	 * @param string									$root_path			Path to the phpbb includes directory.
	 * @param string									$php_ext			php file extension
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\db $config, \phpbb\db\driver\driver_interface $db, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\user $user, \blitze\content\services\displayer $displayer, \blitze\sitemaker\services\util $sitemaker, $root_path, $php_ext)
	{
		parent::__construct($auth, $cache, $config, $db, $template, $user, $displayer, $root_path, $php_ext);

		$this->sitemaker = $sitemaker;
		$this->request = $request;
	}

	public function get_name()
	{
		return 'tiles';
	}

	public function get_langname()
	{
		return 'CONTENT_DISPLAY_TILES';
	}

	public function get_index_template()
	{
		return 'content_tiles.html';
	}

	public function customize_view(&$sql_topics_count, &$sql_topics_data, &$type_data, &$limit)
	{
		if ($this->request->is_ajax())
		{
			$this->template->assign_var('S_HIDE_HEADERS', true);
		}

		$asset_path = $this->sitemaker->asset_path;
		$this->sitemaker->add_assets(array(
			'js'   => array(
				$asset_path . 'ext/blitze/content/components/imagesloaded/imagesloaded.pkgd.min.js',
				$asset_path . 'ext/blitze/content/components/masonry/dist/masonry.pkgd.min.js',
				'@blitze_content/assets/content_tiles.min.js',
			),
			'css'	=> array(
				'@blitze_content/assets/content_tiles.min.css',
			),
		));
	}
}
