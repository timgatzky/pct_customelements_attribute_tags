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
class TableHelper extends \Backend
{
	/**
	 * Return all contao tables as array
	 * @param object
	 */
	public function getAllTables(\DataContainer $objDC)
	{
		return \Database::getInstance()->listTables();
	}
	
	
	/**
	 * Return all fields as array from the selected table
	 * @object
	 */
	public function getFields(\DataContainer $objDC)
	{
		if(strlen($objDC->activeRecord->tag_table) < 1)
		{
			return array();
		}
		
		$fields = \Database::getInstance()->getFieldNames($objDC->activeRecord->tag_table);
		
		$fields = array_combine($fields,array_values($fields));
		unset($fields['PRIMARY']);
		
		return $fields;
	}
}