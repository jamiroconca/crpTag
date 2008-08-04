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

function crpTag_init()
{
	// create table
	if (!DBUtil :: createTable('crptag'))
	{
		return false;
	}
	
	// create table
	if (!DBUtil :: createTable('crptag_archive'))
	{
		return false;
	}
	
	// Create the index
  if (!DBUtil :: createIndex('tag_module', 'crptag_archive', array('module')))
  	return false;
  	
  if (!DBUtil :: createIndex('tag_archive', 'crptag_archive', array('id_tag','id_module', 'module'), array('UNIQUE' => '1')))
  	return false;


	// Set default pages per page
	pnModSetVar('crpTag', 'tag_itemsperpage', 25);
	pnModSetVar('crpTag', 'tag_minlength', 4);

	// Set up module hooks for users
	// embed on creation form
	if (!pnModRegisterHook('item', 'new', 'GUI', 'crpTag', 'user', 'newtag'))
      return false;
	// embed on edit form
  if (!pnModRegisterHook('item', 'create', 'API', 'crpTag', 'user', 'createtag'))
      return false;
  // embed on edit form
  if (!pnModRegisterHook('item', 'modify', 'GUI', 'crpTag', 'user', 'modifytag'))
      return false;
	// embed on update
  if (!pnModRegisterHook('item', 'update', 'API', 'crpTag', 'user', 'updatetag'))
      return false;
  // display hooked tags
	if (!pnModRegisterHook('item', 'display', 'GUI', 'crpTag', 'user', 'embedtag'))
		return false;
	
  // remove embed on item deletion
	if (!pnModRegisterHook('item', 'delete', 'API', 'crpTag', 'admin', 'deletetag'))
		return false;
	
	// remove hook on module deletion
	if (!pnModRegisterHook('module', 'remove', 'API', 'crpTag', 'admin', 'removetag'))
		return false;

	// Initialisation successful
	return true;
}

function crpTag_upgrade($oldversion)
{
	$tables = pnDBGetTables();
	switch ($oldversion)
	{
		case "0.1.0" :
			break;
	}
	// Update successful
	return true;
}

function crpTag_delete()
{
	// Remove module hooks for users
	// embed on creation form
	if (!pnModUnRegisterHook('item', 'new', 'GUI', 'crpTag', 'user', 'newtag'))
      return false;
	// embed on edit form
  if (!pnModUnRegisterHook('item', 'create', 'API', 'crpTag', 'user', 'createtag'))
      return false;
  // embed on edit form
  if (!pnModUnRegisterHook('item', 'modify', 'GUI', 'crpTag', 'user', 'modifytag'))
      return false;
	// embed on update
  if (!pnModUnRegisterHook('item', 'update', 'API', 'crpTag', 'user', 'updatetag'))
      return false;
  // display hooked video
	if (!pnModUnRegisterHook('item', 'display', 'GUI', 'crpTag', 'user', 'embedtag'))
		return false;
	
	// Set up module hooks for admins
  // remove embed on item deletion
	if (!pnModUnRegisterHook('item', 'delete', 'API', 'crpTag', 'admin', 'deletetag'))
		return false;
				
	// remove hook on module deletion
	if (!pnModUnRegisterHook('module', 'remove', 'API', 'crpTag', 'admin', 'removetag'))
		return false;

	// drop table
	if (!DBUtil :: dropTable('crptag'))
	{
		return false;
	}

	if (!DBUtil :: dropTable('crptag_archive'))
	{
		return false;
	}

	// Delete any module variables
	pnModDelVar('crpTag');

	// Deletion successful
	return true;
}
