<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2015, Premium Contao Webworks, Premium Contao Themes
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
 * Export
 */
class Export extends \PCT\CustomElements\Plugins\Export\Export
{
	/**
	 * 
	 */
	public function addToExport($arrStatements,$objExport)
	{
		$objDatabase = \Contao\Database::getInstance();
		
		if(count($arrStatements) < 1)
		{
			return $arrStatements;
		}
		
		$strAlias = $objExport->get('strCustomElementAlias');
		$intCE = $objExport->get('intCustomElement');
		
		$options = array
		(
			'columns' => array
			(
				array
				(
				'column'	=> 'type',
				'operation'	=> '=?',
				'value'		=> 'tags'
				),
				array
				(
				'column'	=> 'tag_custom',
				'operation'	=> '!=?',
				'value'		=> '1'
				),
			)
		);
		
		// fetch tags attributes
		$objTagsAttributes = \PCT\CustomElements\Core\AttributeFactory::fetchMultipleByCustomElement($intCE,$options);
		
		if( $objTagsAttributes === null || $objTagsAttributes->numRows < 1)
		{
			return $arrStatements;
		}
		
		$arrTagsAttributes = $objTagsAttributes->fetchEach('id');
		
		// store tag ids
		$arrTags = array();
		
		// fetch tags from vault
		$objVault = $objDatabase->prepare("SELECT * FROM tl_pct_customelement_vault WHERE attr_id IN(".implode(',', $arrTagsAttributes ).")")->execute();
		if($objVault->numRows > 0)
		{
			while($objVault->next())
			{
				$tags = \Contao\StringUtil::deserialize($objVault->data_blob);
				if(!is_array($tags))
				{
					$tags = explode(',', $tags);
				}
				$arrTags = array_merge($arrTags,$tags);
			}
		}
		
		// fetch tags from customcatalogs
		if($GLOBALS['PCT_CUSTOMELEMENTS']['exportCustomCatalogTables'])
		{
			// fetch tags attributes again
			$objTagsAttributes = \PCT\CustomElements\Core\AttributeFactory::fetchMultipleByCustomElement($intCE,$options);
		
			$objCCs = $objDatabase->prepare("SELECT * FROM tl_pct_customcatalog WHERE pid=? AND mode=? AND tableName!=''")->execute($intCE,'new');
			if($objCCs->numRows > 0)
			{
				while($objCCs->next())
				{
					$strTable = $objCCs->tableName;
					if(!$objDatabase->tableExists($strTable))
					{
						continue;
					}
					
					while($objTagsAttributes->next())
					{
						$field = $objTagsAttributes->alias;
						
						$objTagsInTable = $objDatabase->prepare("SELECT * FROM ".$strTable.' WHERE '.$field.' IS NOT NULL')->execute();
						if($objTagsInTable->numRows < 1)
						{
							continue;
						}
						
						while($objTagsInTable->next())
						{
							$tags = \Contao\StringUtil::deserialize($objTagsInTable->{$field});
							if(!is_array($tags))
							{
								$tags = explode(',', $tags);
							}
							$arrTags = array_merge($arrTags,$tags);
						}
					}
				}
			}
		}
		
		$arrTags = array_unique(array_filter($arrTags,'strlen'));	
		
		if(count($arrTags) < 1)
		{
			return $arrStatements;
		}
		
		$objTags = $objDatabase->prepare("SELECT * FROM tl_pct_customelement_tags WHERE id IN(".implode(',', $arrTags).")")->execute();
		if($objTags->numRows > 0)
		{
			$tmp = array();
			while($objTags->next())
			{
				$tmp[] = $this->prepareStatement( $objTags->row() );
			}
			$arrStatements[] = array('table'=>'tl_pct_customelement_tags','data'=>$tmp);
			unset($tmp);
		}	
		
		return $arrStatements;
	}
}