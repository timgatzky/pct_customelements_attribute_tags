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
$arrPalettes['settings_legend'][] = 'mode';
$arrPalettes['settings_legend'][] = 'includeReset';
\Contao\ArrayUtil::arrayInsert( $arrPalettes, 3, array('conditions_legend' => array('conditional')) );
$GLOBALS['TL_DCA']['tl_pct_customelement_filter']['palettes'][$strType] = $objDcaHelper->generatePalettes($arrPalettes);

$objActiveRecord = $objDcaHelper->getActiveRecord();
if( $objActiveRecord !== null && $objActiveRecord->type == $strType )
{
	if(\Contao\Input::get('act') == 'edit' && \Contao\Input::get('table') == $objDcaHelper->getTable())
	{
		// Show template info
		\Contao\Message::addInfo(sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['templateInfo_filter'], 'customcatalog_filter_tags'));
	}
	
	$GLOBALS['TL_DCA']['tl_pct_customelement_filter']['fields']['attr_id']['options_values'] = array('tags');
	$GLOBALS['TL_DCA']['tl_pct_customelement_filter']['fields']['template']['default'] = 'customcatalog_filter_tags';
	
	$GLOBALS['TL_DCA']['tl_pct_customelement_filter']['fields']['mode']['inputType'] = 'select';
	$GLOBALS['TL_DCA']['tl_pct_customelement_filter']['fields']['mode']['options'] = array('exact');
	$GLOBALS['TL_DCA']['tl_pct_customelement_filter']['fields']['mode']['eval'] = array('tl_class'=>'clr','includeBlankOption' => true);
	$GLOBALS['TL_DCA']['tl_pct_customelement_filter']['fields']['mode']['reference'] = $GLOBALS['TL_LANG']['tl_pct_customelement_filter']['mode']['tags'];
}