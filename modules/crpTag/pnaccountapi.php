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
 * Return an array of items to show in the your account panel
 *
 * @return   array   array of items, or false on failure
 */
function crpTag_accountapi_getall($args)
{
	if (!isset ($args['uname']))
	{
		if (!pnUserloggedIn())
		{
			$uname = null;
		}
		else
		{
			$uname = pnUserGetVar('uname');
		}
	}

	// Security check
	if (!SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_READ))
		$uname = null;

	// Create an array of links to return
	if ($uname != null)
	{
		$dom = ZLanguage :: getModuleDomain('crpTag');

		$items[] = array (
			'url' => pnModURL('crpTag', 'user', 'usertags', array (
				'uid' => pnUserGetVar('uid')
			)),
			'module' => 'crpTag',
			'set' => 'pnimages',
			'title' => __('My tags', $dom),
			'icon' => 'package_favourite.gif'
		);
	}
	else
		$items = null;

	// Return the items
	return $items;
}