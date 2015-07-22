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

/**
 * Class file
 * TableCustomElementTags
 */
class TableCustomElementTags extends \Backend
{
	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}
	
	/**
	 * Render the tag list record
	 * @param array
	 * @param string
	 * @return string
	 */
	public function listRecord($arrRow, $strLabel)
	{
		if(strlen($arrRow['translations']) > 0)
		{
			$arrTranslations = deserialize($arrRow['translations']);
			$lang = \Input::get('language') ?: \Input::get('lang') ?: $GLOBALS['TL_LANGUAGE'];
			$strLabel = $arrTranslations[$lang]['label'] ?: $strLabel;
		}
		
		return '<a href="'.$this->addToUrl('node='.$arrRow['id']).'" title="'.$GLOBALS['TL_LANG']['MSC']['selectNode'].'">'.$strLabel.'</a>';
	}
	
	
	/**
	 * Add the breadcrumb menu
	 * @param object
	 * @param string
	 * @param string
	 */
	public function addBreadcrumb(\DataContainer $objDC, $strKey='tabletree_node', $strTitleField='title')
	{
		$objSession = \Session::getInstance();
		$intNode = \Input::get('node');
		
		if(isset($intNode))
		{
			// Store the node in the Session
			$objSession->set($strKey,\Input::get('node'));
			// Remove node param from url and reload
			\Controller::redirect(preg_replace('/&node=[^&]*/', '', \Environment::get('request')));
		}
		
		// retrieve active node from session
		$intNode = $objSession->get($strKey);
		
		if($intNode < 1)
		{
			return;
		}
		
		$objDatabase = \Database::getInstance();
		
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
				$arrLinks[] = ' <a href="' . \Controller::addToUrl('node='.$objRow->id) . '" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['selectNode']).'">' . $objRow->$strTitleField . '</a>';
			}
			
			$intId = $objRow->pid;
		}
		while($intId > 0);

		// limit tree view
		$GLOBALS['TL_DCA'][$objDC->table]['list']['sorting']['root'] = array($intNode);
		
		// Add root link
		$arrLinks[] = '<img src="' .PCT_CUSTOMELEMENTS_TAGS_PATH.'/assets/img/tags.png'. '" width="16" height="16" alt=""> <a href="' . \Controller::addToUrl('node=0') . '" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['selectAllNodes']).'">' . $GLOBALS['TL_LANG']['MSC']['filterAll'] . '</a>';
		$arrLinks = array_reverse($arrLinks);

		// Render breadcrumb		
		$GLOBALS['TL_DCA'][$objDC->table]['list']['sorting']['breadcrumb'] = '<ul id="tl_breadcrumb"><li>' . implode(' &gt; </li><li>', $arrLinks) . '</li></ul>';
	}

	
	/**
	 * Copy childs
	 * @param object
	 */
	public function updateChilds(\DataContainer $objDC)
	{
		$objDatabase = \Database::getInstance();
		
		$objActiveRecord = $objDatabase->prepare("SELECT * FROM ".$objDC->table." WHERE id=?")->limit(1)->execute($objDC->id);
		
		$objChilds = $objDatabase->prepare("SELECT * FROM ".$objDC->table." WHERE pid=?")->execute($objDC->id);
		if($objChilds->numRows < 1)
		{
			return;
		}
		
		$time = time();
		while($objChilds->next())
		{
			$arrSet = array
			(
				'tstamp'	=> $time,
				'title' 	=> $objActiveRecord->title.'-'.$objChilds->id
			);
			$objDatabase->prepare("UPDATE ".$objDC->table." %s WHERE id=?")->set($arrSet)->execute($objChilds->id);
		}
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
		if($GLOBALS['TL_DCA'][$table]['config']['closed'])
		{
			return '';
		}

		$objChilds = \Database::getInstance()->prepare("SELECT * FROM ".$table." WHERE pid=?")->limit(1)->execute($row['id']);

		return ($objChilds->numRows && ($this->User->isAdmin || ($this->User->isAllowed(2, $row)))) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ' : \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}
	
	
	/**
	 * Load assets
	 */
	public function loadAssets()
	{
		$GLOBALS['TL_CSS'][] = PCT_CUSTOMELEMENTS_TAGS_PATH.'/assets/css/styles.css';
	}
	
}