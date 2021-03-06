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

function crpTag_user_newtag()
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_COMMENT))
	{
		// no permission but the module can continue his process
		return false;
	}

	$modvars = pnModGetVar('crpTag');
	$tagString = null;
	$tagNameArray = array ();

	if ($modvars['tag_use_preset'] && !empty ($modvars['tag_enabled_preset']))
	{
		$tagString = $modvars['tag_enabled_preset'];
		$tagNameArray = explode(',', $modvars['tag_enabled_preset']);
	}

	$tag = new crpTag();
	return $tag->ui->newItemTags($tagString, $modvars, $tagNameArray);
}

/**
 * update item
 *
 * @return string HTML output
 */
function crpTag_user_edittag()
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_COMMENT))
	{
		// no permission but the module can continue his process
		return false;
	}

	$tagmodule = FormUtil :: getPassedValue('tagmodule', null, 'POST');
	$objectid = FormUtil :: getPassedValue('id_module', null, 'POST');
	$taglist = FormUtil :: getPassedValue('taglist', null, 'POST');
	$returnurl = FormUtil :: getPassedValue('returnurl', null, 'POST');

	if (!$objectid || !$tagmodule)
	{
		$dom = ZLanguage::getModuleDomain('crpTag');
		LogUtil :: registerError(__('Error! Could not do what you wanted. Please check your input.', $dom));
	}

	$tag = new crpTag();
	$tag->updateTag($objectid, array (
		'module' => $tagmodule,
		'returnurl' => $returnurl
	), $taglist);
	pnRedirect($returnurl);
	pnShutDown();
}

function crpTag_user_modifytag($args = array ())
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_COMMENT))
	{
		// no permission but the module can continue his process
		return false;
	}
	if (!$args['objectid'] || !$args['extrainfo']['module'])
	{
		$dom = ZLanguage::getModuleDomain('crpTag');
		LogUtil :: registerError(__('Error! Could not do what you wanted. Please check your input.', $dom));
	}

	$tagArray = pnModAPIFunc('crpTag', 'user', 'gettags', array (
		'id_module' => $args['objectid'],
		'tagmodule' => $args['extrainfo']['module'],
		'extended' => false
	));
	foreach ($tagArray as $vTag)
		$tagNameArray[] = $vTag['name'];

	$tagString = implode(',', $tagNameArray);

	$modvars = pnModGetVar('crpTag');

	$tag = new crpTag();
	return $tag->ui->modifyItemTags($tagString, $modvars, $tagNameArray);
}

function crpTag_user_embedtag($args = array ())
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_READ))
	{
		return LogUtil :: registerPermissionError();
	}

	$modvars = pnModGetVar('crpTag');
	$tagArray = pnModAPIFunc('crpTag', 'user', 'gettags', array (
		'id_module' => $args['objectid'],
		'tagmodule' => $args['extrainfo']['module'],
		'extended' => false
	));

	$tag = new crpTag();

	if (empty ($tagArray))
	{
		// add tags
		if ($modvars['tag_edit_inline'] && SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_MODERATE))
		{
			$can_add = true;
			return $tag->ui->displayAddItemTags($args['objectid'], $args['extrainfo']['module'], $modvars, $args['extrainfo']['returnurl'], $can_add);
		}
		else
			return;
	}
	else
	{
		foreach ($tagArray as $vTag)
			$tagNameArray[] = $vTag['name'];

		$tagString = implode(',', $tagNameArray);

		// edit, copy, delete
		if ($modvars['tag_edit_inline'] && SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_MODERATE))
			$can_edit = true;

		return $tag->ui->displayItemTags($tagArray, $tagString, $tagNameArray, $modvars, $args['extrainfo']['returnurl'], $can_edit);
	}
}

/**
 * display item
 *
 * @return string html string
 */
function crpTag_user_display($args)
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_READ))
	{
		return LogUtil :: registerPermissionError();
	}

	$startnum = (int) FormUtil :: getPassedValue('startnum', isset ($args['startnum']) ? $args['startnum'] : 0, 'GET');
	$id_tag = FormUtil :: getPassedValue('id', isset ($args['id']) ? $args['id'] : null, 'REQUEST');
	$tagmodule = FormUtil :: getPassedValue('tagmodule', isset ($args['tagmodule']) ? $args['tagmodule'] : null, 'REQUEST');
	$objectid = FormUtil :: getPassedValue('objectid', isset ($args['objectid']) ? $args['objectid'] : null, 'REQUEST');

	// defaults and input validation
	if (!is_numeric($startnum) || $startnum < 0)
		$startnum = 1;
	if (!empty ($objectid))
		$id_tag = $objectid;

	// get all module vars for later use
	$modvars = pnModGetVar('crpTag');
	$tagArray = pnModAPIFunc('crpTag', 'user', 'gettags', array (
		'id_tag' => $id_tag,
		'tagmodule' => $tagmodule,
		'extended' => true,
		'startnum' => $startnum,
		'numitems' => $modvars['tag_itemsperpage']
	));

	foreach ($tagArray as $ktag => $vtag)
	{
		$item = pnModAPIFunc($vtag['module'], 'user', 'get', array (
			$vtag['mapid'] => $vtag['id_module']
		));
		if (SecurityUtil :: checkPermission("$vtag[module]::", "::", ACCESS_READ))
		{
			$tagArray[$ktag]['item'] = $item;
		}
	}

	$pager = array (
		'numitems' => pnModAPIFunc('crpTag', 'user', 'countitems', array (
			'id_tag' => $id_tag,
			'tagmodule' => $tagmodule
		)),
		'itemsperpage' => $modvars['tag_itemsperpage']
	);

	$tag = new crpTag();
	return $tag->ui->displayTaggedItems($tagArray, $modvars, $pager, $id_tag, $tagmodule);
}

function crpTag_user_main()
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_READ))
	{
		return LogUtil :: registerPermissionError();
	}

	$startnum = (int) FormUtil :: getPassedValue('startnum', isset ($args['startnum']) ? $args['startnum'] : 0, 'GET');
	$objectid = FormUtil :: getPassedValue('objectid', isset ($args['objectid']) ? $args['objectid'] : null, 'REQUEST');

	// defaults and input validation
	if (!is_numeric($startnum) || $startnum < 0)
		$startnum = 1;
	if (!empty ($objectid))
		$id_tag = $objectid;

	// get all module vars for later use
	$modvars = pnModGetVar('crpTag');
	$tagArray = pnModAPIFunc('crpTag', 'user', 'gettags', array (
		'extended' => true,
		'startnum' => $startnum,
		'numitems' => $modvars['tag_itemsperpage']
	));

	foreach ($tagArray as $ktag => $vtag)
	{
		$item = pnModAPIFunc($vtag['module'], 'user', 'get', array (
			$vtag['mapid'] => $vtag['id_module']
		));
		if (SecurityUtil :: checkPermission("$vtag[module]::", "::", ACCESS_READ))
		{
			switch ($vtag['module'])
			{
				case "News" :
					$fromstamp = ($item['from']) ? DateUtil :: makeTimestamp($item['from']) : null;
					$tostamp = ($item['to']) ? DateUtil :: makeTimestamp($item['to']) : null;
					if (((!$fromstamp && !$tostamp) || ($fromstamp && !$tostamp && $fromstamp < time()) || ($tostamp && !$fromstamp && $tostamp > time()) || ($fromstamp && $tostamp && $fromstamp < time() && $tostamp > time())) && ($item['published_status'] == '0'))
						$tagArray[$ktag]['item'] = $item;
					else
						unset ($tagArray[$ktag]);
					break;
				case "crpCalendar" :
				case "crpVideo" :
				case "Pages" :
				case "Ephemerids" :
				case "FAQ" :
				case "locations" :
				case "Feeds" :
				case "Reviews" :
					if ($item['obj_status'] == 'A')
						$tagArray[$ktag]['item'] = $item;
					else
						unset ($tagArray[$ktag]);
					break;
				default :
					$tagArray[$ktag]['item'] = $item;
					break;
			}
		}
	}

	$pager = array (
		'numitems' => pnModAPIFunc('crpTag', 'user', 'countitems'),
		'itemsperpage' => $modvars['tag_itemsperpage']
	);

	$tag = new crpTag();
	return $tag->ui->displayMain($tagArray, $modvars, $pager);
}

/**
 * display tags by user
 *
 * @return string html string
 */
function crpTag_user_usertags($args)
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_READ))
	{
		return LogUtil :: registerPermissionError();
	}

	$startnum = (int) FormUtil :: getPassedValue('startnum', isset ($args['startnum']) ? $args['startnum'] : 0, 'GET');
	$uid = FormUtil :: getPassedValue('uid', isset ($args['uid']) ? $args['uid'] : null, 'REQUEST');
	$objectid = FormUtil :: getPassedValue('objectid', isset ($args['objectid']) ? $args['objectid'] : null, 'REQUEST');

	// defaults and input validation
	if (!is_numeric($startnum) || $startnum < 0)
		$startnum = 1;
	if (!empty ($objectid))
		$uid = $objectid;

	// get all module vars for later use
	$modvars = pnModGetVar('crpTag');
	$tagArray = pnModAPIFunc('crpTag', 'user', 'gettags', array (
		'uid' => $uid,
		'extended' => true,
		'startnum' => $startnum,
		'numitems' => $modvars['tag_itemsperpage']
	));

	foreach ($tagArray as $ktag => $vtag)
	{
		$item = pnModAPIFunc($vtag['module'], 'user', 'get', array (
			$vtag['mapid'] => $vtag['id_module']
		));
		if (SecurityUtil :: checkPermission("$vtag[module]::", "::", ACCESS_READ))
		{
			$tagArray[$ktag]['item'] = $item;
		}
	}

	$pager = array (
		'numitems' => pnModAPIFunc('crpTag', 'user', 'countitems', array (
			'uid' => $uid
		)),
		'itemsperpage' => $modvars['tag_itemsperpage']
	);

	$tag = new crpTag();
	return $tag->ui->displayMyTaggedItems($tagArray, $modvars, $pager, $uid);
}