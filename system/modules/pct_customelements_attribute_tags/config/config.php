<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2014, Premium Contao Webworks, Premium Contao Themes
 * @author		Tim Gatzky <info@tim-gatzky.de>
 * @package		pct_customelements_attribute_tags
 * @link		http://contao.org
 * @license     LGPL
 */


/**
 * Constants
 */
define(PCT_CUSTOMELEMENTS_TAGS_PATH, 'system/modules/pct_customelements_attribute_tags');
define(PCT_CUSTOMELEMENTS_TAGS_VERSION, '1.4.11');

/**
 * Back end modules
 */
array_insert($GLOBALS['BE_MOD']['content'], count($GLOBALS['BE_MOD']['content']), array
(
	'pct_customelements_tags' => array
	(
		'tables' 		=> array('tl_pct_customelement_tags'),
	)
));

if(version_compare(VERSION, '3.5','<='))
{
	$GLOBALS['BE_MOD']['content']['pct_customelements_tags']['icon'] = PCT_CUSTOMELEMENTS_TAGS_PATH.'/assets/img/tags_mod.png';
}

/**
 * Register attribute
 */
$GLOBALS['PCT_CUSTOMELEMENTS']['ATTRIBUTES']['tags'] = array
(
	'label'		=> &$GLOBALS['TL_LANG']['PCT_CUSTOMELEMENTS']['ATTRIBUTES']['tags'],
	'path' 		=> PCT_CUSTOMELEMENTS_TAGS_PATH,
	'class'		=> 'PCT\CustomElements\Attributes\Tags',
	'icon'		=> 'fa fa-tags'
);

/**
 * Register filter
 */
$GLOBALS['PCT_CUSTOMELEMENTS']['FILTERS']['tags'] = array
(
	'label'		=> &$GLOBALS['TL_LANG']['PCT_CUSTOMELEMENTS']['FILTERS']['tags'],
	'path' 		=> PCT_CUSTOMELEMENTS_TAGS_PATH,
	'class'		=> 'PCT\CustomElements\Filters\Tags',
	'icon'		=> 'fa fa-tags'
);

/**
 * Hooks
 */
if(!$GLOBALS['TL_CONFIG']['bypassCache'])
{
	// workaround a contao bug with the internal cache and set the module icon via css
	$GLOBALS['TL_HOOKS']['loadDataContainer'][] 				= array('PCT\CustomElements\Backend\TableCustomElementTags','loadAssets');
}
$GLOBALS['CUSTOMCATALOG_HOOKS']['prepareField'][] 			= array('PCT\CustomElements\Attributes\Tags','prepareField');
$GLOBALS['CUSTOMELEMENTS_HOOKS']['processWildcardValue'][] 	= array('PCT\CustomElements\Attributes\Tags','processWildcardValue');
$GLOBALS['CUSTOMELEMENTS_HOOKS']['getExportChain'][] 		= array('PCT\CustomElements\Attributes\Tags\Export','addToExport');