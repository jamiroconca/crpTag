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

Loader :: includeOnce('modules/crpTag/pnclass/crpTag.php');

function crpTag_userapi_createtag($args = array ())
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_COMMENT))
	{
		// no permission but the module can continue his process
		return false;
	}

	if (!$args['objectid'] || !$args['extrainfo']['module'])
	{
		$dom = ZLanguage :: getModuleDomain('crpTag');
		LogUtil :: registerError(__('Error! Could not do what you wanted. Please check your input.', $dom));
	}

	$taglist = FormUtil :: getPassedValue('taglist', null, 'POST');
	$tag = new crpTag();

	return $tag->insertTag($args['objectid'], $args['extrainfo'], $taglist);
}

function crpTag_userapi_updatetag($args = array ())
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_COMMENT))
	{
		// no permission but the module can continue his process
		return false;
	}

	if (!$args['objectid'] || !$args['extrainfo']['module'])
	{
		$dom = ZLanguage :: getModuleDomain('crpTag');
		LogUtil :: registerError(__('Error! Could not do what you wanted. Please check your input.', $dom));
	}

	$taglist = FormUtil :: getPassedValue('taglist', null, 'POST');
	$tag = new crpTag();

	return $tag->updateTag($args['objectid'], $args['extrainfo'], $taglist);
}

function crpTag_userapi_gettags($args = array ())
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_READ))
	{
		return LogUtil :: registerPermissionError();
	}

	$tag = new crpTag();
	return $tag->dao->getTags($args['id_tag'], $args['id_module'], $args['tagmodule'], $args['extended'], $args['startnum'], $args['numitems'], $args['groupbyname'], $args['uid'], $args['interval']);
}

/**
 * utility function to count the number of items held by this module
 * @return integer number of items held by this module
 */
function crpTag_userapi_countitems($args)
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_READ))
	{
		return LogUtil :: registerPermissionError();
	}

	$tag = new crpTag();
	return $tag->dao->countItems($args['id_tag'], $args['id_module'], $args['tagmodule'], $args['uid']);
}

/**
 * retrieve list of events, filtered as specified, for form use
 *
 * @return array events
 */
function crpTag_userapi_getall_formlist($args = array ())
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_READ))
	{
		return LogUtil :: registerPermissionError();
	}

	$tag = new crpTag();
	return $tag->dao->formList($args['id_tag'], $args['id_module'], $args['tagmodule'], $args['startnum'], $args['numitems'], $args['groupbyname'], $args['uid'], $args['alias']);
}