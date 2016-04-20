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
			$arrReturn['tabletree']['sortingField'] = $this->get('tag_sorting');
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
			$strKeyField = $objAttribute->get('tag_key') ?: 'id';
			$strSortingField = $objAttribute->get('tag_sorting') ?: 'sorting';
			$strTranslationField = $this->get('tag_translations') ?: 'translations';
		}
		
		$objResult = $objDatabase->prepare("SELECT * FROM ".$strSource." WHERE ".($this->get('tag_where') ? $this->get('tag_where') : "")." ".$objDatabase->findInSet($strKeyField,$varValue).($strSorting ? " ORDER BY ".$strSortingField:"") )->execute();
		if($objResult->numRows < 1)
		{
			return '';
		}
		
		$metaWizardKey = (version_compare(VERSION,'3.2','<=') ? 'title': 'label');
		
		// translate values
		$arrValues = array();
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
						$this->addTranslation($objResult->{$strValueField},$strLabel,$lang);
					}
				}
			}

			if($this->hasTranslation($strValue))
			{
				$strValue = $this->getTranslatedValue($strValue);
			}
			
			$arrValues[] = $strValue;
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
		
		$objRows = $objDatabase->prepare("SELECT * FROM ".$objOrigin->getTable()." WHERE ".$strField. " IS NOT NULL")->execute();
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
			$strSource = $this->get('tag_table') ?: 'tl_pct_customelement_tags';
			$strValueField = $this->get('tag_value') ?: 'title';
			$strKeyField = $this->get('tag_key') ?: 'id';
			$strSorting = $this->get('tag_sorting') ?: 'sorting';
			$strTranslationField = $this->get('tag_translations') ?: 'translations';
		}
		
		$objResult = $objDatabase->prepare("SELECT * FROM ".$strSource." WHERE ".$objDatabase->findInSet($strKeyField, array_unique($arrValues)).($this->get('tag_where') ? " AND ".$this->get('tag_where') : " ").($strSorting ? " ORDER BY ".$strSorting:"") )->execute();
		if($objResult->numRows < 1)
		{
			return array();
		}
		
		$metaWizardKey = (version_compare(VERSION,'3.2','<=') ? 'title': 'label');
		
		$arrReturn = array();
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
		
		$strTable = $objCC->getTable();
		
		$objRows = \Database::getInstance()->prepare("SELECT * FROM ".$strTable." WHERE ".$strField. " IS NOT NULL")->execute();
		if($objRows->numRows < 1)
		{
			return array();
		}
		
		$arrSession = \Session::getInstance()->getData();
		$strSession = $GLOBALS['PCT_CUSTOMCATALOG']['backendFilterSession'];
		
		$varFilterValue = deserialize($arrSession[$strSession][$strTable][$strField] ?: $arrSession['filter'][$strTable][$strField]);
		$varSearchValue = $arrSession[$strSession.'_search'][$strTable]['value'] ?: $arrSession['search'][$strTable]['value'];
		
		if(is_array($varFilterValue))
		{
			$varFilterValue = $varFilterValue[0];
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
			
			if(!in_array($varSearchValue, $values) && !in_array($varFilterValue, $values))
			{
				continue;
			}
			
			$arrIds[] = $objRows->id;
		}
		
		if(count($arrIds) < 1)
		{
			return array();
		}
		
		return array('FIND_IN_SET(id,?)',implode(',',array_unique($arrIds)));
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
		$strTable = $objCC->getTable();
		if(!\Database::getInstance()->tableExists($strTable))
		{
			return $arrData;
		}
		
		if($objAttribute->get('type') != 'tags' || !\Database::getInstance()->fieldExists($strField,$strTable))
		{
			return $arrData;
		}
		
		// set the orgin to the customcatalog
		$objAttribute->setOrigin($objCC);
		
		$arrData['fieldDef']['options'] = $objAttribute->getSelectOptions();
		
		// show language records in a multilanguage custom catalog source
		if($objAttribute->get('tag_custom'))
		{
			$objSession = \Session::getInstance();
			$strSource = $objAttribute->get('tag_table');
			if(\PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory::validateByTableName($strSource))
			{
				$objSourceCC = \PCT\CustomElements\Plugins\CustomCatalog\Core\Cache::getCustomCatalog($strSource);
				if(!$objSourceCC)
				{
					$objSourceCC = \PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory::findByTableName($strSource);
				}
				
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
		
		return $this->renderCallback($objAttribute->get('alias'),$varValue,$objTemplate,$objAttribute);;
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
			$strSource = $this->get('tag_table') ?: 'tl_pct_customelement_tags';
			$strValueField = $this->get('tag_value') ?: 'title';
			$strKeyField = $this->get('tag_key') ?: 'id';
			$strSorting = $this->get('tag_sorting') ?: 'sorting';
			$strTranslationField = $this->get('tag_translations') ?: 'translations';
		}
		
		$objResult = $objDatabase->prepare("SELECT * FROM ".$strSource." WHERE ".$objDatabase->findInSet($strKeyField, array_unique($arrValues)).($this->get('tag_where') ? " AND ".$this->get('tag_where') : " ").($strSorting ? " ORDER BY ".$strSorting:"") )->execute();
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