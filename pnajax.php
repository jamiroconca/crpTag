<?php

/**
 * crpTag
 *
 * @copyright (c) 2008-2010 Daniele Conca
 * @link http://code.zikula.org/crptag Support and documentation
 * @author Daniele Conca <conca.daniele@gmail.com>
 * @license GNU/GPL - v.2.1
 * @package crpTag
 */

/**
 * get form list of tags
 *
 * @return array events
 */
function crpTag_ajax_getTags()
{
	if (!SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_COMMENT))
	{
		$dom = ZLanguage :: getModuleDomain('crpTag');
		AjaxUtil :: error(pnVarPrepHTMLDisplay(__('Sorry! No authorization to access this module.', $dom)));
	}

	// get all module vars
	$modvars = pnModGetVar('crpTag');

	$startnum = '1';
	$numitems = '-1';
	$alias = DataUtil :: convertFromUTF8(FormUtil :: getPassedValue('alias', null, 'GET'));

	$data = compact('startnum', 'numitems', 'alias');

	$tags = pnModAPIFunc('crpTag', 'user', 'getall_formlist', $data);

	$resultlist = DataUtil :: convertFromUTF8($tags);
	return $resultlist;
}