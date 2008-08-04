<?php
/**
 * crpTag
 *
 * @copyright (c) 2007, Daniele Conca
 * @link http://code.zikula.org/crptag Support and documentation
 * @author Daniele Conca <conca.daniele@gmail.com>
 * @license GNU/GPL - v.2.1
 * @package crpTag
 */

/**
 * initialise block
 *
 */
function crpTag_tagsblock_init()
{
	// Security
	pnSecAddSchema('Tagsblock::', 'Block title::');
}

/**
 * get information on block
 * 
 */
function crpTag_tagsblock_info()
{
	return array (
		'text_type' => 'crpTags',
		'module' => 'crpTag',
		'text_type_long' => 'Collection of site tags',
		'allow_multiple' => true,
		'form_content' => false,
		'form_refresh' => false,
		'show_preview' => true
	);
}

/**
 * display block
 *
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the rendered bock
 */
function crpTag_tagsblock_display($blockinfo)
{
	// security check
	if (!SecurityUtil :: checkPermission('Tagsblock::', "$blockinfo[title]::", ACCESS_READ))
		return;

	if (!pnModAvailable('crpTag'))
		return;

	// get the current language
	$currentlang = pnUserGetLang();

	// Break out options from our content field
	$vars = pnBlockVarsFromContent($blockinfo['content']);
	// get all module vars for later use
	$modvars = pnModGetVar('crpVideo');

	$apiargs['startnum'] = 1;
	$apiargs['numitems'] = $modvars['tag_itemsperpage'];
	$apiargs['extended'] = true;
	$apiargs['groupbyname'] = true;

	// call the api
	$items = pnModAPIFunc('crpTag', 'user', 'gettags', $apiargs);

	// check for an empty return
	if (empty ($items))
		return;

	// create the output object
	$pnRender = pnRender :: getInstance('crpTag', false);

	$pnRender->assign('tags', $items);
	$pnRender->assign($modvars);
	
	$blockinfo['content'] = $pnRender->fetch('blocks/crptag_block_tags_cloud.htm');
		
	return pnBlockThemeBlock($blockinfo);
}

/**
 * modify block settings
 *
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the bock form
 */
function crpTag_tagsblock_modify($blockinfo)
{
	return;

}

/**
 * update block settings
 *
 * @param        array       $blockinfo     a blockinfo structure
 * @return       $blockinfo  the modified blockinfo structure
 */
function  crpTag_tagsblock_update($blockinfo)
{
	return;
}
?>