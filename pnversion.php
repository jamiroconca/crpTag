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

$dom = ZLanguage::getModuleDomain('crpTag');

$modversion['name'] = __('crpTag', $dom);
$modversion['displayname'] = __('crpTag', $dom);
$modversion['description'] = __('Tagging module', $dom);
$modversion['version'] = '0.1.4';
$modversion['credits'] = 'pndocs/credits.txt';
$modversion['help'] = 'pndocs/install.txt';
$modversion['changelog'] = 'pndocs/changelog.txt';
$modversion['license'] = 'pndocs/license.txt';
$modversion['official'] = 1;
$modversion['author'] = 'Daniele Conca - jami';
$modversion['contact'] = 'conca.daniele@gmail.com';
$modversion['securityschema'] = array ('crpTag::' => '::');
