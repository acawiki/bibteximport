<?php
/**
 *
 * @subpackage Extensions
 *
 * @author Steren Giannini
 */

if (!defined('MEDIAWIKI')) die();
require_once "$IP/includes/SpecialPage.php";

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'BibTex Import',
	'author' => array('Steren Giannini'),
	'url' => 'http://wiki.creativecommons.org',
	'description' => 'Imports BibTex files to articles',
	'descriptionmsg' => 'bibteximport-desc',
);

//$wgAvailableRights[] = 'import_users';
//$wgGroupPermissions['bureaucrat']['import_users'] = true;
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['BibTexImport'] = 'SpecialBibTexImport'; 
$wgAutoloadClasses['SpecialBibTexImport'] = $dir . 'SpecialBibTexImport_body.php';
$wgExtensionMessagesFiles['BibTexImport'] = $dir . 'SpecialBibTexImport.i18n.php';
$wgExtensionAliasesFiles['BibTexImport'] = $dir . 'SpecialBibTexImport.alias.php';
