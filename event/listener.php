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

	/**
	 * Constructor
	 *
	 * @param template        $template
	 * @param string          $ext_root_path
	 * @access public
	 */
	public function __construct(template $template, $ext_root_path)
	{
        $this->template = $template;
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
			'core.user_setup'							=> 'load_language_on_setup',
            'core.posting_modify_template_vars'         => 'posting_modify_template_vars',
            'core.ucp_pm_compose_modify_data'   => 'posting_modify_template_vars',
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
    * Fill smiley templates (or just the variables) with smilies, either in a window or inline
    */
    public function posting_modify_template_vars($event)
    {
    	global $user, $template;
    	global $phpEx, $phpbb_root_path;

        $symbols_found = 0;

    	$base_url = append_sid("{$phpbb_root_path}posting.$phpEx", 'mode=smilies&amp;f=' . $forum_id);
        $symbols_groups_file = $phpbb_root_path . '/ext/v12mike/symbols/data/';

    	/* read the csv file holding the symbol groups definitions */
    	$groups = $groups_header = array();
        $file_handle = fopen($symbols_groups_file . 'symbol_groups.csv', 'r');
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
    		$id = 0;
    		foreach ($groups as $group)
            {
                $template->assign_block_vars('symbols_box', array(
    				'SYMBOLS_TAB_ID'     => 'sym' . $id . '-panel-tab',
    				'SYMBOLS_TAB_PANEL'  => 'sym' . $id . '-panel',
                    'SYMBOLS_TAB_NAME'	=> $group['Name'],
                    'SYMBOLS_TAB_LABEL'	=> $group['Label'],
                    )
                );

                /* read the csv file holding the symbol definitions */
                $symbols = $symbols_header = array();
    			$group_filename = 
                $file_handle = fopen($symbols_groups_file . $group['File'], 'r');
                if ($file_handle)
                {
                    while (($row = fgetcsv($file_handle, 1024)) !== false) 
                    {
                        if (empty($symbols_header))
                        {
                            $symbols_header = $row;
                        }
                        else
                        {
                            $symbols[] = array_combine($symbols_header, $row);
                        }
                    }
                    fclose($file_handle);

                    foreach ($symbols as $symbol)
                    {
                        $template->assign_block_vars('symbols_box.symbols_table', array(
                            'SYMBOL_DESCRIPTION'	=> $symbol['Description'],
                            'SYMBOL_CODE'	=> $symbol['Alpha Code'],
                            )
                        );
    					$symbols_found++;
                    }
                }
    			$id++;
            }
        }
        $template->assign_var('SYMBOLS_TABS', $symbols_found);
    }
}
