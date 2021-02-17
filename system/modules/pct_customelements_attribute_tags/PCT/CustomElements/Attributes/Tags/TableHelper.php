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
 * Namespace
 */
namespace PCT\CustomElements\Attributes\Tags;

use Contao\StringUtil;
use Contao\System;

/**
 * Class file
 * TableHelper
 */
class TableHelper extends \Contao\Controller
{
	/**
	 * Return all contao tables as array
	 * @param object
	 */
	public function getAllTables($objDC)
	{
		return \Contao\Database::getInstance()->listTables();
	}
	
	
	/**
	 * Return all fields as array from the selected table
	 * @object
	 */
	public function getFields($objDC)
	{
		if(strlen($objDC->activeRecord->tag_table) < 1 || !\Contao\Database::getInstance()->tableExists($objDC->activeRecord->tag_table))
		{
			return array();
		}
		
		$fields = \Contao\Database::getInstance()->getFieldNames($objDC->activeRecord->tag_table);
		
		$fields = array_combine($fields,array_values($fields));
		unset($fields['PRIMARY']);
		
		return $fields;
	}
	
	
	/**
	 * Return all tags as array by a data container object
	 * @param object
	 *
	 * called from options_callback
	 */
	public function getTagsByDca($objDC)
	{
		$objAttribute = null;
		// is customcatalog
		if(\PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory::validateByTableName($objDC->table))
		{
			$objAttribute = \PCT\CustomElements\Plugins\CustomCatalog\Core\AttributeFactory::findByCustomCatalog($objDC->field,$objDC->table);
		}
		// is customelement
		else
		{
			$objAttribute = \PCT\CustomElements\Core\AttributeFactory::findByUuid($objDC->field);
		}
		
		if($objAttribute === null)
		{
			return array();
		}
		return $objAttribute->getAllTags();
	}


	/**
	 * Store current values in session
	 * @param mixed
	 * @param object
	 * @return array
	 * called by save_callback
	 */
	public function storeTagsValues($varValues, $objDC)
	{
		$objSession = System::getContainer()->get('session');
		$strSession = 'tags_' . $objDC->table . '_' . $objDC->field.'_'.$objDC->id;
		$objSession->set($strSession, $varValues);
		return $varValues;
	}


	/**
	 * Merge with selected values
	 * @param mixed
	 * @param object
	 * @return array
	 * called by load_callback
	 */
	public function mergeTagsValues($varValues, $objDC)
	{
		$varValues = StringUtil::deserialize($varValues);
		$strSession = 'tags_' . $objDC->table . '_' . $objDC->field.'_'.$objDC->id;
		$objSession = System::getContainer()->get('session');
		if( $objSession->get($strSession) === null )
		{
			$objSession->set($strSession, $varValues);
		}
		
		$arrFromSession = StringUtil::deserialize($objSession->get($strSession)) ?: array();
		
		// merge with new values
		if (empty($varValues) === false) 
		{
			$varValues = array_merge($varValues, $arrFromSession);
		}
		
		return array_unique($varValues);
	}
}