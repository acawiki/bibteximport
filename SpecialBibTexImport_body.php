<?php
if( !defined( 'MEDIAWIKI' ) ) {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	exit( 1 );
}

class SpecialBibTexImport extends SpecialPage {

    function SpecialBibTexImport() {
	SpecialPage::SpecialPage('BibTexImport' , 'bibteximport' );
	wfLoadExtensionMessages('BibTexImport');
    }

    function execute( $par ) {
      global $wgOut, $wgUser;
      $wgOut->setArticleRelated( false );

      $wgOut->setPagetitle( wfMsg( 'bibteximport' ) );
      if (IsSet($_FILES['users_file'])) {
        $wgOut->addHTML( $this->AnalizeArticles($_FILES['users_file']) );
      } else {
        $wgOut->addHTML( $this->MakeForm() );
      }
    }

    function MakeForm() {
      $titleObj = Title::makeTitle( NS_SPECIAL, 'BibTexImport' );
      $action = $titleObj->escapeLocalURL();
      $output = '<p>' . wfMsg( 'bibteximport-form-message' ) . '</p>';
      $output.='<form enctype="multipart/form-data" method="post"  action="'.$action.'">';
      $output.='<table border=0 width=100%>';
      $output.='<tr><td align=right width=160>'.wfMsg( 'bibteximport-form-caption' ).': </td><td><input name="users_file" type="file" size=40 /></td></tr>';
      $output.='<tr><td align=right></td><td><input type="submit" value="'.wfMsg( 'bibteximport-form-button' ).'" /></td></tr>';
      $output.='</table>';
      $output.='</form>';
      return $output;
    }

    function AnalizeArticles($fileinfo) {
      global $IP, $wgOut;
      //TO CHANGE require_once "$IP/includes/User.php";
      $extracted = 0 ;
      $filedata=explode("\n",rtrim(file_get_contents($fileinfo['tmp_name'])));
      $output='';

      foreach ($filedata as $line=>$newuserstr) {
       // $extracted++;
      }

      $output.=wfMsg( 'bibteximport-log-summary-extracter' ).' : '.$extracted.'<br />';
      $output.='<h2>'.wfMsg( 'bibteximport-select-data' ).'</h2>';
      return $output;
    }
  }
