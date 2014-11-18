<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\services;

class template_loader implements \Twig_LoaderInterface, \Twig_ExistsLoaderInterface
{
	/* @var array */
	protected $blocks_config = array();

	/* @var array */
	protected $content_types = array();

	/**
	 * Constructor
	 *
	 * @param \primetime\content\services\types		$content		Content types object
	 */
	public function __construct(\primetime\content\services\types $content)
	{
		global $phpbb_container;
		$blocks = $phpbb_container->get('primetime.blocks.display');

		$sql_where = "bvar = 'block_tpl' OR bvar = 'last_modified'";

		$types = $content->get_all_types();
		$config_ary = $blocks->get_blocks_config($sql_where);

		foreach ($types as $type => $row)
		{
			$this->content_types[$type] = array(
				'summary_tpl'	=> $row['summary_tpl'],
				'detail_tpl'	=> $row['detail_tpl'],
				'last_modified'	=> $row['last_modified'],
			);
		}

		foreach ($config_ary as $bid => $row)
		{
			$this->blocks_config[$bid] = array(
				'block_tpl'		=> htmlspecialchars_decode($row['block_tpl']),
				'last_modified'	=> $row['last_modified'],
			);
		}
		unset($types, $config_ary);
	}

	public function getSource($name)
	{
		if (false === $source = $this->getValue('source', $name)) {
			throw new Twig_Error_Loader(sprintf('Template "%s" does not exist.', $name));
		}

		return $source;
	}

	// Twig_ExistsLoaderInterface as of Twig 1.11
	public function exists($name)
	{
		return $name === $this->getValue('name', $name);
	}

	public function getCacheKey($name)
	{
		return $name;
	}

	public function isFresh($name, $time)
	{
		if (false === $lastModified = $this->getValue('modified', $name))
		{
			return false;
		}

		return $lastModified <= $time;
	}

	protected function getValue($what, $name)
	{
		preg_match('/(.*)_(summary|detail|block)$/is', $name, $match);
		list(, $id, $view) = $match;

		if ($view == 'block')
		{
			$data = $this->blocks_config[$id];
			$column_name = 'block_tpl';
		}
		else
		{
			$data = $this->content_types[$id];
			$column_name = $view . '_tpl';
		}

		$return = array(
			'name'		=> $name,
			'source'	=> $data[$column_name],
			'modified'	=> $data['last_modified'],
		);

		return $return[$what];
	}
}
