<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\services\views;

class tiles extends view
{
	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \primetime\primetime\core\primetime */
	protected $primetime;

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
	 * @param \primetime\content\services\displayer		$displayer			Content displayer object
	 * @param \primetime\primetime\core\primetime		$primetime			Primetime object
	 * @param string									$root_path			Path to the phpbb includes directory.
	 * @param string									$php_ext			php file extension
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\db $config, \phpbb\db\driver\driver_interface $db, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\user $user, \primetime\content\services\displayer $displayer, \primetime\primetime\core\primetime $primetime, $root_path, $php_ext)
	{
		parent::__construct($auth, $cache, $config, $db, $template, $user, $displayer, $root_path, $php_ext);

		$this->primetime = $primetime;
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

		$asset_path = $this->primetime->asset_path;
		$this->primetime->add_assets(array(
			'js'   => array(
				$asset_path . 'ext/primetime/content/assets/vendor/imagesloaded/imagesloaded.pkgd.min.js',
				$asset_path . 'ext/primetime/content/assets/vendor/masonry/dist/masonry.pkgd.min.js',
				$asset_path . 'ext/primetime/content/assets/scripts/tiles.min.js',
			)
		));
	}
}
