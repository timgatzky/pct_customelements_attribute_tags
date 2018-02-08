<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2014, Premium Contao Webworks, Premium Contao Themes
 * @author		Tim Gatzky <info@tim-gatzky.de>
 * @package		pct_customelements_attribute_tags
 * @subpackage	Tags
 * @link		http://contao.org
 * @license     LGPL
 */

/**
 * Imports
 */
use PCT\CustomElements\Helper\DcaHelper as DcaHelper;

/**
 * Table tl_pct_customelement_attribute
 */
$objDcaHelper = DcaHelper::getInstance()->setTable('tl_pct_customelement_attribute');

/**
 * Config
 */
$GLOBALS['TL_DCA']['tl_pct_customelement_attribute']['config']['onload_callback'][] = array('PCT\CustomElements\Attributes\Tags\TableCustomElementAttribute','setTabletreeOptions');

/**
 * Palettes
 */
$strType = 'tags';
$arrPalettes = $objDcaHelper->getPalettesAsArray('default');
$arrPalettes['settings_legend'] = array('tag_custom','tag_roots','eval_multiple','eval_mandatory','options');
$GLOBALS['TL_DCA']['tl_pct_customelement_attribute']['palettes'][$strType] = $objDcaHelper->generatePalettes($arrPalettes);

/**
 * Subpalettes
 */
$objDcaHelper->addSubpalette('tag_custom',array('tag_table','tag_key','tag_value','tag_sorting','tag_translations','tag_where'));

/**
 * Fields
 */
if($objDcaHelper->getActiveRecord()->type == $strType)
{
	$GLOBALS['TL_DCA']['tl_pct_customelement_attribute']['fields']['options'][$strType]['options'] = array
	(
		'sortable',
		'checkboxmenu'
	);
	$GLOBALS['TL_DCA']['tl_pct_customelement_attribute']['fields']['options'][$strType]['reference'] = &$GLOBALS['TL_LANG']['tl_pct_customelement_attribute']['options'][$strType];
}



$objDcaHelper->addFields(array
(
	'tag_roots' => array
	(
	    'label'  		=> &$GLOBALS['TL_LANG']['tl_pct_customelement_attribute']['tag_roots'],
	    'exclude'		=> true,
	    'inputType'		=> 'pct_tabletree',
	    'eval'			=> array('tl_class'=>'clr','tabletree'=>array('source'=>'tl_pct_customelement_tags','keyField'=>'id','valueField'=>'title'),'fieldType'=>'checkbox','multiple'=>true),
	    'sql'			=> "blob NULL"
	),
	'tag_custom' => array
	(
	    'label'  		=> &$GLOBALS['TL_LANG']['tl_pct_customelement_attribute']['tag_custom'],
	    'exclude'		=> true,
	    'inputType'		=> 'checkbox',
	    'eval'			=> array('tl_class'=>'','submitOnChange'=>true),
	    'sql'			=> "char(1) NOT NULL default ''"
	),
	'tag_table' => array
	(
	    'label'  		=> &$GLOBALS['TL_LANG']['tl_pct_customelement_attribute']['tag_table'],
	    'exclude'		=> true,
	    'inputType'		=> 'select',
	    'options_callback'	=> array('PCT\CustomElements\Attributes\Tags\TableHelper','getAllTables'),
	    'eval'			=> array('tl_class'=>'w50','chosen'=>true,'mandatory'=>true,'submitOnChange'=>true,'includeBlankOption'=>true,'decodeEntities'=>true),
	    'sql'			=> "varchar(128) NOT NULL default ''"
	),
	'tag_key' => array
	(
	    'label'  		=> &$GLOBALS['TL_LANG']['tl_pct_customelement_attribute']['tag_key'],
	    'exclude'		=> true,
	    'default'		=> 'id',
	    'inputType'		=> 'select',
	    'options_callback'	=> array('PCT\CustomElements\Attributes\Tags\TableHelper','getFields'),
	    'eval'			=> array('tl_class'=>'w50','chosen'=>true,'decodeEntities'=>true),
	    'sql'			=> "varchar(128) NOT NULL default ''"
	),
	'tag_value' => array
	(
	    'label'  		=> &$GLOBALS['TL_LANG']['tl_pct_customelement_attribute']['tag_value'],
	    'exclude'		=> true,
	    'inputType'		=> 'select',
	    'options_callback'	=> array('PCT\CustomElements\Attributes\Tags\TableHelper','getFields'),
	    'eval'			=> array('tl_class'=>'w50','chosen'=>true,'decodeEntities'=>true),
	    'sql'			=> "varchar(128) NOT NULL default ''"
	),
	'tag_sorting' => array
	(
	    'label'  		=> &$GLOBALS['TL_LANG']['tl_pct_customelement_attribute']['tag_sorting'],
	    'exclude'		=> true,
	    'inputType'		=> 'select',
	    'options_callback'	=> array('PCT\CustomElements\Attributes\Tags\TableHelper','getFields'),
	    'eval'			=> array('tl_class'=>'w50','chosen'=>true,'decodeEntities'=>true),
	    'sql'			=> "varchar(128) NOT NULL default ''"
	),
	'tag_where' => array
	(
	    'label'  		=> &$GLOBALS['TL_LANG']['tl_pct_customelement_attribute']['tag_where'],
	    'exclude'		=> true,
	    'inputType'		=> 'text',
		'eval'			=> array('tl_class'=>'clr long','decodeEntities'=>true),
	    'sql'			=> "varchar(255) NOT NULL default ''"
	),
	'tag_translations' => array
	(
	    'label'  		=> &$GLOBALS['TL_LANG']['tl_pct_customelement_attribute']['tag_translations'],
	    'exclude'		=> true,
	    'inputType'		=> 'select',
	    'options_callback'	=> array('PCT\CustomElements\Attributes\Tags\TableHelper','getFields'),
	    'eval'			=> array('tl_class'=>'w50','chosen'=>true,'decodeEntities'=>true,'includeBlankOption'=>true),
	    'sql'			=> "varchar(128) NOT NULL default ''"
	),
));

