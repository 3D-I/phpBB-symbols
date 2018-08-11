<?php
/**
 *
 * phpBB Symbols extension
 *
 * @copyright (c) 2018 v12mike
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace v12mike\symbols\event;

use phpbb\template\template;
use phpbb\user;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use v12mike\symbols\ext;

/**
 * Event listener
 */
class listener implements EventSubscriberInterface
{
	/** @var template */
	protected $template;

	/** @var string phpBB root path */
	protected $ext_root_path;

	/** @var user */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param template  	  $template
	 * @param string		  $ext_root_path
	 * @access public
	 */
	public function __construct(template $template, user $user, $ext_root_path)
	{
		$this->template = $template;
		$this->user = $user;
		$this->ext_root_path = $ext_root_path;
	}

	/**
	 * Assign functions defined in this class to event listeners in the core
	 *
	 * @return array
	 * @static
	 * @access public
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'core.user_setup'   					=> 'load_language_on_setup',
			'core.posting_modify_template_vars' 	=> 'posting_modify_template_vars',
			'core.ucp_pm_compose_modify_data'   	=> 'posting_modify_template_vars',
		);
	}

	/**
	 * Load common files during user setup
	 *
	 * @param \phpbb\event\data $event The event object
	 * @access public
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'v12mike/symbols',
			'lang_set' => 'symbols',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	* Fill symbols templates
	*/
	public function posting_modify_template_vars($event)
	{
		global $template;

		$symbols_groups_file_path = $phpbb_root_path . $this->ext_root_path . 'data/';

		/* read the csv file holding the symbol groups definitions */
		$groups = $groups_header = array();
		$file_handle = fopen($symbols_groups_file_path . 'symbol_groups.csv', 'r');
		if ($file_handle)
		{
			while (($row = fgetcsv($file_handle, 1024)) !== false) 
			{
				if (empty($groups_header))
				{
					$groups_header = $row;
				}
				else
				{
					$groups[] = array_combine($groups_header, $row);
				}
			}
			fclose($file_handle);

			/* iterate through the groups of symbols */
			foreach ($groups as $group)
			{
				$template->assign_block_vars('symbols_box', array(
					'SYMBOLS_TAB_ID'	 => $group['Identifier'],
					'SYMBOLS_TAB_NAME'	=> $this->user->lang[$group['Name']],
					'SYMBOLS_TAB_LABEL'	=> $group['Label'],
					)
				);
			}
            /* template flag to show that we at least found groups file */
			$template->assign_var('SYMBOLS_TABS', 1);
		}
	}
}
