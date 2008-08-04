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

Loader :: includeOnce('modules/crpTag/pnclass/crpTag.php');

function crpTag_user_newtag()
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_COMMENT))
	{
		return LogUtil :: registerPermissionError();
	}

	$modvars = pnModGetVar('crpTag');

	$tag = new crpTag();
	return $tag->ui->newItemTags($modvars);
}

function crpTag_user_modifytag($args = array ())
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_COMMENT))
	{
		return LogUtil :: registerPermissionError();
	}
	if (!$args['objectid'] || !$args['extrainfo']['module'])
	{
		LogUtil :: registerError(_MODARGSERROR);
	}

	$tagArray = pnModAPIFunc('crpTag', 'user', 'gettags', array (
		'id_module' => $args['objectid'],
		'module' => $args['extrainfo']['module'],
		'extended' => false
	));
	foreach ($tagArray as $vTag)
		$tagNameArray[] = $vTag['name'];

	$tagString = implode(',', $tagNameArray);

	$modvars = pnModGetVar('crpTag');

	$tag = new crpTag();
	return $tag->ui->modifyItemTags($tagString, $modvars);
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
		'module' => $args['extrainfo']['module'],
		'extended' => false
	));

	if (empty ($tagArray))
		return;
	else
	{
		$tag = new crpTag();
		return $tag->ui->displayItemTags($tagArray, $modvars);
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
		'numitems' => pnModAPIFunc('crpTag',
		'user',
		'countitems',
		array (
			'id_tag' => $id_tag
		)
	), 'itemsperpage' => $modvars['tag_itemsperpage']);

	$tag = new crpTag();
	return $tag->ui->displayTaggedItems($tagArray, $modvars, $pager);
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
			$tagArray[$ktag]['item'] = $item;
		}
	}

	$pager = array (
		'numitems' => pnModAPIFunc('crpTag',
		'user',
		'countitems'
	), 'itemsperpage' => $modvars['tag_itemsperpage']);

	$tag = new crpTag();
	return $tag->ui->displayMain($tagArray, $modvars, $pager);
}