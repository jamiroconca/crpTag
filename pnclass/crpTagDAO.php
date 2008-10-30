<?php

/**
 * crpTag
 *
 * @copyright (c) 2008 Daniele Conca
 * @link http://code.zikula.org/crptag Support and documentation
 * @author Daniele Conca <conca.daniele@gmail.com>
 * @license GNU/GPL - v.2.1
 * @package crpTag
 */

/**
 * crpTag Object
 */
class crpTagDAO
{

	function crpTagDAO()
	{

	}

	/**
	 * Check for tag existence
	 */
	function existTag($tagname = null)
	{
		$pntable = pnDBGetTables();
		$tagcolumn = $pntable['crptag_column'];

		$tagId = DBUtil :: selectField('crptag', 'id', "WHERE $tagcolumn[name]='" . DataUtil :: formatForStore($tagname) . "'");
		return $tagId;
	}

	/**
	 * Tag creation
	 */
	function createTag($inputValues = array ())
	{
		$object = DBUtil :: insertObject($inputValues, 'crptag', 'id');
		if (!$object)
		{
			LogUtil :: registerError(_CREATEFAILED);
			return false;
		}
		return $object['id'];
	}

	/**
	 * Archive creation
	 */
	function createArchive($inputValues = array ())
	{
		return DBUtil :: insertObject($inputValues, 'crptag_archive');
	}

	/**
	 * Archive clean up
	 */
	function cleanArchive($id_tag = null, $id_module = null, $module = null)
	{
		$pntable = pnDBGetTables();
		$archivecolumn = $pntable['crptag_archive_column'];

		if ($id_tag)
		{
			$queryargs[] = "($archivecolumn[id_tag]='" . DataUtil :: formatForStore($id_tag) . "')";
		}
		if ($id_module)
		{
			$queryargs[] = "($archivecolumn[id_module]='" . DataUtil :: formatForStore($id_module) . "')";
		}
		if ($module)
		{
			$queryargs[] = "($archivecolumn[module]='" . DataUtil :: formatForStore($module) . "')";
		}

		$where = null;
		if (count($queryargs) > 0)
		{
			$where = ' WHERE ' . implode(' AND ', $queryargs);
		}

		return DBUtil :: deleteObject(null, 'crptag_archive', $where);
	}

	/**
	 * Return list by parameters
	 */
	function getTags($id_tag = null, $id_module = null, $module = null, $extended = null, $startnum = 1, $numitems = null,
										$groupbyname=null, $uid=null, $interval=null)
	{
		(empty ($startnum)) ? $startnum = 1 : '';
		(empty ($numitems)) ? $numitems = pnModGetVar('crpTag', 'tag_itemsperpage') : '';

		if (!is_numeric($startnum) || !is_numeric($numitems))
		{
			return LogUtil :: registerError(_MODARGSERROR);
		}

		$pntable = pnDBGetTables();
		$tagcolumn = $pntable['crptag_column'];
		$archivecolumn = $pntable['crptag_archive_column'];
		$queryargs = array ();

		if ($id_tag)
			$queryargs[] = "($archivecolumn[id_tag]='" . DataUtil :: formatForStore($id_tag) . "')";

		if ($id_module)
			$queryargs[] = "($archivecolumn[id_module]='" . DataUtil :: formatForStore($id_module) . "')";

		if ($module)
			$queryargs[] = "($archivecolumn[module]='" . DataUtil :: formatForStore($module) . "')";

		if ($uid)
			$queryargs[] = "($pntable[crptag_archive].$archivecolumn[cr_uid]='" . DataUtil :: formatForStore($uid) . "')";

		if ($interval)
		{
			$queryargs[]= "(($pntable[crptag_archive].$archivecolumn[cr_date] < NOW() " .
			"AND $pntable[crptag_archive].$archivecolumn[cr_date] > DATE_SUB(NOW(), INTERVAL " . DataUtil :: formatForStore($interval) . " DAY))) ";
		}

		$queryargs[] = "($archivecolumn[id_module] IS NOT NULL)";

		$groupby = "$pntable[crptag_archive].$archivecolumn[id_tag], $pntable[crptag_archive].$archivecolumn[id_module], $pntable[crptag_archive].$archivecolumn[module]";
		if ($groupbyname)
			$groupby = "$pntable[crptag].$tagcolumn[name]";


		$where = null;
		if (count($queryargs) > 0)
		{
			$where = ' WHERE ' . implode(' AND ', $queryargs);
		}

		$sqlStatement = "SELECT $pntable[crptag_archive].$archivecolumn[id_tag] as id, " .
		"$pntable[crptag].$tagcolumn[name] as name, " .
		"$pntable[crptag_archive].$archivecolumn[id_module] as id_module, " .
		"$pntable[crptag_archive].$archivecolumn[module] as module, " .
		"COUNT($pntable[crptag_archive].$archivecolumn[id_tag]) as tagcounter " .
		"FROM $pntable[crptag] " .
		"LEFT JOIN $pntable[crptag_archive] ON ($pntable[crptag].$tagcolumn[id]=$pntable[crptag_archive].$archivecolumn[id_tag]) " .
		"$where " .
		"GROUP BY $groupby ORDER BY $pntable[crptag_archive].$tagcolumn[lu_date] DESC";

		// get the objects from the db
		$res = DBUtil :: executeSQL($sqlStatement, $startnum -1, $numitems, true, true);

		$objArray = DBUtil :: marshallObjects($res, array (
			'id',
			'name',
			'id_module',
			'module',
			'tagcounter'
		), true);

		// Check for an error with the database code, and if so set an appropriate
		// error message and return
		if ($objArray === false)
		{
			return LogUtil :: registerError(_GETFAILED);
		}

		foreach ($objArray as $kobj => $vobj)
		{
			if ($extended)
			{
				$objArray[$kobj]['func'] = crpTag ::mapModuleMeta($vobj['module'],'displayfunc');
				$objArray[$kobj]['mapid'] = crpTag ::mapModuleMeta($vobj['module'],'itemid');
				$moduleId = pnModGetIDFromName($vobj['module']);
				$moduleInfo = pnModGetInfo($moduleId);
				$objArray[$kobj]['modname'] = $moduleInfo['displayname'];
			}
			$objArray[$kobj]['avg'] = $this->tagAVG($vobj['id']);
		}

		// Return the items
		return $objArray;
	}

	/**
	 * Return items count
	 *
	 * @return int on success
	 */
	function countItems($id_tag = null, $id_module = null, $module = null, $uid = null)
	{
		$pntable = pnDBGetTables();
		$tagcolumn = $pntable['crptag_column'];
		$archivecolumn = $pntable['crptag_archive_column'];
		$queryargs = array ();

		if ($id_tag)
		{
			$queryargs[] = "($archivecolumn[id_tag]='" . DataUtil :: formatForStore($id_tag) . "')";
		}
		if ($id_module)
		{
			$queryargs[] = "($archivecolumn[id_module]='" . DataUtil :: formatForStore($id_module) . "')";
		}
		if ($module)
		{
			$queryargs[] = "($archivecolumn[module]='" . DataUtil :: formatForStore($module) . "')";
		}
		if ($uid)
		{
			$queryargs[] = "($archivecolumn[cr_uid]='" . DataUtil :: formatForStore($uid) . "')";
		}

		$queryargs[] = "($archivecolumn[id_module] IS NOT NULL)";

		$where = null;
		if (count($queryargs) > 0)
		{
			$where = ' WHERE ' . implode(' AND ', $queryargs);
		}

		if ($id_tag)
			return DBUtil :: selectObjectCountByID('crptag_archive', $id_tag, 'id_tag');
		elseif
			($module) return DBUtil :: selectObjectCountByID('crptag_archive', $module, 'module');
		elseif
			($uid) return DBUtil :: selectObjectCountByID('crptag_archive', $uid, 'cr_uid');
		else
			return DBUtil :: selectObjectCount('crptag_archive', $where, 'id_tag');

	}

	/**
	 * Calculate tag average value
	 */
	function tagAVG($id_tag=null)
	{
		// start counter from zero
		$tag_counter = DBUtil :: selectObjectCountByID('crptag_archive', $id_tag, 'id_tag')-1;
		$tot_counter = DBUtil :: selectObjectCount('crptag_archive');

		$tag_avg = ($tag_counter * 100 ) / $tot_counter;

		return $tag_avg;
	}

	/**
	 * Purge unused tags from db
	 */
	function tagPurge()
	{
		$pntable = pnDBGetTables();
		$tagcolumn = $pntable['crptag_column'];
		$archivecolumn = $pntable['crptag_archive_column'];

		$sqlStatement = "DELETE $pntable[crptag]
			FROM $pntable[crptag]
			LEFT JOIN $pntable[crptag_archive] ON ($tagcolumn[id] = $archivecolumn[id_tag])
			WHERE $archivecolumn[id_tag] IS NULL";

		return DBUtil :: executeSQL($sqlStatement);
	}

	/**
	 * Return list by parameters
	 */
	function formList($id_tag = null, $id_module = null, $module = null, $startnum = 1, $numitems = null,
										$groupbyname=null, $uid=null, $alias=null)
	{
		(empty ($startnum)) ? $startnum = 1 : '';
		(empty ($numitems)) ? $numitems = pnModGetVar('crpTag', 'tag_itemsperpage') : '';

		if (!is_numeric($startnum) || !is_numeric($numitems))
		{
			return LogUtil :: registerError(_MODARGSERROR);
		}

		$pntable = pnDBGetTables();
		$tagcolumn = $pntable['crptag_column'];
		$archivecolumn = $pntable['crptag_archive_column'];
		$queryargs = array ();

		if ($id_tag)
			$queryargs[] = "($archivecolumn[id_tag]='" . DataUtil :: formatForStore($id_tag) . "')";

		if ($id_module)
			$queryargs[] = "($archivecolumn[id_module]='" . DataUtil :: formatForStore($id_module) . "')";

		if ($module)
			$queryargs[] = "($archivecolumn[module]='" . DataUtil :: formatForStore($module) . "')";

		if ($uid)
			$queryargs[] = "($pntable[crptag_archive].$archivecolumn[cr_uid]='" . DataUtil :: formatForStore($uid) . "')";

		$queryargs[] = "($archivecolumn[id_module] IS NOT NULL)";

		$groupby = "$pntable[crptag_archive].$archivecolumn[id_tag], $pntable[crptag_archive].$archivecolumn[id_module], $pntable[crptag_archive].$archivecolumn[module]";
		if ($groupbyname)
			$groupby = "$pntable[crptag].$tagcolumn[name]";


		$where = null;
		if (count($queryargs) > 0)
		{
			$where = ' WHERE ' . implode(' AND ', $queryargs);
		}

		$sqlStatement = "SELECT $pntable[crptag_archive].$archivecolumn[id_tag] as id, " .
		"$pntable[crptag].$tagcolumn[name] as name " .
		"FROM $pntable[crptag] " .
		"LEFT JOIN $pntable[crptag_archive] ON ($pntable[crptag].$tagcolumn[id]=$pntable[crptag_archive].$archivecolumn[id_tag]) " .
		"$where " .
		"GROUP BY $groupby ORDER BY $pntable[crptag_archive].$tagcolumn[lu_date] DESC";

		// get the objects from the db
		$res = DBUtil :: executeSQL($sqlStatement, $startnum -1, $numitems, true, true);

		$objArray = DBUtil :: marshallObjects($res, array (
			'id',
			'name'
		), true);

		if ($alias)
		{
			foreach ($objArray as $kobj => $vobj)
			{
				$objArray[$kobj]['caption'] = $vobj['name'];
				$objArray[$kobj]['value'] = $vobj['name'];
				unset($objArray[$kobj]['name']);
				unset($objArray[$kobj]['id']);
			}
		}

		// Check for an error with the database code, and if so set an appropriate
		// error message and return
		if ($objArray === false)
		{
			return LogUtil :: registerError(_GETFAILED);
		}

		// Return the items
		return $objArray;
	}

	/**
	 * get a specific admin lite data
	 *
	 * @param int $eventid item identifier
	 *
	 * @return string item value
	 */
	function isAuthor($id_module = null, $module = null)
	{
		$pntable= pnDBGetTables();
		$tagcolumn= $pntable['crptag_archive_column'];

		$queryargs[]= "($tagcolumn[id_module] = '" . DataUtil :: formatForStore($id_module) . "' " .
		"AND $tagcolumn[module] = '" . DataUtil :: formatForStore($module) . "' " .
		"AND $tagcolumn[cr_uid] = '" . DataUtil :: formatForStore(pnUserGetVar('uid')) . "')";

		$columnArray= array (
			'cr_uid'
		);

		$where= null;
		if (count($queryargs) > 0)
		{
			$where= ' WHERE ' . implode(' AND ', $queryargs);
		}

		$item= DBUtil :: selectObject('crptag_archive', $where, $columnArray);

		$author= false;
		($item['cr_uid']) ? $author= true : $author= false;

		return $author;
	}
}