<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\blocks;

use Solution10\Calendar\Calendar;
use Solution10\Calendar\Event;
use Solution10\Calendar\Resolution\MonthResolution;

class calendr extends \primetime\core\services\blocks\driver\block
{
	/** @var \phpbb\config\db */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/* @var \primetime\content\services\displayer */
	protected $displayer;

	/** @var \primetime\core\services\forum\query */
	protected $forum;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string phpEx */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\db							$config				Config object
	 * @param \phpbb\user								$user				User object
	 * @param \primetime\content\services\displayer		$displayer			Content displayer object
	 * @param \primetime\core\services\forum\query		$forum				Forum object
	 * @param string									$root_path			phpBB root path
	 * @param string									$php_ext			phpEx
	 */
	public function __construct(\phpbb\config\db $config, \phpbb\user $user, \primetime\content\services\displayer $displayer, \primetime\core\services\forum\query $forum, $phpbb_root_path, $php_ext)
	{
		$this->config = $config;
		$this->user = $user;
		$this->displayer = $displayer;
		$this->forum = $forum;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * 
	 */
	public function display($bdata, $edit_mode = false)
	{
		$time = $this->user->create_datetime();

		$calendar = new Calendar($time);
		$e = new Event('Standup and Finish very strong alltogether', new \DateTime('2014-10-2 10:00:00'), new \DateTime('2014-10-2 10:15:00'));
		$calendar->addEvent($e);
		$e = new Event('Finish', new \DateTime('2014-10-2 10:15:00'), new \DateTime('2014-10-2 10:15:00'));
		$calendar->addEvent($e);
		$calendar->setResolution(new MonthResolution());

		$viewData = $calendar->viewData();
		$month = array_shift($viewData['contents']);

		$this->ptemplate->assign_vars(array(
			'CAPTION'		=> $month->title('F Y'),
			'SHOW_OVERFLOW'	=> $calendar->resolution()->showOverflowDays(),
		));

		foreach ($month->weeks()[0]->days() as $day)
		{
			$this->ptemplate->assign_block_vars('wday', array(
				'DAY'	=> $day->date()->format('D'),
			));
		}

		foreach ($month->weeks() as $week)
		{
			$this->ptemplate->assign_block_vars('week', array());
			foreach ($week->days() as $day)
			{
				$this->ptemplate->assign_block_vars('week.day', array(
					'S_OVERFLOW'	=> $day->isOverflow(),
					'DAY_NUM'		=> $day->date()->format('j'),
					'DAY_DATE'		=> $day->date()->format('m/d/Y'),
					'EVENTS'		=> $calendar->eventsForTimeframe($day),
					'U_SEARCH'		=> append_sid("{$this->phpbb_root_path}search.$this->php_ext", "search")
				));
			}
		}

		return array(
			'title'		=> 'Calendar', //$this->user->lang[$lang_var],
			'content'	=> $this->ptemplate->render_view('primetime/content', 'blocks/content_calendar.html', 'content_calendar_block')
		);
	}
}
