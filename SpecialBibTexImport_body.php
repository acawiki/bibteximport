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
      } else if ( IsSet($_POST['import_to_wiki']) ) {
        $wgOut->addHTML( $this->ImportArticles() ); 
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
      require_once(dirname(__FILE__) . "/phpbib/bibliography.php");
      $extracted = 0 ;

      $titleObj = Title::makeTitle( NS_SPECIAL, 'BibTexImport' );
      $action = $titleObj->escapeLocalURL();


      $output_select='';
      $output_select.='<form enctype="multipart/form-data" method="post"  action="'.$action.'">';
      $output_select.='<table style="width: 100%; border :0; ">';

      $myBIB=new Bibliography($fileinfo['tmp_name']);
      foreach($myBIB->biblio["title"] as $bibkey=>$title) {
          $output_select.='<tr> <td><input name="' . $bibkey . '" type="checkbox" /></td> <td>'.wfMsg( 'bibteximport-title' ).'</td> <td><input type="text" name="'. $bibkey .'_-_title" value="' . $title . '" size="60"/></td></tr>';
          if(isset($myBIB->biblio["author"][$bibkey])) { $output_select.='<tr><td></td><td>'.wfMsg( 'bibteximport-author' ).'</td><td><input type="text" name="'. $bibkey .'_-_author" value="' . $myBIB->biblio["author"][$bibkey] . '" size="60"/></td></tr>'; }    
          if(isset($myBIB->biblio["year"][$bibkey])) { $output_select.='<tr><td></td><td>'.wfMsg( 'bibteximport-year' ).'</td><td><input type="text" name="'. $bibkey .'_-_year" value="' . $myBIB->biblio["year"][$bibkey] . '" size="60"/></td></tr>'; }  
          $extracted++;
      }

      $output_select.='<tr><td><br/></td><td></td><td></td></tr>';
      $output_select.='<tr><td></td><td></td><td><input type="submit" name="import_to_wiki" value="'.wfMsg( 'bibteximport-import-button' ).'" /></td></tr>';
      $output_select.='</table>';
      $output_select.='</form>';

      $output=wfMsg( 'bibteximport-log-summary-extracter' ).$extracted.'<br />';
      $output.='<h2>'.wfMsg( 'bibteximport-select-data' ).'</h2>';
      $output.=$output_select;

      return $output;
    }

    function ImportArticles() {
      global $IP;
      //TO CHANGE require_once "$IP/includes/User.php";
      $output = '';

  foreach ($_POST as $key => $value) {
    $output .= $key . addslashes(trim($value)) ."<br/>";
  }

      return $output;
    }
  }
