<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2014, Premium Contao Webworks, Premium Contao Themes
 * @author		Tim Gatzky <info@tim-gatzky.de>
 * @package		pct_customelements
 * @attribute	AttributeTags
 * @link		http://contao.org
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
		return '<a href="'.$this->addToUrl('node='.$arrRow['id']).'" title="'.$GLOBALS['TL_LANG']['MSC']['selectNode'].'">'.$strLabel.'</a>';
	}
	
	
	/**
	 * Copy childs
	 */
	public function updateChilds(\DataContainer $objDC)
	{
		$objActiveRecord = \Database::getInstance()->prepare("SELECT * FROM ".$objDC->table." WHERE id=?")->limit(1)->execute($objDC->id);
		
		$objChilds = \Database::getInstance()->prepare("SELECT * FROM ".$objDC->table." WHERE pid=?")->execute($objDC->id);
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
			\Database::getInstance()->prepare("UPDATE ".$objDC->table." %s WHERE id=?")->set($arrSet)->execute($objChilds->id);
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
}