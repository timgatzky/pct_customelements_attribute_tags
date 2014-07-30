<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2014, Premium Contao Webworks, Premium Contao Themes
 * @author		Tim Gatzky <info@tim-gatzky.de>
 * @package		pct_customelements
 * @attribute	Tags
 * @link		http://contao.org
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
	 * Data Array
	 * @var array
	 */
	protected $arrData = array();
	
	/**
	 * Tell the vault to store how to save the data (binary,blob)
	 * Leave empty to varchar
	 * @var boolean
	 */
	protected $saveDataAs = 'blob';
		
	/**
	 * Create new instance
	 * @param array
	 */ 
	public function __construct($arrData=array())
	{
		if(count($arrData) > 0)
		{
			foreach($arrData as $strKey => $varValue)
			{
				$this->arrData[$strKey] = deserialize($varValue);
			}
		}
	}	

	
	/**
	 * Return the field definition
	 * @return array
	 */
	public function getFieldDefinition()
	{
		$arrEval = $this->getEval();
		
		if(isset($arrEval['path']))
		{
			$arrEval['path'] = \FilesModel::findByPk($arrEval['path'])->path;
		}
	
		$arrEval['fieldType'] ='checkbox';
		$arrEval['multiple'] = true;
		$arrEval['isGallery'] = true;
		
		// toggle show files only or folders only
		$arrEval['files'] = $this->get('eval_files') ? 0 : 1;
		
		$arrReturn = array
		(
			'label'			=> array( $this->get('title'),$this->get('description') ),
			'exclude'		=> true,
			'inputType'		=> 'fileTree',
			'eval'			=> $arrEval,
			'sql'			=> "binary(16) NULL",
		);
		
		if($this->get('eval_multiple'))
		{
			ControllerHelper::callstatic('loadDataContainer',array('tl_content'));
			$arrReturn['load_callback'] = array
			(
				array('tl_content', 'setFileTreeFlags')
			);
			$arrReturn['sql'] = "blob NULL";
		}
		
		// make attribute sortable
		if($this->get('sortBy') == 'custom')
		{
			$arrReturn['sortable'] = true;
		}
		
		return $arrReturn;
	}
	
	
	/**
	 * Parse widget callback, render the attribute in the backend
	 * @param object
	 * @param string
	 * @param array
	 * @param object
	 * @param mixed
	 * @return string
	 */
	public function parseWidgetCallback($objWidget,$strField,$arrFieldDef,$objDC,$varValue)
	{
		// validate the input
		$objWidget->validate();
		
		if($objWidget->hasErrors())
		{
			$objWidget->class = 'error';
		}
		
		$strBuffer = $objWidget->parse();
		
		// load data container and language file
		ControllerHelper::callstatic('loadDataContainer',array('tl_content'));
		ControllerHelper::callstatic('loadLanguageFile',array('tl_content'));
		
		$options = deserialize($this->get('options'));
		if(empty($options) || !is_array($options))
		{
			return $strBuffer;
		}
		
		// size field 
		if(in_array('size', $options))
		{
			$strName = $strField.'_size';
			$arrFieldDef = $GLOBALS['TL_DCA']['tl_content']['fields']['size'];
			$arrFieldDef['eval']['tl_class'] = 'w50';
			$arrFieldDef['saveDataAs'] = 'varchar';
			$this->prepareChildAttribute($arrFieldDef,$strName);
		}
		
		// imagemargin field
		if(in_array('imagemargin', $options))
		{
			$strName = $strField.'_imagemargin';
			$arrFieldDef = $GLOBALS['TL_DCA']['tl_content']['fields']['imagemargin'];
			$arrFieldDef['eval']['tl_class'] = 'w50';
			$arrFieldDef['saveDataAs'] = 'varchar';
			$this->prepareChildAttribute($arrFieldDef,$strName);
		}
		
		// perRow field 
		if(in_array('perRow', $options))
		{
			$strName = $strField.'_perRow';
			$arrFieldDef = $GLOBALS['TL_DCA']['tl_content']['fields']['perRow'];
			$arrFieldDef['eval']['tl_class'] = 'w50';
			$arrFieldDef['saveDataAs'] = 'varchar';
			$this->prepareChildAttribute($arrFieldDef,$strName);
		}
		
		// fullscreen/new window field 
		if(in_array('fullsize', $options))
		{
			$strName = $strField.'_fullsize';
			$arrFieldDef = $GLOBALS['TL_DCA']['tl_content']['fields']['fullsize'];
			$arrFieldDef['eval']['tl_class'] = 'w50';
			$arrFieldDef['saveDataAs'] = 'varchar';
			$this->prepareChildAttribute($arrFieldDef,$strName);
		}
		
		// perPage field 
		if(in_array('perPage', $options))
		{
			$strName = $strField.'_perPage';
			$arrFieldDef = $GLOBALS['TL_DCA']['tl_content']['fields']['perPage'];
			$arrFieldDef['eval']['tl_class'] = 'w50';
			$arrFieldDef['saveDataAs'] = 'varchar';
			$this->prepareChildAttribute($arrFieldDef,$strName);
		}
		
		// numberOfItems field 
		if(in_array('numberOfItems', $options))
		{
			$strName = $strField.'_numberOfItems';
			$arrFieldDef = $GLOBALS['TL_DCA']['tl_content']['fields']['numberOfItems'];
			$arrFieldDef['eval']['tl_class'] = 'w50';
			$arrFieldDef['saveDataAs'] = 'varchar';
			$this->prepareChildAttribute($arrFieldDef,$strName);
		}
		
		return $strBuffer;
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
	public function renderCallback($strField,$varValue,$arrFieldDef,$strBuffer,$objTemplate,$objAttribute)
	{
		$varValue = explode(',', $varValue);
		$objGallery = new \ContentGallery($this->getActiveRecord());
		$objGallery->size = $this->findValueByField($strField.'_size');
		$objGallery->imagemargin = $this->findValueByField($strField.'_imagemargin');;
		$objGallery->perRow = $this->findValueByField($strField.'_perRow');;
		$objGallery->perPage = $this->findValueByField($strField.'_perPage');;
		$objGallery->fullsize = $this->findValueByField($strField.'_fullsize');
		$objGallery->multiSRC = $varValue;
		$objGallery->sortBy = $this->get('sortBy');
		$objGallery->orderSRC = $varValue;
		$objGallery->galleryTpl = $this->get('galleryTpl');
		
		// generate the gallery
		$objTemplate->value = $objGallery->generate();
		return $objTemplate->parse();
	}
	
}