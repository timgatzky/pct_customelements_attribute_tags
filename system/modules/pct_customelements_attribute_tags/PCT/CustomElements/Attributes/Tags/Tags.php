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
namespace PCT\CustomElements\Attributes;

/**
 * Imports
 */
use PCT\CustomElements\Helper\ControllerHelper as ControllerHelper;

/**
 * Class file
 * Tags
 */
class Tags extends \PCT\CustomElements\Core\Attribute
{
	/**
	 * Tell the vault how to save the data (binary,blob)
	 * Leave empty to varchar
	 * @var boolean
	 */
	protected $saveDataAs = 'blob';
	
	/**
	 * Return the field definition
	 * @return array
	 */
	public function getFieldDefinition()
	{
		$arrEval = $this->getEval();

		if($this->get('eval_multiple') > 0)
		{
			$arrEval['fieldType'] ='checkbox';
			$arrEval['multiple'] = true;
		}
		
		$arrReturn = array
		(
			'label'			=> array( $this->get('title'),$this->get('description') ),
			'exclude'		=> true,
			'inputType'		=> 'pct_tabletree',
			'tabletree'		=> array
			(
				'source'		=> 'tl_pct_customelement_tags',
				'valueField'	=> 'title',
				'keyField'		=> 'id',
				'translationField' => 'translations',
			),
			'eval'			=> $arrEval,
			'sql'			=> "blob NULL",
		);
		
		// use a custom source
		if($this->get('tag_custom'))
		{
			$arrReturn['tabletree']['source'] = $this->get('tag_table');
			$arrReturn['tabletree']['valueField'] = $this->get('tag_value');
			$arrReturn['tabletree']['keyField'] = $this->get('tag_key');
			$arrReturn['tabletree']['orderField'] = $this->get('tag_sorting');
			$arrReturn['tabletree']['translationField'] = $this->get('tag_translations');
			$arrReturn['tabletree']['conditionsField'] = 'tag_where';
			$arrReturn['tabletree']['conditions'] = $this->get('tag_where');
		}
		
		// set root nodes
		$arrReturn['tabletree']['roots'] = deserialize($this->get('tag_roots'));
		$arrReturn['tabletree']['rootsField'] = 'tag_roots';
		
		// make field sortable
		$arrOptions = deserialize($this->get('options')) ?: array();
		if(in_array('sortable', $arrOptions))
		{
			$arrReturn['sortable'] = true;
		}
		
		return $arrReturn;
	}
	
	
	/**
	 * Parse widget callback
	 * Generate the widgets in the backend 
	 * @param object	Widget
	 * @param string	Name of the field
	 * @param array		Field definition
	 * @param object	DataContainer
	 * @return string	HTML output of the widget
	 */
	public function parseWidgetCallback($objWidget,$strField,$arrFieldDef,$objDC)
	{
		$arrFieldDef['id'] = $arrFieldDef['strField'] = $arrFieldDef['name'] = $strField;
		$arrFieldDef['strTable'] = $objDC->table;
		// recreate the widget since contao does not support custom config/eval arrays for widgets yet
		$objWidget = new $GLOBALS['BE_FFL']['pct_tabletree']($arrFieldDef);
		$objWidget->label = $this->get('title');
		$objWidget->description = $this->get('description');
		
		// validate the input
		$objWidget->validate();
		
		if($objWidget->hasErrors())
		{
			$objWidget->class = 'error';
		}

		return $objWidget->parse();
	}


	/**
	 * Generate the attribute in the frontend
	 * @param string
	 * @param mixed
	 * @param array
	 * @param string
	 * @param object
	 * @param object
	 * @return string
	 * called renderCallback method
	 */
	public function renderCallback($strField,$varValue,$objTemplate,$objAttribute)
	{
		$this->setData($objAttribute->getData());
		
		$varValue = deserialize($varValue);
		
		if(!is_array($varValue))
		{
			$varValue = explode(',',$varValue);
		}
		
		if(empty($varValue) || count($varValue) < 1)
		{
			return '';
		}
		
		$objDatabase = \Database::getInstance();
		
		// fetch the readable values
		$strSource = 'tl_pct_customelement_tags';
		$strValueField = 'title';
		$strKeyField = 'id';
		$strSortingField = 'sorting';
		$strTranslationField = 'translations';
		if($objAttribute->get('tag_custom'))
		{
			$strSource = $objAttribute->get('tag_table');
			$strValueField = $objAttribute->get('tag_value');
			$strKeyField = $objAttribute->get('tag_key');
			$strSortingField = $objAttribute->get('tag_sorting');
			$strTranslationField = $objAttribute->get('tag_translations');
		}
		
		$objResult = $objDatabase->prepare("SELECT ".$strKeyField.','.$strValueField.($strTranslationField ? ','.$strTranslationField:'')." FROM ".$strSource." WHERE ".($objAttribute->get('tag_where') ? $objAttribute->get('tag_where') : "")." ".$objDatabase->findInSet($strKeyField,$varValue).($strSorting ? " ORDER BY ".$strSortingField:"") )->execute();
		if($objResult->numRows < 1)
		{
			return '';
		}
		
		$metaWizardKey = (version_compare(VERSION,'3.2','<=') ? 'title': 'label');
		
		// translate values
		$arrValues = array();
		
		if(strlen($strTranslationField) > 0)
		{
			while($objResult->next())
			{
				$strValue = $objResult->{$strValueField};
				
				// store the translations
				if(strlen($objResult->{$strTranslationField}) > 0)
				{
					$arrTranslations = deserialize($objResult->{$strTranslationField});
					if(count($arrTranslations) > 0 && is_array($arrTranslations) && array_key_exists($metaWizardKey, $arrTranslations))
					{
						foreach($arrTranslations as $lang => $arrTranslation)
						{
							$strLabel = $arrTranslation[$metaWizardKey];
							if(strlen($strLabel) < 1)
							{
								$strLabel = $objResult->{$strValueField};
							}
							$objAttribute->addTranslation($objResult->{$strValueField},$strLabel,$lang);
						}
					}
				}
	
				if($objAttribute->hasTranslation($strValue))
				{
					$strValue = $objAttribute->getTranslatedValue($strValue);
				}
				$arrValues[] = $strValue;
			}
		}
		
		// no translations
		else
		{
			$arrValues = $objResult->fetchEach($strValueField);
		}
		
		$objTemplate->result = $objResult;
		$objTemplate->value = implode(',', $arrValues);
		return $objTemplate->parse();
	}
	
	
	/**
	 * Return the options as array
	 * @return array()
	 */
	public function getSelectOptions()
	{
		$objOrigin = $this->getOrigin();
		$objDatabase = \Database::getInstance();
		$strField = $this->get('alias');
		
		if(strlen($strField) < 1 || !\Database::getInstance()->fieldExists($strField,$objOrigin->getTable()))
		{
			return array();
		}
		
		// look up from cache
		$objCache = new \PCT\CustomElements\Plugins\CustomCatalog\Core\Cache();
		$objRows = $objCache::getDatabaseResult('Tags::findAll',$strField);
		if($objRows === null)
		{
			$objRows = $objDatabase->prepare("SELECT * FROM ".$objOrigin->getTable()." WHERE ".$strField. " IS NOT NULL")->execute();
			// add to cache
			$objCache::addDatabaseResult('Tags::findAll',$strField,$objRows);
		}
		
		if($objRows->numRows < 1)
		{
			return array();
		}
		
		$arrValues = array();
		while($objRows->next())
		{
			$values = deserialize($objRows->{$strField});
			if(!is_array($values))
			{
				$values = explode(',', $values);
			}
			$arrValues = array_merge($arrValues,$values);
		}
		
		// fetch the readable values
		$strSource = 'tl_pct_customelement_tags';
		$strValueField = 'title';
		$strKeyField = 'id';
		$strSorting = 'sorting';
		$strTranslationField = 'translations';
		if($this->get('tag_custom'))
		{
			$strSource = $this->get('tag_table');
			$strValueField = $this->get('tag_value');
			$strKeyField = $this->get('tag_key');
			$strSorting = $this->get('tag_sorting');
			$strTranslationField = $this->get('tag_translations');
		}
		
		$objResult = $objDatabase->prepare("SELECT ".$strKeyField.','.$strValueField.($strTranslationField ? ','.$strTranslationField:'')." FROM ".$strSource." WHERE ".$objDatabase->findInSet($strKeyField, array_unique($arrValues)).($this->get('tag_where') ? " AND ".$this->get('tag_where') : " ").($strSorting ? " ORDER BY ".$strSorting:"") )->execute();
		if($objResult->numRows < 1)
		{
			return array();
		}
		
		$metaWizardKey = (version_compare(VERSION,'3.2','<=') ? 'title': 'label');
		
		
		
		$arrReturn = array();
		
		if(strlen($strTranslationField) > 0)
		{
			while($objResult->next())
			{
				$strLabel = $objResult->{$strValueField};
				
				// store the translations
				if(strlen($objResult->{$strTranslationField}) > 0)
				{
					$arrTranslations = deserialize($objResult->{$strTranslationField});
					if(count($arrTranslations) > 0 && is_array($arrTranslations))
					{
						foreach($arrTranslations as $lang => $arrTranslation)
						{
							$strLabel = $arrTranslation[$metaWizardKey];
							if(strlen($strLabel) < 1)
							{
								$strLabel = $objResult->{$strValueField};
							}
							$this->addTranslation($objResult->{$strValueField},$strLabel,$lang);
						}
					}
				}
				
				// look up translation
				if($this->hasTranslation($objResult->{$strValueField}))
				{
					$strLabel = $this->getTranslatedValue($objResult->{$strValueField});
				}
				
				$arrReturn[$objResult->{$strKeyField}] = $strLabel;
			}
		}
		// no translation
		else
		{
			$arrReturn = $objResult->fetchEach($strValueField);
		}
		
		return $arrReturn;
	}

	
	/**
	 * Custom backend filtering routing
	 * @param array
	 * @param string
	 * @param object
	 * @param object
	 */
	public function getBackendFilterOptions($arrData,$strField,$objAttribute,$objCC)
	{
		$arrOptions = $objAttribute->getSelectOptions();
		if(count($arrOptions) < 1)
		{
			return array();
		}
		
		$objDatabase = \Database::getInstance();
			
		$strTable = $objCC->getTable();
		
		if(!$objDatabase->tableExists($strTable))
		{
			return array();
		}
		
		// look up from cache
		$objCache = new \PCT\CustomElements\Plugins\CustomCatalog\Core\Cache();
		$objRows = $objCache::getDatabaseResult('Tags::findAll',$strField);
		if($objRows === null)
		{
			$objRows = $objDatabase->prepare("SELECT * FROM ".$objCC->getTable()." WHERE ".$strField. " IS NOT NULL")->execute();
			// add to cache
			$objCache::addDatabaseResult('Tags::findAll',$strField,$objRows);
		}
		
		$arrSession = \Session::getInstance()->getData();
		$strSession = $GLOBALS['PCT_CUSTOMCATALOG']['backendFilterSession'];
		
		$varFilterValue = deserialize($arrSession[$strSession][$strTable][$strField] ?: $arrSession['filter'][$strTable][$strField]);
		$varSearchValue = $arrSession[$strSession.'_search'][$strTable]['value'] ?: $arrSession['search'][$strTable]['value'];
		$varSearchField = $arrSession[$strSession.'_search'][$strTable]['field'];
		
		if(is_array($varFilterValue))
		{
			$varFilterValue = $varFilterValue[0];
		}
		
		$arrSearch = array();
		if(strlen($varSearchValue) > 0 && $varSearchField == $strField)
		{
			$strSource = 'tl_pct_customelement_tags';
			$strKeyField = 'id';
			$strValueField = 'title';
			if($this->get('tag_custom'))
			{
				$strSource = $this->get('tag_table');
				$strValueField = $this->get('tag_value');
				$strKeyField = $this->get('tag_key');
			}
			
			$objTags = $objDatabase->prepare("SELECT * FROM ".$strSource." WHERE ".$strValueField." LIKE '%$varSearchValue%'")->execute();
			
			if($objTags->numRows > 0)
			{
				$arrSearch = $objTags->fetchEach($strKeyField);
			}
		}
		
		$arrIds = array();
		while($objRows->next())
		{
			$values = deserialize($objRows->{$strField});
			if(!is_array($values))
			{
				$values = explode(',', $values);
			}
			
			$values = array_filter($values,'strlen');
			
			// match
			if(count(array_intersect($values, $arrSearch)) > 0)
			{
				$arrIds[] = $objRows->id;
				continue;
			}
			
			if(!in_array($varSearchValue, $values) && !in_array($varFilterValue, $values))
			{
				continue;
			}
			
			$arrIds[] = $objRows->id;
		}
		
		if(count($arrIds) < 1 && (!empty($varFilterValue) || !empty($varSearchValue)) )
		{
			$arrIds = array(-1);
		}
		
		if(empty($arrIds))
		{
			return array();
		}
				
		return array('FIND_IN_SET(id,?)',implode(',',array_unique($arrIds)));
	}
	
	
	/**
	 * Custom sorting routine
	 * @param array		The attribute field defintion
	 * @param string	The attribute alias/name
	 * @param object	The attribute object
	 * @param object	The DataContainer Object
	 * @return string	The ORDER BY part for the Contao query
	 */
	public function getBackendSortingOptions($arrData,$strField,$objAttribute,$objDC)
	{
		$arrOptions = $objAttribute->getSelectOptions();
		if(count($arrOptions) < 1)
		{
			return array();
		}
		
		$objCC = $objAttribute->get('objCustomCatalog') ?: $objAttribute->getOrigin();
		if($objCC === null)
		{
			return '';
		}
		
		$strKeyTrick = 'myKey_';
		
		// trick php to use asort and arsort later on
		$tmp = array();
		foreach($arrOptions as $k => $v)
		{
			$tmp[$strKeyTrick.$k] = $v;
		}
		$arrOptions = $tmp;
		unset($tmp);
		
		$strReturn = '';

		$flag = $objCC->get('list_flag');
		
		// ascending
		if( in_array($flag, array(1,3,11)) || !$flag )
		{
			asort($arrOptions,SORT_NATURAL);
		}
		// descending
		else if( in_array($flag, array(2,4,12)) )
		{
			arsort($arrOptions,SORT_NATURAL);
		}
		
		// rebuild the array
		$tmp = array();
		foreach($arrOptions as $k => $v)
		{
			$k = str_replace($strKeyTrick,'', $k);
			$tmp[$k] = $v;
		}
		$arrOptions = $tmp;
		unset($tmp);
		
		// look up from cache
		$objCache = new \PCT\CustomElements\Plugins\CustomCatalog\Core\Cache();
		$objRows = $objCache::getDatabaseResult('Tags::findAll',$strField);
		if($objRows === null)
		{
			$objRows = $objDatabase->prepare("SELECT * FROM ".$objOrigin->getTable()." WHERE ".$strField. " IS NOT NULL")->execute();
			// add to cache
			$objCache::addDatabaseResult('Tags::findAll',$strField,$objRows);
		}
		
		$arrIds = array();
		$arrKeys = array_keys($arrOptions);
		$strKeyField = 'id';
		$strSorting = 'sorting';
		$strTranslationField = 'translations';
		if($objAttribute->get('tag_custom'))
		{
			$strSource = $objAttribute->get('tag_table');
			$strValueField = $objAttribute->get('tag_value');
			$strKeyField = $objAttribute->get('tag_key');
			$strSorting = $objAttribute->get('tag_sorting');
			$strTranslationField = $this->get('tag_translations');
		}
		
		#\PC::debug($arrKeys);
		$ORDER_BY_FIELD = array();
		
		$pos = 0;
		while($objRows->next())
		{
			$values = deserialize($objRows->{$strField});
			if(!is_array($values))
			{
				$values = explode(',', $values);
			}
			
			if(array_intersect($values, $arrKeys))
			{
				// order by the keys array
				foreach($values as $val)
				{
					$pos = array_search($val, $arrKeys);
					
					// add the entry to the ordered list
					if(isset($ORDER_BY_FIELD[$pos]))
					{
						$pos++;
					}
					
					$ORDER_BY_FIELD[$pos] = $objRows->id;
				}
			}
		}
		
		ksort( $ORDER_BY_FIELD );
		
		$ORDER_BY_FIELD = array_unique($ORDER_BY_FIELD);
		
		if(count($ORDER_BY_FIELD) < 1)
		{
			return '';
		}
		
		return "FIELD(id,".implode(',', $ORDER_BY_FIELD).")";
	}
	
	
	/**
	 * Modify the field DCA settings for customcatalogs
	 * @param array
	 * @param string
	 * @param object
	 * @param object
	 * @param object
	 * @param object
	 * @return array
	 * called by prepareField Hook
	 */	
	public function prepareField($arrData,$strField,$objAttribute,$objCC,$objCE,$objSystemIntegration)
	{
		$objDatabase = \Database::getInstance();
		
		$strTable = $objCC->getTable();
		if(!$objDatabase->tableExists($strTable))
		{
			return $arrData;
		}
		
		if($objAttribute->get('type') != 'tags' || !$objDatabase->fieldExists($strField,$strTable))
		{
			return $arrData;
		}
		
		// set the orgin to the customcatalog
		$objAttribute->setOrigin($objCC);
		
		if($objAttribute->get('be_visible') || $objAttribute->get('be_filter') || $objAttribute->get('be_search') || $objAttribute->get('be_sorting'))
		{
			$strSource = 'tl_pct_customelement_tags';
			if($objAttribute->get('tag_custom'))
			{
				$strSource = $objAttribute->get('tag_table');
			}
			
			if($objDatabase->fieldExists('id',$strSource))
			{
				$arrData['fieldDef']['foreignKey'] = $strSource.'.title';
				$arrData['fieldDef']['relation'] = array('type'=>'hasMany', 'load'=>'lazy');
			
				if($objAttribute->get('tag_custom'))
				{
					$arrData['fieldDef']['foreignKey'] =  $strSource.'.'.$objAttribute->get('tag_value');
				}
			}
			else
			{
				$arrData['fieldDef']['options'] = $objAttribute->getSelectOptions();
			}
		}
		
		// show language records in a multilanguage custom catalog source
		if($objAttribute->get('tag_custom'))
		{
			$objSession = \Session::getInstance();
			$strSource = $objAttribute->get('tag_table');
			if(\PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory::validateByTableName($strSource))
			{
				$objSourceCC = \PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory::findByTableName($strSource);
				
				if($objSourceCC->hasLanguageRecords())
				{
					$strLanguage = \PCT\CustomElements\Plugins\CustomCatalog\Core\Multilanguage::getLanguage($objCC->getTable());
					if(strlen($strLanguage) > 0)
					{
						$arrRoots = \PCT\CustomElements\Plugins\CustomCatalog\Core\Multilanguage::getInstance()->findLanguageRecords($strSource,$strLanguage);
						$arrData['fieldDef']['tabletree']['roots'] = $arrRoots;
					}
					else
					{
						$arrRoots = \PCT\CustomElements\Plugins\CustomCatalog\Core\Multilanguage::getInstance()->findBaseRecords($strSource,$strLanguage);
						$arrData['fieldDef']['tabletree']['roots'] = $arrRoots;
					}
					
					if(\Input::get('act') == 'show')
					{
						// set table tree roots session
						$arrSession = $objSession->get('pct_tabletree_roots');
						$arrSession[$strField] = $arrRoots;
						$objSession->set('pct_tabletree_roots',$arrSession);
					}
				}
			}
		}
		
		return $arrData;
	}
	
	
	/**
	 * Generate wildcard value
	 * @param mixed
	 * @param object	DatabaseResult
	 * @param integer	Id of the Element ( >= CE 1.2.9)
	 * @param string	Name of the table ( >= CE 1.2.9)
	 * @return string
	 */
	public function processWildcardValue($varValue,$objAttribute)
	{
		if($objAttribute->get('type') != 'tags')
		{
			return $varValue;
		}
		
		$objTemplate = new \BackendTemplate('be_customelement_attr_default');
		$objTemplate->setData($objAttribute->getData());
		
		return $this->renderCallback($objAttribute->get('alias'),$varValue,$objTemplate,$objAttribute);
	}
	
	
	/**
	 * Get the readable values from tags and return as array
	 * @param array		List of tags
	 * @return array
	 */
	public function getLabels($arrValues)
	{
		if(count($arrValues) < 1 || !is_array($arrValues))
		{
			return array();
		}
		
		$objDatabase = \Database::getInstance();
		
		// fetch the readable values
		$strSource = 'tl_pct_customelement_tags';
		$strValueField = 'title';
		$strKeyField = 'id';
		$strSorting = 'sorting';
		$strTranslationField = 'translations';
		if($this->get('tag_custom'))
		{
			$strSource = $this->get('tag_table');
			$strValueField = $this->get('tag_value');
			$strKeyField = $this->get('tag_key');
			$strSorting = $this->get('tag_sorting');
			$strTranslationField = $this->get('tag_translations');
		}
		
		$objResult = $objDatabase->prepare("SELECT ".$strKeyField.','.$strValueField.($strTranslationField ? ','.$strTranslationField:'')." FROM ".$strSource." WHERE ".$objDatabase->findInSet($strKeyField, array_unique($arrValues)).($this->get('tag_where') ? " AND ".$this->get('tag_where') : " ").($strSorting ? " ORDER BY ".$strSorting:"") )->execute();
		if($objResult->numRows < 1)
		{
			return array();
		}
		
		$metaWizardKey = (version_compare(VERSION,'3.2','<=') ? 'title': 'label');		
		
		$arrReturn = array();
		while($objResult->next())
		{
			$key = $objResult->{$strKeyField};
			
			$arrReturn[$key] = array($objResult->{$strValueField});
		
			// run through translations
			if(strlen($objResult->{$strTranslationField}) > 0)
			{
				$arrTranslations = deserialize($objResult->{$strTranslationField});
				if(count($arrTranslations) > 0 && is_array($arrTranslations) && array_key_exists($metaWizardKey, $arrTranslations))
				{
					foreach($arrTranslations as $lang => $arrTranslation)
					{
						$strLabel = $arrTranslation[$metaWizardKey];
						if(strlen($strLabel) < 1)
						{
							$strLabel = $objResult->{$strValueField};
						}
						
						if(!in_array($strLabel, $arrReturn[$key]))
						{
							$arrReturn[$key][$lang] = $strLabel;
						}
					}
				}
			}
		}
		
		return $arrReturn;
	}

}