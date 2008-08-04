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
 * Smarty function to display url of a tagged item
 *
 * Example
 * <!--[crptagbuildurl module=$tag.module func=$tag.func mapid=$tag.mapid id_module=$tag.id_module]-->
 * 
 * @return string the results of the module function
 */
function smarty_function_crptagbuildurl($params, & $smarty)
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpTag::', '::', ACCESS_READ))
	{
		return LogUtil :: registerPermissionError();
	}

	

	return pnModURL($params['module'],
					'user',
					$params['func'],
					array (
						"$params[mapid]" => $params['id_module']
					));
}
?>
