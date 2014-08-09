<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2014
 * @author		Tim Gatzky <info@tim-gatzky.de>
 * @package		pct_customelements
 * @subpackage	AttributeTags
 * @link		http://contao.org
 */

/**
 * Table tl_pct_customelement_tags
 */
$GLOBALS['TL_DCA']['tl_pct_customelement_tags'] = array
(
	// Config
	'config' => array
	(
		'label'                       => $GLOBALS['TL_LANG']['tl_pct_customelement_tags']['config']['label'] ? $GLOBALS['TL_LANG']['tl_pct_customelement_tags']['config']['label'] : 'Tags',
		'dataContainer'	=> 'Table',
		'switchToEdit'                => true,
		'enableVersioning'            => true,
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
		'onsubmit_callback' => array
		(
			array('PCT\CustomElements\Backend\TableCustomElementTags', 'updateChilds'),
		),
	),
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 5,
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
				'label'               => &$GLOBALS['TL_LANG']['tl_pct_customelement']['edit'],
				'href'                => 'table=tl_pct_customelement_group',
				'icon'                => 'edit.gif',
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_pct_customelement']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
			),
			#'copyChilds' => array
			#(
			#	'label'               => &$GLOBALS['TL_LANG']['tl_page']['copyChilds'],
			#	'href'                => 'act=paste&amp;mode=copy&amp;childs=1',
			#	'icon'                => 'copychilds.gif',
			#	'attributes'          => 'onclick="Backend.getScrollOffset()"',
			#	'button_callback'     => array('PCT\CustomElements\Backend\TableCustomElementTags', 'copyWithChilds')
			#),
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_pct_customelement']['copy'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif',
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_pct_customelement']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_pct_customelement']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
		)
	),
	// Palettes
	'palettes' => array
	(
		'default'                  	  => '{title_legend},title;'
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
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'unique'=>true, 'tl_class'=>'w50'),
			'sql'					  =>  "varchar(255) NOT NULL default ''",
		),	
	)
);