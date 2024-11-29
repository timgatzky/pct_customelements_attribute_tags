<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * @copyright 	Tim Gatzky 2016
 * @author  	Tim Gatzky <info@tim-gatzky.de>
 * @package  	pct_customelements
 * @subpackage 	pct_customelements_attribute_tags
 */

/**
 * Namespace
 */
namespace PCT\CustomElements\Models;

/**
 * Class
 * TagsModel
 *
 * @method static \PCT_TagsModel|null findById($val, $opt=array())
 * @method static \PCT_TagsModel|null findByPid($val, $opt=array())
 * @method static \PCT_TagsModel|null findMultipleByIds($val, $opt=array())
 */
class TagsModel extends \Contao\Model
{
	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_pct_customelement_tags';
	
	
	/**
	 * Find the translated value by an ID and optional by the language code
	 * @param integer
	 * @param string
	 * @return string
	 */
	public static function findTranslationById($intId, $strLanguage='')
	{
		if($intId < 1)
		{
			return '';
		}
		
		$objModel = self::findByPk($intId);
		if($objModel === null)
		{
			return '';
		}
		
		$arrTranslations = \Contao\StringUtil::deserialize($objModel->translations);
		
		if(strlen($strLanguage) < 1)
		{
			$strLanguage = str_replace('-', '_', $GLOBALS['TL_LANGUAGE']);
		}
		
		return $arrTranslations[$strLanguage]['label'] ?: '';
	}
}