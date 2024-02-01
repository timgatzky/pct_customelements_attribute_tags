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

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\System;
use Contao\Environment;
use Contao\ArrayUtil;

/**
 * Constants
 */
define('PCT_CUSTOMELEMENTS_TAGS_PATH', 'system/modules/pct_customelements_attribute_tags');
define('PCT_CUSTOMELEMENTS_TAGS_VERSION', '1.16.0');

if( version_compare(ContaoCoreBundle::getVersion(),'5.0','>=') )
{
	$rootDir = System::getContainer()->getParameter('kernel.project_dir');
	include( $rootDir.'/system/modules/pct_customelements_attribute_tags/config/autoload.php' );
}


/**
 * Back end modules
 */
ArrayUtil::arrayInsert($GLOBALS['BE_MOD']['content'], count($GLOBALS['BE_MOD']['content']), array
(
	'pct_customelements_tags' => array
	(
		'tables' 		=> array('tl_pct_customelement_tags','tl_pct_customelement_attribute'),
		'icon' 			=> PCT_CUSTOMELEMENTS_TAGS_PATH.'/assets/img/tags_mod.png',
	)
));

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
	'icon'		=> 'fa fa-tags',
	'settings'	=> array('useIdsAsFilterValue'=>true)
);

/**
 * Register the model classes
 */
$GLOBALS['TL_MODELS']['tl_pct_customelement_tags'] = 'Contao\PCT_TagsModel';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['loadDataContainer'][] 				= array('PCT\CustomElements\Backend\TableCustomElementTags','loadAssets');
$GLOBALS['CUSTOMCATALOG_HOOKS']['prepareField'][] 			= array('PCT\CustomElements\Attributes\Tags','prepareField');
$GLOBALS['CUSTOMELEMENTS_HOOKS']['processWildcardValue'][] 	= array('PCT\CustomElements\Attributes\Tags','processWildcardValue');
$GLOBALS['CUSTOMELEMENTS_HOOKS']['getExportChain'][] 		= array('PCT\CustomElements\Attributes\Tags\Export','addToExport');
$GLOBALS['TL_CRON']['daily'][] 								= array('PCT\CustomElements\Backend\TableCustomElementTags','purgeRevisedRecords');