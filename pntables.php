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
 * crpTag table information
 */
function crpTag_pntables()
{
	// Initialise table array
	$pntable = array ();

	// Full table definition
	$pntable['crptag'] = DBUtil :: getLimitedTablename('crptag');
	$pntable['crptag_column'] = array (
		'id' => 'id',
		'name' => 'name'
	);
	$pntable['crptag_column_def'] = array (
		'id' => 'I(11) AUTOINCREMENT PRIMARY',
		'name' => "C(255) NOTNULL DEFAULT ''",
		
	);

	$pntable['crptag_archive'] = DBUtil :: getLimitedTablename('crptag_archive');
	$pntable['crptag_archive_column'] = array (
		'id_tag' => 'id_tag',
		'id_module' => 'id_module',
		'module' => 'module'
	);
	$pntable['crptag_archive_column_def'] = array (
		'id_tag' => "I(11) NOTNULL DEFAULT '0'",
		'id_module' => "I(11) NOTNULL DEFAULT '0'",
		'module' => "C(255) NOTNULL DEFAULT ''"
	);
	
	// add standard data fields
  ObjectUtil::addStandardFieldsToTableDefinition ($pntable['crptag_column'], '');
  ObjectUtil::addStandardFieldsToTableDataDefinition($pntable['crptag_column_def']);
  
  ObjectUtil::addStandardFieldsToTableDefinition ($pntable['crptag_archive_column'], '');
  ObjectUtil::addStandardFieldsToTableDataDefinition($pntable['crptag_archive_column_def']);

	return $pntable;
}
