<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2015 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\model;

use blitze\sitemaker\model\mapper_factory_interface;

class mapper_factory implements mapper_factory_interface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var array */
	protected $mapper_tables;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db			Database object
	 * @param array									$tables		Tables for data mapping
	 */
	public function  __construct(\phpbb\db\driver\driver_interface $db, array $tables)
	{
		$this->db = $db;
		$this->mapper_tables = array_shift($tables);
	}

	/**
	 * {@inheritdoc}
	 */
	public function create($type)
	{
		$mapper_class = 'blitze\\content\\model\\mapper\\' . $type;
		$collection = 'blitze\\content\\model\\collections\\' . $type;

		return new $mapper_class($this->db, new $collection, $this, $this->mapper_tables[$type]);
	}
}
