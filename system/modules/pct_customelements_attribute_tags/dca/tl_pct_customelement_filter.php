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
 * Table tl_pct_customelement_filter
 */
$objDcaHelper = \PCT\CustomElements\Helper\DcaHelper::getInstance()->setTable('tl_pct_customelement_filter');
$strType = 'tags';

/**
 * Palettes
 */
$arrPalettes = $objDcaHelper->getPalettesAsArray('default');
$arrPalettes['settings_legend'][] = 'attr_id'; 
$arrPalettes['settings_legend'][] = 'label';
$arrPalettes['settings_legend'][] = 'includeReset';
array_insert($arrPalettes['expert_legend'],1,array('conditional'));
$GLOBALS['TL_DCA']['tl_pct_customelement_filter']['palettes'][$strType] = $objDcaHelper->generatePalettes($arrPalettes);

if($objDcaHelper->getActiveRecord()->type == $strType)
{
	if(\Input::get('act') == 'edit' && \Input::get('table') == $objDcaHelper->getTable())
	{
		// Show template info
		\Message::addInfo(sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['templateInfo_filter'], 'customcatalog_filter_tags'));
	}
	
	$GLOBALS['TL_DCA']['tl_pct_customelement_filter']['fields']['attr_id']['options_values'] = array('tags');
	$GLOBALS['TL_DCA']['tl_pct_customelement_filter']['fields']['template']['default'] = 'customcatalog_filter_tags';
}