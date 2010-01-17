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

function crpTag_adminapi_deletetag($args=array())
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_COMMENT))
	{
		return LogUtil :: registerPermissionError();
	}

	if (!$args['objectid'] || !$args['extrainfo']['module'])
	{
		$dom = ZLanguage::getModuleDomain('crpTag');
		LogUtil :: registerError(__('Error! Could not do what you wanted. Please check your input.', $dom));
	}

	$tag = new crpTag();

	return $tag->deleteTag($args['objectid'],$args['extrainfo']);
}

function crpTag_adminapi_removetag($args=array())
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_COMMENT))
	{
		return LogUtil :: registerPermissionError();
	}

	if (!$args['extrainfo']['module'])
	{
		$dom = ZLanguage::getModuleDomain('crpTag');
		LogUtil :: registerError(__('Error! Could not do what you wanted. Please check your input.', $dom));
	}

	$tag = new crpTag();

	return $tag->removeTag($args['extrainfo']);
}
