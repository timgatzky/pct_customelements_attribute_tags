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


$path = \Contao\System::getContainer()->getParameter('kernel.project_dir').'/vendor/composer/../../system/modules/pct_customelements_attribute_tags';

/**
 * Register the classes
 */
$classMap = array
(
	'PCT\CustomElements\Attributes\Tags'								=> $path.'/PCT/CustomElements/Attributes/Tags/Tags.php',	
	'PCT\CustomElements\Attributes\Tags\TableHelper'					=> $path.'/PCT/CustomElements/Attributes/Tags/TableHelper.php',	
	'PCT\CustomElements\Attributes\Tags\TableCustomElementAttribute'	=> $path.'/PCT/CustomElements/Attributes/Tags/TableCustomElementAttribute.php',	
	'PCT\CustomElements\Backend\TableCustomElementTags'					=> $path.'/PCT/CustomElements/Backend/TableCustomElementTags.php',	
	'PCT\CustomElements\Filters\Tags'									=> $path.'/PCT/CustomElements/Filters/Tags/Tags.php',	
	'PCT\CustomElements\Attributes\Tags\Export'							=> $path.'/PCT/CustomElements/Attributes/Tags/Export.php',	
	'Contao\PCT_TagsModel'												=> $path.'/Contao/PCT_TagsModel.php',
	'PCT\CustomElements\Models\TagsModel'								=> $path.'/PCT/CustomElements/Models/TagsModel.php',	
);

$loader = new \Composer\Autoload\ClassLoader();
$loader->addClassMap($classMap);
$loader->register();

/**
 * Register the templates
 */
\Contao\TemplateLoader::addFiles(array
(
	'customcatalog_filter_tags'		=> 'system/modules/pct_customelements_attribute_tags/templates',
));