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
		}
		
		// set root nodes
		$arrReturn['tabletree']['roots'] = deserialize($this->get('tag_roots'));
		
		// make field sortable
		#$arrReturn['sortable'] = true;
		
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
	public function renderCallback($strField,$varValue,$arrFieldDef,$objTemplate,$objAttribute)
	{
		$varValue = deserialize($varValue);
		
		if(empty($varValue) || count($varValue) < 1)
		{
			return '';
		}
		
		if(!is_array($varValue))
		{
			$varValue = explode(',', $varValue);
		}
		
		// fetch the readable values
		$strSource = 'tl_pct_customelement_tags';
		$strValueField = 'title';
		$strKeyField = 'id';
		$strSorting = 'sorting';
		if($objAttribute->get('tag_custom'))
		{
			$strSource = $objAttribute->get('tag_table');
			$strValueField = $objAttribute->get('tag_value');
			if($objAttribute->get('tag_key')) {$strKeyField = $objAttribute->get('tag_key');}
			$strSortingField = $objAttribute->get('tag_sorting');
		}
		
		$objResult = \Database::getInstance()->prepare("SELECT * FROM ".$strSource." WHERE id IN(".implode(',', $varValue).")".($strSorting ? " ORDER BY ".$strSorting:"") )->execute();
		if($objResult->numRows < 1)
		{
			return '';
		}
		$objTemplate->result = $objResult;
		$objTemplate->value = implode(',', $objResult->fetchEach($strValueField) );
		return $objTemplate->parse();
	}
	
}