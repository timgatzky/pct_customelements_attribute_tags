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

/**
 * Class file
 * TableHelper
 */
class TableHelper extends \Controller
{
	/**
	 * Return all contao tables as array
	 * @param object
	 */
	public function getAllTables($objDC)
	{
		return \Database::getInstance()->listTables();
	}
	
	
	/**
	 * Return all fields as array from the selected table
	 * @object
	 */
	public function getFields($objDC)
	{
		if(strlen($objDC->activeRecord->tag_table) < 1 || !\Database::getInstance()->tableExists($objDC->activeRecord->tag_table))
		{
			return array();
		}
		
		$fields = \Database::getInstance()->getFieldNames($objDC->activeRecord->tag_table);
		
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
		
		$objOrigin = new \PCT\CustomElements\Core\Origin;
		$objOrigin->set('pid',$objDC->id);
		$objOrigin->set('table',$objDC->table);
		#$objOrigin->set('objActiveRecord'.$objDC->activeRecord);
		$objAttribute->setOrigin($objOrigin);
		
		return $objAttribute->getSelectOptions();
	}
	
}