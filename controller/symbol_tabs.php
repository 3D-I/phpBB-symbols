<?php
/**
 *
 * phpBB Symbols extension
 *
 * @copyright (c) 2018 v12mike
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

use \Symfony\Component\HttpFoundation\Response;

namespace v12mike\symbols\controller;

class symbol_tabs
{
	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param \phpbb\controller\helper  $helper
	 * @param \phpbb\template\template  $template
	 * @param \phpbb\user				$user
	 */
	public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->helper   = $helper;
		$this->template = $template;
		$this->user 	= $user;
	}

	/**
	 * Demo controller for route /demo/{name}
	 *
	 * @param string $name
	 * @throws \phpbb\exception\http_exception
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function handle($tab_id)
	{
		global $user, $template;
		global $phpEx, $phpbb_root_path;

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
			foreach ($groups as $group)
			{
				/* only generate data for one tab */
				if ($tab_id == $group['Identifier'])
				{
					$this->template->assign_vars(array(
						'SYMBOLS_TAB_ID'	 => $group['Identifier'],
						'SYMBOLS_TAB_NAME'	=> $this->user->lang[$group['Name']],
						)
					);

					/* read the csv file holding the symbol definitions */
					$symbols = $symbols_header = array();
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
							$this->template->assign_block_vars('symbols_table', array(
								'SYMBOL_DESCRIPTION'	=> $this->user->lang[$symbol['Description']],
								'SYMBOL_CODE'	=> $symbol['Alpha Code'],
								)
							);
						}
					}
				}
			}
		}
		return $this->helper->render('symbols_tab.html', $name);
	}
}
