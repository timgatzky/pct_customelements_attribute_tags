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
		if(isset($_POST[$strName.'_reset']) || isset($_GET[$strName.'_reset']))
		{
			$this->reset();
		}
		
		$values = $this->getTagsOptions();

		$options = array();
		$isSelected = false;
		
		// build options array
		if(count($values) > 0)
		{
			foreach($values as $i => $v)
			{
				$tmp = array
				(
					'id'  => 'ctrl_'.$strName.'_'.$i,
					'value'  => $v,
					'label'  => $v,
					'name'  => $strName,
				);
				
				if($this->isSelected($v))
				{
					$tmp['selected'] = 'checked';
					$isSelected = true;
				}
				else
				{
					$this->setValue(null);
				}
				$options[] = $tmp;
			}
		}
		
		// insert blank option for resetting the filter
		if($this->get('includeReset'))
		{
			$label = !$isSelected ? sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['filter_firstOption'],$this->objAttribute->title) : $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['filter_reset'];
			$blank = array('value'=>'','label'=>$label,'id'=>'ctrl_'.$strName.'_reset','name'=> $strName.'_reset');
			array_insert($options,0,array($blank));
		}

		$objTemplate->options = $options;
		$objTemplate->name = $strName;
		$objTemplate->label = $this->get('label');

		return $objTemplate->parse();
	}
	
	
	/**
	 * Get the tags options
	 * @return array 
	 */
	protected function getTagsOptions()
	{
		// fetch the possible filter values
		$values = $this->fetchValues($this->getTable(),$this->getFilterTarget());
		
		if(empty($values))
		{
			return array();
		}
		
		$return = array();
		$table = 'tl_pct_customelement_tags';
		$valueField = 'title';
		
		// handle custom tables
		if($this->objAttribute->tag_custom)
		{
			$table = $this->objAttribute->tag_table;
			$valueField = $this->objAttribute->tag_value;
		}
		
		$objTags = \Database::getInstance()->prepare("SELECT id,".$valueField." FROM ".$table." WHERE id IN(".implode(',', $values).")")->execute();
		
		while($objTags->next())
		{
			$return[] = $objTags->$valueField;
		}
		
		return $return;
	}
	
	
	/**
	 * Convert the current filter values back to the raw tag values and return the matching ids for the filter
	 * @return array
	 */
	protected function findMatchingIds()
	{
		// get the current filter values
		$values = $this->getValue();
		if(empty($values))
		{
			return array();
		}
		
		$values = array(152);
		
		return $values;
	}
}