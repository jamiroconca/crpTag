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

Loader :: includeOnce('modules/crpTag/pnclass/crpTagUI.php');
Loader :: includeOnce('modules/crpTag/pnclass/crpTagDAO.php');

/**
 * crpTag Object
 */
class crpTag
{

	function crpTag()
	{
		$this->ui = new crpTagUI();
		$this->dao = new crpTagDAO();
	}

	/**
	 * Tags insertion, check for existence before
	 */
	function insertTag($objectid = null, $extrainfo = array (), $taglist = null)
	{
		$tagArray = explode(',', $taglist);
		$creatingTag = array ();

		foreach ($tagArray as $kTag => $vTag)
		{
			if ($idTag = $this->dao->existTag($vTag))
				$creatingTag[] = $idTag;
			elseif (!empty ($vTag) && strlen($vTag) >= pnModGetVar('crpTag', 'tag_minlength'))
			{
				$idCreated = $this->dao->createTag(array (
					'name' => $vTag,
					'cr_date' => date('Y-m-d H:i:S'),
					'cr_uid' => pnUserGetVar('uid'),
					'lu_date' => date('Y-m-d H:i:S'),
					'lu_uid' => pnUserGetVar('uid')
				));
				$creatingTag[] = $idCreated;
			}
		}

		$creatingTag = array_unique($creatingTag);
		foreach ($creatingTag as $vIdTag)
		{
			$this->dao->createArchive(array (
				'id_tag' => $vIdTag,
				'id_module' => $objectid,
				'module' => $extrainfo['module'],
				'cr_date' => date('Y-m-d H:i:S'),
				'cr_uid' => pnUserGetVar('uid'),
				'lu_date' => date('Y-m-d H:i:S'),
				'lu_uid' => pnUserGetVar('uid')
			));
		}

		return true;
	}

	/**
	 * Tags update, check for existence before
	 */
	function updateTag($objectid = null, $extrainfo = array (), $taglist = null)
	{
		$tagArray = explode(',', $taglist);
		$creatingTag = array ();

		foreach ($tagArray as $kTag => $vTag)
		{
			if ($idTag = $this->dao->existTag($vTag))
				$creatingTag[] = $idTag;
			elseif (!empty ($vTag) && strlen($vTag) >= pnModGetVar('crpTag', 'tag_minlength'))
			{
				$idCreated = $this->dao->createTag(array (
					'name' => $vTag,
					'lu_date' => date('Y-m-d H:i:S'),
					'lu_uid' => pnUserGetVar('uid')
				));
				$creatingTag[] = $idCreated;
			}
		}

		// clean from old values
		$this->dao->cleanArchive(null, $objectid, $extrainfo['module']);

		$creatingTag = array_unique($creatingTag);
		foreach ($creatingTag as $vIdTag)
		{
			$this->dao->createArchive(array (
				'id_tag' => $vIdTag,
				'id_module' => $objectid,
				'module' => $extrainfo['module'],
				'lu_date' => date('Y-m-d H:i:S'),
				'lu_uid' => pnUserGetVar('uid')
			));
		}

		return true;
	}

	/**
	 * Tags deletion for an item
	 */
	function deleteTag($objectid = null, $extrainfo = array ())
	{
		// clean from old values
		$this->dao->cleanArchive(null, $objectid, $extrainfo['module']);

		return true;
	}

	/**
	 * Tags deletion for a module
	 */
	function removeTag($extrainfo = array ())
	{
		// clean from old values
		$this->dao->cleanArchive(null, null, $extrainfo['module']);

		return true;
	}

	/**
	 * Modify module's configuration
	 */
	function modifyConfig()
	{
		// get all module vars
		$modvars = pnModGetVar('crpTag');

		return $this->ui->modifyConfig($modvars);
	}

	/**
	 * Update module's configuration
	 */
	function updateConfig()
	{
		// Confirm authorisation code
		if (!SecurityUtil :: confirmAuthKey())
			return LogUtil :: registerAuthidError(pnModURL('crpTag', 'admin', 'main'));

		// Update module variables
		$tag_itemsperpage = (int) FormUtil :: getPassedValue('tag_itemsperpage', 25, 'POST');
		$tag_minlength = (int) FormUtil :: getPassedValue('tag_minlength', 4, 'POST');
		if ($tag_itemsperpage < 1)
			$tag_itemsperpage = 25;
		if ($tag_minlength < 1)
			$tag_minlength = 4;

		pnModSetVar('crpTag', 'tag_itemsperpage', $tag_itemsperpage);
		pnModSetVar('crpTag', 'tag_minlength', $tag_minlength);

		// Let any other modules know that the modules configuration has been updated
		pnModCallHooks('module', 'updateconfig', 'crpTag', array (
			'module' => 'crpTag'
		));

		// the module configuration has been updated successfuly
		LogUtil :: registerStatus(_CONFIGUPDATED);

		return pnRedirect(pnModURL('crpTag', 'admin', 'main'));
	}

	/**
	 * Map modules display functions
	 */
	function mapModuleDisplayFunc($tagmodule = null)
	{
		switch ($tagmodule)
		{
			case "News" :
			case "Reviews" :
			case "Pages" :
			case "crpCalendar" :
			case "crpVideo" :
			case "FAQ" :
			default :
				return "display";
				break;
		}
	}

	/**
	 * Map modules display functions
	 */
	function mapModuleId($tagmodule = null)
	{
		switch ($tagmodule)
		{
			case "Reviews" :
				return "id";
				break;
			case "Pages" :
				return "pageid";
				break;
			case "crpVideo" :
				return "videoid";
				break;
			case "crpCalendar" :
			case "News" :
			case "FAQ" :
			default :
				return "objectid";
				break;
		}
	}
}