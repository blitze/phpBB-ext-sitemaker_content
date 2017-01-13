<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services;

/**
 * Class template_loader
 *
 * @package blitze\content\services
 */
class template_loader implements \Twig_LoaderInterface, \Twig_ExistsLoaderInterface
{
	/* @var array */
	protected $blocks_data = array();

	/* @var array */
	protected $content_types = array();

	/**
	 * Constructor
	 *
	 * @param \blitze\content\services\types				$content			Content types object
	 * @param \blitze\sitemaker\model\mapper_factory		$mapper_factory		Mapper factory object
	 */
	public function __construct(\blitze\content\services\types $content, \blitze\sitemaker\model\mapper_factory $mapper_factory)
	{
		$types = $content->get_all_types();
		foreach ($types as $type => $entity)
		{
			$this->content_types[$type] = array(
				'summary_tpl'	=> $entity->get_summary_tpl(),
				'detail_tpl'	=> $entity->get_detail_tpl(),
				'last_modified'	=> $entity->get_last_modified(),
			);
		}

		$block_mapper = $mapper_factory->create('blocks', 'blocks');
		$collection = $block_mapper->find(array('name', '=', 'blitze.content.block.recent'));

		foreach ($collection as $entity)
		{
			$settings = $entity->get_settings();
			$this->blocks_data[$entity->get_bid()] = array(
				'block_tpl'		=> htmlspecialchars_decode($settings['block_tpl']),
				'last_modified'	=> $settings['last_modified'],
			);
		}
	}

	public function setPaths()
	{
		// do nothing
	}

	/**
	 * @param string $name
	 * @return mixed
	 * @throws \Twig_Error_Loader
	 */
	public function getSource($name)
	{
		if (false === $source = $this->getValue('source', $name))
		{
			throw new \Twig_Error_Loader(sprintf('Template "%s" does not exist.', $name));
		}

		return $source;
	}

	/**
	 * Twig_SourceContextLoaderInterface as of Twig 1.27
	 * @param string $name
	 * @return mixed
	 * @throws \Twig_Error_Loader
	 *
	public function getSourceContext($name)
	{
		if (false === $source = $this->getValue('source', $name))
		{
			throw new Twig_Error_Loader(sprintf('Template "%s" does not exist.', $name));
		}

		return new \Twig_Source($source, $name);
	}
	*/

	/**
	 * Twig_ExistsLoaderInterface as of Twig 1.11
	 * @param string $name
	 * @return bool
	 */
	public function exists($name)
	{
		return $name === $this->getValue('name', $name);
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function getCacheKey($name)
	{
		return $name;
	}

	/**
	 * @param string $name
	 * @param int $time
	 * @return bool
	 */
	public function isFresh($name, $time)
	{
		if (false === $last_modified = $this->getValue('last_modified', $name))
		{
			return false;
		}

		return $last_modified <= $time;
	}

	/**
	 * @param string $what
	 * @param string $name
	 * @return mixed
	 */
	protected function getValue($what, $name)
	{
		preg_match('/(.*)_(summary|detail|block)$/is', $name, $match);
		list(, $id, $view) = $match;

		if ($view === 'block')
		{
			$data = $this->blocks_data[$id];
			$column_name = 'block_tpl';
		}
		else
		{
			$data = $this->content_types[$id];
			$column_name = $view . '_tpl';
		}

		$return = array(
			'name'			=> $name,
			'source'		=> $data[$column_name],
			'last_modified'	=> $data['last_modified'],
		);

		return $return[$what];
	}
}
