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

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\DC_Table;

/**
 * Table tl_pct_customelement_tags
 */
$GLOBALS['TL_DCA']['tl_pct_customelement_tags'] = array
(
	// Config
	'config' => array
	(
		'label'                       => $GLOBALS['TL_LANG']['tl_pct_customelement_tags']['config']['label'] ?? 'Tags',
		'dataContainer'				  => DC_Table::class,
		'enableVersioning'            => true,
		'markAsCopy'                  => 'title',
		'sql' => array
		(
			'keys' => array
			(
				'id' 	=> 'primary',
				'pid' 	=> 'index',
			)
		),
		'onload_callback' => array
		(
			array('PCT\CustomElements\Backend\TableCustomElementTags', 'addBreadcrumb'),
		),
	),
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 5,
			'rootPaste'               => true,
			'showRootTrails'          => true,
			'fields'                  => array('title'),
			'icon'                    => PCT_CUSTOMELEMENTS_TAGS_PATH.'/assets/img/tags.png',
			'panelLayout'             => 'filter;search',
		),
		'label' => array
		(
			'fields'                  => array('title'),
			'format'                  => '%s',
			'label_callback'          => array('PCT\CustomElements\Backend\TableCustomElementTags', 'listRecord')
		),
		'global_operations' => array
		(
			'toggleNodes' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['toggleAll'],
				'href'                => 'ptg=all',
				'class'               => 'header_toggle'
			),
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			),
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_pct_customelement_tags']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_pct_customelement_tags']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
			),
			'copyChilds' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_pct_customelement_tags']['copyChilds'],
				'href'                => 'act=paste&amp;mode=copy&amp;childs=1',
				'icon'                => 'copychildren.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
				'button_callback'     => array('PCT\CustomElements\Backend\TableCustomElementTags', 'copyWithChilds')
			),
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_pct_customelement_tags']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_pct_customelement_tags']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.svg',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_pct_customelement_tags']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.svg'
			),
		)
	),
	// Palettes
	'palettes' => array
	(
		'default'                  	  => '{title_legend},title;translations;'
	),
	// Fields
	'fields' => array
	(
		'id' => array
		(
			'eval'					  => array('doNotCopy'=>true),
			'sql'                     => "int(10) unsigned NOT NULL auto_increment",
		),
		'pid' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
		),
		'sorting' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
		),
		'tstamp' => array
		(
			'eval'					  => array('doNotCopy'=>true),
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
		),
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_pct_customelement_tags']['title'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50','decodeEntities'=>true),
			'sql'					  =>  "varchar(255) NOT NULL default ''",
		),
		'translations' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_pct_customelement_tags']['translations'],
			'inputType'               => 'metaWizard',
			'eval'                    => array('allowHtml'=>true, 'metaFields'=>array('label')),
			'reference'				  => $GLOBALS['TL_LANG']['tl_pct_customelement_tags']['translations'],
			'sql'                     => "blob NULL"
		),
	)
);

if( \version_compare(ContaoCoreBundle::getVersion(),'5.0','<') )
{
	$GLOBALS['TL_DCA']['tl_pct_customelement_tags']['list']['operations']['copyChilds']['icon'] = 'copychilds.svg';
}