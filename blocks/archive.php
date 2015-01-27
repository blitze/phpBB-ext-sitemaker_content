<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\blocks;

class archive extends \primetime\core\services\blocks\driver\block
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\content_visibility */
	protected $content_visibility;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\user */
	protected $user;

	/* @var \primetime\content\services\types */
	protected $content_types;

	/** @var string */
	protected $phpbb_root_path;

	/* @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth						$auth					Auth object
	 * @param \phpbb\content_visibility				$content_visibility		Content visibility
	 * @param \phpbb\db\driver\driver_interface		$db						Database object
	 * @param \phpbb\controller\helper				$helper					Helper object
	 * @param \phpbb\user							$user					User object
	 * @param \primetime\content\services\types		$content_types			Content types object
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\content_visibility $content_visibility, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\user $user, \primetime\content\services\types $content_types, $phpbb_root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->content_visibility = $content_visibility;
		$this->db = $db;
		$this->helper = $helper;
		$this->user = $user;
		$this->content_types = $content_types;
		$this->root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Block config
	 */
	public function get_config($settings)
	{
		$content_types = $this->content_types->get_all_types();

		$content_type_options = array();
		foreach ($content_types as $type => $row)
		{
			$forum_id = $row['forum_id'];
			$content_type_options[$forum_id] = $row['content_langname'];
		}

		$forum_id	= (isset($settings['forum_id'])) ? $settings['forum_id'] : $forum_id;

		return array(
			'legend1'		=> $this->user->lang['SETTINGS'],
			'forum_id'		=> array('lang' => 'CONTENT_TYPE', 'validate' => 'string', 'type' => 'select', 'params' => array($content_type_options, $forum_id), 'default' => $forum_id, 'explain' => false),
		);
	}

	public function display($bdata, $edit_mode = false)
	{
		$settings = $bdata['settings'];

		if (empty($settings['forum_id']))
		{
			return array(
				'title'		=> '',
				'content'	=> ($edit_mode) ? $this->user->lang['NO_CONTENT_TYPE'] : '',
			);
		}

		$sql_array = array(
			'SELECT'	=> 'YEAR(FROM_UNIXTIME(t.topic_time)) AS year, MONTH(FROM_UNIXTIME(t.topic_time)) AS month, COUNT(t.topic_id) AS total',
			'FROM'		=> array(
				TOPICS_TABLE => 't',
			),
			'WHERE'		=> 't.forum_id = ' . (int) $settings['forum_id'] . '
				AND t.topic_time <= ' . time() . '
				AND ' . $this->content_visibility->get_global_visibility_sql('topic', array_keys($this->auth->acl_getf('!f_read', true)), 't.'),
			'GROUP_BY'	=> 'year, month',
			'ORDER_BY'	=> 'year DESC',
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		$year = '';
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($year != $row['year'])
			{
				$year = $row['year'];
				$this->ptemplate->assign_block_vars('year', array('YEAR' => $year));
			}

			$this->ptemplate->assign_block_vars('year.month', array(
				'ARCHIVE_MONTH'	=> $row['month'],
				'NUM_TOPICS'	=> $row['total'],
				'U_ARCHIVE'		=> '',
			));
		}
		$this->db->sql_freeresult($result);

		return array(
			'title'		=> 'ARCHIVE',
			'content'	=> 'Test',
		);
	}
}
