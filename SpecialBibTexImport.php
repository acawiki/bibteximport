<?php
/**
 *
 * @subpackage Extensions
 *
 * @author Steren Giannini
 */

if (!defined('MEDIAWIKI')) die();

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'BibTeX Import',
	'author' => array('Steren Giannini'),
	'url' => 'http://www.mediawiki.org/wiki/Extension:BibTexImport',
	'description' => 'Imports BibTeX files to articles',
	'descriptionmsg' => 'bibteximport-desc',
);

//$wgAvailableRights[] = 'import_users';
//$wgGroupPermissions['bureaucrat']['import_users'] = true;
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['BibTeXImport'] = 'SpecialBibTexImport'; 
$wgAutoloadClasses['SpecialBibTexImport'] = $dir . 'SpecialBibTexImport_body.php';
$wgExtensionMessagesFiles['BibTexImport'] = $dir . 'SpecialBibTexImport.i18n.php';
$wgExtensionAliasesFiles['BibTexImport'] = $dir . 'SpecialBibTexImport.alias.php';
