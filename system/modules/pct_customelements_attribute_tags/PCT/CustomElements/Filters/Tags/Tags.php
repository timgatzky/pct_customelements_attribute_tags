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
namespace PCT\CustomElements\Filters;

/**
 * Imports
 */
use \PCT\CustomElements\Helper\ControllerHelper;

/**
 * Class file
 * Tags
 */
class Tags extends \PCT\CustomElements\Filter
{
	/**
	 * The attribute
	 * @param object
	 */
	protected $objAttribute = null;


	/**
	 * Init
	 */
	public function __construct($arrData=array())
	{
		$this->setData($arrData);

		// fetch the attribute the filter works on
		$this->objAttribute = \PCT\CustomElements\Core\AttributeFactory::fetchById($this->get('attr_id'));

		// point the filter to the attribute or use the urlparameter
		$name = $this->get('urlparam') ? $this->get('urlparam') : $this->objAttribute->alias;
		$target = $this->objAttribute->alias;

		// set the filter name
		$this->setName($name);

		// point the filter to the attribute
		$this->setFilterTarget($target);

		// set filter to strict mode
		$this->strictMode(true);
		
		// set the filter to be multiple
		$this->set('multiple',true);
		
		// show empty results
		$this->set('showEmptyResults',false);
		if(isset($GLOBALS['PCT_CUSTOMCATALOG']['FRONTEND']['FILTER']['showEmptyResults']))
		{
			$this->set('showEmptyResults',(boolean)$GLOBALS['PCT_CUSTOMCATALOG']['FRONTEND']['FILTER']['showEmptyResults']);
		}
		
		// use ids as values
		$this->set('useIdsAsFilterValue',false);
		if(isset($GLOBALS['PCT_CUSTOMELEMENTS']['FILTERS']['tags']['settings']['useIdsAsFilterValue']))
		{
			$this->set('useIdsAsFilterValue',(boolean)$GLOBALS['PCT_CUSTOMELEMENTS']['FILTERS']['tags']['settings']['useIdsAsFilterValue']);
		}
	}


	/**
	 * Prepare a new simple filter with the matchings ids and return it
	 * @return object
	 */
	public function prepareFilter()
	{
		return new SimpleFilter( $this->findMatchingIds() );
	}


	/**
	 * Render the filter and return string
	 * @param string	Name of the attribute
	 * @param mixed		Active filter values
	 * @param object	Template object
	 * @param object	The current filter object
	 * @return string
	 */
	public function renderCallback($strName,$varActiveFilterValue,$objTemplate,$objFilter)
	{
		// reset the filter (will reload the page)
		if(\Contao\Input::post($strName.'_reset') != '' || \Contao\Input::get($strName.'_reset') != '')
		{
			$this->reset();
		}
		
		$objJumpTo = $objFilter->jumpTo;
		$objModule = $objFilter->getModule();
		
		$arrValues = $this->getTagsOptions();

		$arrOptions = array();
		$isSelected = false;
				
		// build options array
		if(count($arrValues) > 0)
		{
			foreach($arrValues as $i => $v)
			{
				$label = $v;
				$value = $v;
				
				// check the condition of the value in relation to other filters
				// skip the value if it would produce an empty result
				if(!$this->hasResults($value) && $this->get('showEmptyResults') === false)
				{
					continue;
				}
				
				$tmp = array
				(
					'id'  		=> 'ctrl_'.$strName.'_'.$i,
					'value'  	=> $value,
					'label'  	=> $label,
					'name'  	=> $strName,
					'selected'	=> false,
				);
				
				// translate label
				if($this->hasTranslation($value))
				{
					$tmp['label'] = $this->getTranslatedValue($value);
				}
				
				if($this->isSelected($value))
				{
					$tmp['selected'] = true;
					$tmp['href'] = $objFilter->removeFromUrl($value,$objJumpTo,$objFilter->getModule()->customcatalog_filter_method);
					$isSelected = true;
				}
				else
				{
					$tmp['href'] = $objFilter->addToUrl($value,$objJumpTo,$objFilter->getModule()->customcatalog_filter_method);
				}
				
				$arrOptions[] = $tmp;
				unset($tmp);
			}
		}
		
		// insert blank option for resetting the filter
		if($this->get('includeReset'))
		{
			$label = !$isSelected ? sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['filter_firstOption'],$this->objAttribute->title) : $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['filter_reset'];
			$blank = array('value'=>'','label'=>$label,'id'=>'ctrl_'.$strName.'_reset','name'=> $strName.'_reset');
			array_insert($arrOptions,0,array($blank));
		}

		$objTemplate->options = $arrOptions;
		$objTemplate->name = $strName;
		$objTemplate->label = $this->get('label');

		return $objTemplate->parse();
	}
	
	
	/**
	 * Get the tags options
	 * @return array 
	 */
	protected function getTagsOptions($arrValues=array())
	{
		$bolByValueField = false;
		if(count($arrValues) < 1 && !$this->isModified('arrValue'))
		{
			// fetch the possible filter values
			$arrValues = $this->fetchValues($this->getTable(),$this->getFilterTarget());
		}
		else if(count($arrValues) < 1 && $this->isModified('arrValue'))
		{
			$arrValues = $this->getValue();
		}
		else
		{
			$bolByValueField = true;
		}
		
		if(empty(array_filter($arrValues)))
		{
			return array();
		}
		
		$objDatabase = \Contao\Database::getInstance();
		
		$return = array();
		$strSource = 'tl_pct_customelement_tags';
		$strKeyField = 'id';
		$strValueField = 'title';
		$strTranslationField = 'translations';
		$strSortingField = 'sorting';
		// handle custom tables
		if($this->objAttribute->tag_custom)
		{
			$strSource = $this->objAttribute->tag_table;
			$strValueField = $this->objAttribute->tag_value;
			$strKeyField = $this->objAttribute->tag_key;
			$strTranslationField = $this->objAttribute->tag_translations;
			$strSortingField = $this->objAttribute->tag_sorting;
		}
		
		$blnHasIdField = $objDatabase->fieldExists('id',$strSource);
		
		if($bolByValueField)
		{
			foreach($arrValues as $i => $v)
			{
				// capsule strings
				if(strlen(strpos($v, ',')) > 0 || (is_string($v) && !is_numeric($v)) )
				{
					$arrValues[$i] = "'".$v."'";
				}
			}
			$objTags = $objDatabase->prepare("SELECT ".($blnHasIdField ? "id,":'').$strValueField.($strKeyField ? ','.$strKeyField:'').($strTranslationField ? ','.$strTranslationField:'')." FROM ".$strSource." WHERE ".$strValueField." IN(".implode(',', $arrValues).")" . ($strSortingField ? " ORDER BY ".$strSortingField : "") )->execute();
		}
		else
		{
			$objTags = $objDatabase->prepare("SELECT ".($blnHasIdField ? "id,":'').$strValueField.($strKeyField ? ','.$strKeyField:'').($strTranslationField ? ','.$strTranslationField:'')." FROM ".$strSource." WHERE id IN(".implode(',', $arrValues).")" . ($strSortingField ? " ORDER BY ".$strSortingField : "") )->execute();
		}
		
		$metaWizardKey = (version_compare(VERSION,'3.2','<=') ? 'title': 'label');
					
		$arrReturn = array();
		while($objTags->next())
		{
			$varValue = $objTags->{$strValueField};
			
			// use ID field
			if($blnHasIdField && $this->get('useIdsAsFilterValue') === true && $strKeyField == 'id')
			{
				$varValue = $objTags->id;
			}
			// use key field value
			else if(!$blnHasIdField && $this->get('useIdsAsFilterValue') === true)
			{
				$varValue = $objTags->{$strKeyField};
			}
			
			// always store the tags title as fallback to the current language
			$this->addTranslation($varValue,$objTags->{$strValueField},$GLOBALS['TL_LANGUAGE']);
			
			// store the translations
			if(strlen($objTags->{$strTranslationField}) > 0)
			{
				$arrTranslations = \Contao\StringUtil::deserialize($objTags->{$strTranslationField});
				
				if(count($arrTranslations) > 0 && is_array($arrTranslations))
				{
					foreach($arrTranslations as $lang => $arrTranslation)
					{
						if(!array_key_exists($metaWizardKey, $arrTranslation))
						{
							continue;
						}
						
						$strLabel = $arrTranslation[$metaWizardKey];
						if(strlen($strLabel) < 1)
						{
							$strLabel = $objTags->{$strValueField};
						}
						
						$this->addTranslation($varValue,$strLabel,$lang);
					}
				}
			}
			// if the tag has not translations use the title as label
			else
			{
				$this->addTranslation($varValue,$objTags->{$strValueField},$GLOBALS['TL_LANGUAGE']);
			}
			
			$arrReturn[$objTags->{$strKeyField}] = $varValue;
		}
		
		return $arrReturn;
	}
	
	
	/**
	 * Convert the current filter values back to the raw tag values and return the matching ids for the filter
	 * @return array
	 */
	protected function findMatchingIds()
	{
		// get the current filter values
		$filterValues = $this->getValue();
		
		if(empty($filterValues))
		{
			return array();
		}
		
		$objDatabase = \Contao\Database::getInstance();
		$strField = $this->getFilterTarget();
		$strPublished = ($GLOBALS['PCT_CUSTOMCATALOG']['FRONTEND']['FILTER']['publishedOnly'] ? $this->getCustomCatalog()->getPublishedField() : '');
		
		$objCache = new \PCT\CustomElements\Plugins\CustomCatalog\Core\Cache();
		
		// look up from cache
		$objRows = $objCache::getDatabaseResult('Tags::findAll'.(strlen($strPublished) > 0 ? 'Published' : ''),$strField);
		if($objRows === null)
		{
			$objRows = $objDatabase->prepare("SELECT id,".$strField." FROM ".$this->getTable()." WHERE ".$strField." IS NOT NULL ".(strlen($strPublished) > 0 ? " AND ".$strPublished."=1" : ""))->execute();
			// add to cache
			$objCache::addDatabaseResult('Tags::findAll'.(strlen($strPublished) > 0 ? 'Published' : ''),$strField,$objRows);
		}
		
		if($objRows->numRows < 1)
		{
			return array();
		}
		
		if($this->get('useIdsAsFilterValue') === true)
		{
			$arrTags = $filterValues;
		}
		else
		{
			$arrTags = array_keys($this->getTagsOptions($filterValues));
		}
	
		$arrReturn = array();
		while($objRows->next())
		{
			if(strlen($objRows->{$strField}) < 1)
			{
				continue;
			}
			
			$values = \Contao\StringUtil::deserialize($objRows->{$strField});
			
			if(!is_array($values))
			{
				$values = explode(',', $values);
			}
			
			// exact mode
			if($this->get('mode') == 'exact')
			{
				if( empty(array_diff($arrTags,$values)) )
				{
					$arrReturn[] = $objRows->id;
				}
				continue;
			}
			
			// normal mode
			if(count(array_intersect($values, $arrTags)) > 0)
			{
				$arrReturn[] = $objRows->id;
			}
		}
		
		return $arrReturn;
	}
}