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
define(PCT_CUSTOMELEMENTS_TAGS_VERSION, '1.4.2');

/**
 * Back end modules
 */
array_insert($GLOBALS['BE_MOD']['content'], count($GLOBALS['BE_MOD']['content']), array
(
	'pct_customelements_tags' => array
	(
		'tables' 		=> array('tl_pct_customelement_tags'),
		'icon'   		=> PCT_CUSTOMELEMENTS_TAGS_PATH.'/assets/img/tags_mod.png',
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
	'icon'		=> 'fa fa-tags'
);

/**
 * Hooks
 */
$GLOBALS['CUSTOMCATALOG_HOOKS']['prepareField'][] 		= array('PCT\CustomElements\Attributes\Tags','prepareField');
