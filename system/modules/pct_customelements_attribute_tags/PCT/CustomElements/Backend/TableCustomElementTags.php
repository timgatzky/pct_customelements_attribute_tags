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
namespace PCT\CustomElements\Backend;

/**
 * Imports
 */
use PCT\CustomElements\Helper\ControllerHelper as ControllerHelper;
use Contao\BackendUser;
use Contao\System;

/**
 * Class file
 * TableCustomElementTags
 */
class TableCustomElementTags extends \Contao\Backend
{
	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import(BackendUser::class, 'User');	
	}
	
	/**
	 * Render the tag list record
	 * @param array
	 * @param string
	 * @return string
	 */
	public function listRecord($arrRow, $strLabel)
	{
		if( strlen($arrRow['translations']) > 0)
		{
			$arrTranslations = \Contao\StringUtil::deserialize($arrRow['translations']);
			$lang = \Contao\Input::get('language') ?: \Contao\Input::get('lang') ?: $GLOBALS['TL_LANGUAGE'];
			if( isset($arrTranslations[$lang]['label']) && !empty($arrTranslations[$lang]['label']) )
			{
				$strLabel = $arrTranslations[$lang]['label'];
			}
		}
		
		return '<a href="'.$this->addToUrl('node='.$arrRow['id']).'" title="'.$GLOBALS['TL_LANG']['MSC']['selectNode'].'">'.$strLabel.'</a>';
	}
	
	
	/**
	 * Add the breadcrumb menu
	 * @param object
	 * @param string
	 * @param string
	 */
	public function addBreadcrumb($objDC, $strKey='tabletree_node', $strTitleField='title')
	{
		$objSession = System::getContainer()->get('request_stack')->getSession();
		$intNode = \Contao\Input::get('node');
		
		if(isset($intNode))
		{
			// Store the node in the Session
			$objSession->set($strKey,\Contao\Input::get('node'));
			// Remove node param from url and reload
			\Contao\Controller::redirect(preg_replace('/&node=[^&]*/', '', \Contao\Environment::get('request')));
		}
		
		// retrieve active node from session
		$intNode = $objSession->get($strKey);
		
		if($intNode < 1)
		{
			return;
		}
		
		$objDatabase = \Contao\Database::getInstance();
		
		$arrLinks = array();
		$arrIds = array();
		$intId = $intNode;
		
		do
		{
			$objRow = $objDatabase->prepare("SELECT * FROM ".$objDC->table." WHERE id=?")->limit(1)->execute($intId);
			if($objRow->numRows < 1)
			{
				if($intId == $intNode)
				{
					$objSession->set($strKey,0);
					return;
				}
				break;
			}
			$arrIds[] = $intId;
			
			if ($objRow->id == $intNode)
			{
				$arrLinks[] = $objRow->$strTitleField;
			}
			else
			{
				$arrLinks[] = ' <a href="' . \Contao\Controller::addToUrl('node='.$objRow->id) . '" title="'.\Contao\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['selectNode']).'">' . $objRow->$strTitleField . '</a>';
			}
			
			$intId = $objRow->pid;
		}
		while($intId > 0);

		// limit tree view
		$GLOBALS['TL_DCA'][$objDC->table]['list']['sorting']['root'] = array($intNode);
		
		// Add root link
		$arrLinks[] = '<img src="' .PCT_CUSTOMELEMENTS_TAGS_PATH.'/assets/img/tags.png'. '" width="16" height="16" alt=""> <a href="' . \Contao\Controller::addToUrl('node=0') . '" title="'.\Contao\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['selectAllNodes']).'">' . $GLOBALS['TL_LANG']['MSC']['filterAll'] . '</a>';
		$arrLinks = array_reverse($arrLinks);

		// Render breadcrumb		
		$GLOBALS['TL_DCA'][$objDC->table]['list']['sorting']['breadcrumb'] = '<ul id="tl_breadcrumb"><li>' . implode(' &gt; </li><li>', $arrLinks) . '</li></ul>';
	}

	
	/**
	 * Return the copy with childs button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function copyWithChilds($row, $href, $label, $title, $icon, $attributes, $table)
	{
		if( isset($GLOBALS['TL_DCA'][$table]['config']['closed']) && $GLOBALS['TL_DCA'][$table]['config']['closed'])
		{
			return '';
		}

		$objChilds = \Contao\Database::getInstance()->prepare("SELECT * FROM ".$table." WHERE pid=?")->limit(1)->execute($row['id']);
		
		return ($objChilds->numRows >= 1) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.\Contao\StringUtil::specialchars($title).'"'.$attributes.'>'.\Contao\Image::getHtml($icon, $label).'</a> ' : \Contao\Image::getHtml(preg_replace('/\.gif$/i', '_.', $icon)).' ';
	}
	
	
	/**
	 * Load assets
	 */
	public function loadAssets()
	{
		$request = System::getContainer()->get('request_stack')->getCurrentRequest();
		if( $request && System::getContainer()->get('contao.routing.scope_matcher')->isFrontendRequest($request) )
		{
			return;
		}
		$GLOBALS['TL_CSS'][] = PCT_CUSTOMELEMENTS_TAGS_PATH.'/assets/css/styles.css';
	}
	
	
	/**
	 * Purge revised records like records without existing parent entries that reach back to root level (pid=0) 
	 */
	public function purgeRevisedRecords()
	{
		$strTable = 'tl_pct_customelement_tags';
		
		$objDatabase = \Contao\Database::getInstance();
		$objResult = $objDatabase->prepare("SELECT * FROM ".$strTable." WHERE pid > 0 AND tstamp > 0")->execute();
		if($objResult->numRows < 1)
		{
			return;
		}
		
		$arrPurge = array();
		while($objResult->next())
		{
			$arrParents = $objDatabase->getParentRecords($objResult->id,$strTable);
			if(empty($arrParents))
			{
				continue;
			}
			
			$objRootedParents = $objDatabase->prepare("SELECT * FROM ".$strTable." WHERE id IN (".implode(',', $arrParents).") AND pid=0")->execute();
			
			// all good, the parent trail reaches back to root level
			if($objRootedParents->numRows > 0)
			{
				continue;
			}
			
			$arrPurge[] = $objResult->id;
		}
		
		if(count($arrPurge) > 0)
		{
			$objDatabase->prepare("DELETE FROM ".$strTable." WHERE id IN (".implode(',', $arrPurge).")")->execute();
			// Log
			\Contao\System::getContainer()->get('monolog.logger.contao.cron')->info('Purged tags. Child records id: '.implode(',', $arrPurge));

		}
	} 
	
}