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
 * Search plugin info
 **/
function crpTag_searchapi_info()
{
	return array ('title' => 'crpTag',
								'functions' => array ('crpTag' => 'search'));
}

/**
 * Search form component
 **/
function crpTag_searchapi_options($args)
{
	if (SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_READ))
	{
		// Create output object - this object will store all of our output so that
		// we can return it easily when required
		$pnRender= pnRender :: getInstance('crpTag');
		$pnRender->assign('active', (isset ($args['active']) && isset ($args['active']['crpTag'])) || (!isset ($args['crpTag'])));
		return $pnRender->fetch('crptag_search_options.htm');
	}
	return '';

}

/**
 * Search plugin main function
 **/
function crpTag_searchapi_search($args)
{
	pnModDBInfoLoad('Search');
	$pntable= pnDBGetTables();
	$tagtable= $pntable['crptag'];
	$tagcolumn= $pntable['crptag_column'];
	$archivetable= $pntable['crptag_archive'];
	$archivecolumn= $pntable['crptag_archive_column'];
	$searchTable= $pntable['search_result'];
	$searchColumn= $pntable['search_result_column'];

	$where= search_construct_where($args, array (
		$tagcolumn['name']
	), null);

	$sessionId= session_id();

	$sql= "SELECT $pntable[crptag].$tagcolumn[id] as id, " .
		"$pntable[crptag].$tagcolumn[name] as name, " .
		"$pntable[crptag_archive].$archivecolumn[id_module] as id_module, " .
		"$pntable[crptag_archive].$archivecolumn[module] as module, " .
		"$pntable[crptag_archive].$archivecolumn[cr_date] as cr_date " .
		"FROM $pntable[crptag] " .
		"LEFT JOIN $pntable[crptag_archive] ON ($pntable[crptag].$tagcolumn[id]=$pntable[crptag_archive].$archivecolumn[id_tag]) " .
		"WHERE $where";

	$result= DBUtil :: executeSQL($sql);
	if (!$result)
	{
		return LogUtil :: registerError(_GETFAILED);
	}

	$insertSql= "INSERT INTO $searchTable
	  ($searchColumn[title],
	   $searchColumn[text],
	   $searchColumn[extra],
	   $searchColumn[created],
	   $searchColumn[module],
	   $searchColumn[session])
	VALUES ";

	// Process the result set and insert into search result table
	for (; !$result->EOF; $result->MoveNext())
	{
		$item= $result->GetRowAssoc(2);
		if (SecurityUtil :: checkPermission('crpTag::', "::", ACCESS_READ))
		{
			$sql= $insertSql . '(' . '\'' . DataUtil :: formatForStore($item['name']) . '\', \'' . DataUtil :: formatForStore($item['module']) . '\', ' . '\'' . DataUtil :: formatForStore($item['id']) . '\', ' . '\'' . DataUtil :: formatForStore($item['cr_date']) . '\', ' . '\'' . 'crpTag' . '\', ' . '\'' . DataUtil :: formatForStore($sessionId) . '\')';
			$insertResult= DBUtil :: executeSQL($sql);
			if (!$insertResult)
			{
				return LogUtil :: registerError(_GETFAILED);
			}
		}
	}

	return true;
}

/**
 * Do last minute access checking and assign URL to items
 *
 * Access checking is ignored since access check has
 * already been done. But we do add a URL to the found item
 */
function crpTag_searchapi_search_check(& $args)
{
	$datarow= & $args['datarow'];
	$tagId= $datarow['extra'];

	$datarow['url']= pnModUrl('crpTag', 'user', 'display', array (
		'id' => $tagId
	));

	return true;
}