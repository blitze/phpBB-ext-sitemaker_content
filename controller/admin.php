<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\pages\controller;

use Symfony\Component\HttpFoundation\Response;

class admin
{
	/** @var \phpbb\db\driver */
	protected $db;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	/** @var \primetime\category\core\builder */
	protected $tree;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface	$db				Database object
	* @param \phpbb\request\request_interface	$request 		Request object
	* @param \phpbb\user                		$user       	User object
	* @param \primetime\category\core\builder	$tree			Tree builder Object
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\request\request_interface $request, \phpbb\user $user, \primetime\pages\core\builder $tree)
	{
		$this->db = $db;
		$this->request = $request;
		$this->user = $user;
		$this->tree = $tree;
	}

	/**
	* Default controller method to be called if no other method is given.
	* In our case, it is accessed when the URL is /example
	*
	* @return Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function handle($action, $cat_id = 0)
	{
		$this->user->add_lang_ext('primetime/category', 'acp/info_acp_category');

		if ($this->request->is_ajax() === false)
		{
			$this->return_data['errors'] = $this->user->lang['NOT_AUTHORIZED'];
			return new Response(json_encode($this->return_data));
		}

		$errors = array();
		$return = array();

		switch ($action)
		{
			case 'save_tree':

				$raw_tree = $this->request->variable('tree', array(0 => array('' => 0)));

				$data = array();
				for ($i = 1, $size = sizeof($raw_tree); $i < $size; $i++)
				{
					$row = $raw_tree[$i];
					$data[$row['item_id']] = array(
						'cat_id'	=> (int) $row['item_id'],
						'parent_id' => (int) $row['parent_id'],
					);
				}

				$this->tree->update_tree($data);

			break;

			case 'get_item':

				$return = $this->tree->get_row($cat_id);

			break;

			case 'rebuild_tree':

				$this->tree->recalc_nestedset();

				// no break here

			case 'get_all_items':

				$sql = $this->tree->qet_tree_sql();
				$result = $this->db->sql_query($sql);

				$items = array();
				while ($row = $this->db->sql_fetchrow($result))
				{
					$items[] = $row;
				}
				$this->db->sql_freeresult($result);

				$return['items'] = $items;

			break;
		}

		$return['errors'] = join('<br />', $errors);

		$response = new Response(json_encode($return));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}
}
