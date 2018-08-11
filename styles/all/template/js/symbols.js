/**
 *
 * Symbols extension
 *
 * @copyright (c) 2018 v12mike
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

$('a.symbol-tab').click(function(id){

	var data = $(this).attr('tab-link')
	var panel = $(this).attr('data-subpanel')

	if($('#'+panel).html()==""){
		$('#'+panel).load(data);
	}
})
