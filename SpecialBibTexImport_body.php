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

      $output = <<< INTRO
<p>You can import summaries to AcaWiki from other programs such as EndNote and
Zotero using the BibTex format. After you export the file from another reference
management softwware you can import into AcaWiki by uploading it here. The file
extension will end with a .bib. If you have any questions please feel free to
contact the webmaster at acawikisummaries@gmail.com.</p>
INTRO;

      $output .= '<p>' . wfMsg( 'bibteximport-form-message' ) . '</p>';
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
      $output_select.='<form enctype="multipart/form-data" method="post"  action="'.$action.'" name="correctarticles">';
      $output_select.='<table style="width: 100%; border :0; ">';

      $myBIB=new Bibliography($fileinfo['tmp_name']);
      foreach($myBIB->biblio["title"] as $bibkey=>$title) {
          $output_select.='<tr> <td><input name="bibkey_-_' . $bibkey . '" type="checkbox"/></td> <td>'.wfMsg( 'bibteximport-title' ).'</td> <td><input type="text" name="title_-_'. $bibkey .'" value="' . $title . '" size="60"/></td></tr>';

          //$output_select.= $this->AnalizeArticlesFieldLine($myBIB, "author", $bibkey);

          if(isset($myBIB->biblio["author"][$bibkey])) {     
             $output_select.='<tr><td></td><td>'.wfMsg( 'bibteximport-author' ).'</td><td><input type="text" name="author_-_'. $bibkey .'" value="' . implode(", ",explode(" and ", $myBIB->biblio["author"][$bibkey])) . '" size="60"/></td></tr>'; 
             }

  
          if(isset($myBIB->biblio["month"][$bibkey])) { $month = $myBIB->biblio["month"][$bibkey] . '/'; } else { $month = ''; }

          if(isset($myBIB->biblio["year"][$bibkey])) { $output_select.='<tr><td></td><td>'.wfMsg( 'bibteximport-date' ).'</td><td><input type="text" name="date_-_'. $bibkey .'" value="' . $month . $myBIB->biblio["year"][$bibkey] . '" size="60"/></td></tr>'; }

          $output_select.= $this->AnalizeArticlesFieldLine($myBIB, "journal", $bibkey);
          $output_select.= $this->AnalizeArticlesFieldLine($myBIB, "volume", $bibkey);
          $output_select.= $this->AnalizeArticlesFieldLine($myBIB, "url", $bibkey);
          $output_select.= $this->AnalizeArticlesFieldLine($myBIB, "doi", $bibkey);

          if(isset($myBIB->biblio["note"][$bibkey])) {     
             $output_select.='<tr><td></td><td>'.wfMsg( 'bibteximport-note' ).'</td><td><textarea name="note_-_'. $bibkey .'"  rows="4" cols="50">' . $myBIB->biblio["note"][$bibkey] . '</textarea></td></tr>'; 
          }
             

          $output_select.='<tr><td><br/></td><td></td><td></td></tr>';
          $extracted++;
      }

      $output_select.='<tr><td></td><td></td><td><input type="submit" name="import_to_wiki" value="'.wfMsg( 'bibteximport-import-button' ).'" /></td></tr>';
      $output_select.='</table>';
      $output_select.='</form>';

      $output=wfMsg( 'bibteximport-log-summary-extracter' ).$extracted.'<br />';
      $output.='<h2>'.wfMsg( 'bibteximport-select-data' ).'</h2>';
      $output.=$output_select;

      return $output;
    }

    function AnalizeArticlesFieldLine($BIB, $field, $bibkey) {
          if(isset($BIB->biblio[$field][$bibkey])) { return '<tr><td></td><td>'.wfMsg( 'bibteximport-'. $field ).'</td><td><input type="text" name="' . $field . '_-_' . $bibkey .'" value="' . $BIB->biblio[$field][$bibkey] . '" size="60"/></td></tr>'; }
          else { return ''; }
    }

    function ImportArticles() {
        $output = '<h2>'.wfMsg( 'bibteximport-import-imported' ).'</h2>';
        $console = '<h3>'.wfMsg( 'bibteximport-import-console' ).'</h3>';

        $articles = array();
        $bibkey ='';
        $title = ''; $content = '';

        //We first parse the post data
        foreach ($_POST as $key => $value) {
            $keyword_key = explode('_-_',$key);
            if($keyword_key[0] == 'bibkey' && $value == 'on') { 
                //if bibkey not empty create the page before doing more processing
                if($bibkey !='') {
                    //create the page
                    $output .= $this->Createpage($title,$content);
                }                

                //prepare the new temp variables
                $bibkey = $keyword_key[1]; 
                $console .= wfMsg( 'bibteximport-import-newarticle' ) . ' ' . $bibkey .'<br/>';
                $title = ''; $content = '';
            }
            else if( $keyword_key[1] == $bibkey && $keyword_key[0] == 'title' ) { 
                $console .= wfMsg( 'bibteximport-title' ) . ' ' . $value .'<br/>' ;
                $title = $value;
            }
            else if( $keyword_key[1] == $bibkey && $keyword_key[0] == 'author' ) {
                $console .= wfMsg( 'bibteximport-author' ) . ' ' . $value .'<br/>' ;
                $content.= "|authors=" . $value . "\r\n";
            }
            else if( $keyword_key[1] == $bibkey && $keyword_key[0] == 'date' ) {
                $console .= wfMsg( 'bibteximport-date' ) . ' ' . $value .'<br/>' ;
                $content.= "|pub_date=" . $value . "\r\n";
            }
            else if( $keyword_key[1] == $bibkey && $keyword_key[0] == 'journal' ) {
                $console .= wfMsg( 'bibteximport-journal' ) . ' ' . $value .'<br/>' ;
                $content.= "|journal=" . $value . "\r\n";
            }
            else if( $keyword_key[1] == $bibkey && $keyword_key[0] == 'volume' ) {
                $console .= wfMsg( 'bibteximport-volume' ) . ' ' . $value .'<br/>' ;
                $content.= "|journal_volume=" . $value . "\r\n";
            }
            else if( $keyword_key[1] == $bibkey && $keyword_key[0] == 'url' ) {
                $console .= wfMsg( 'bibteximport-url' ) . ' ' . $value .'<br/>' ;
                $content.= "|url=" . $value . "\r\n";
            }
            else if( $keyword_key[1] == $bibkey && $keyword_key[0] == 'doi' ) {
                $console .= wfMsg( 'bibteximport-doi' ) . ' ' . $value .'<br/>' ;
                $content.= "|doi=" . $value . "\r\n";
            }
            else if( $keyword_key[1] == $bibkey && $keyword_key[0] == 'note' ) {
                $console .= wfMsg( 'bibteximport-note' ) . ' ' . $value .'<br/>' ;
                $content.= "|summary=" . $value . "\r\n";
            }
        }
        if($bibkey !='') {
            //create the page
            $output .= $this->Createpage($title,$content);
        }

      $output.=$console;

      return $output;
    }


    function Createpage($title,$content) {
        global $IP;
        $content = "{{Summary\r\n" . $content . "\r\n|pub_open_access=No}}";

        $articleTitle = Title::newFromText($title);
        $article = new Article($articleTitle);
        if( !$article->exists() )
        {
            $article->doEdit($content, 'BibTex auto import ' . date('Y-m-d h:i:s') );
            if($article)
            {
                return '<a href="' . $articleTitle->escapeFullURL() . '">' . $articleTitle->getText() . '</a> <br/>';
            } else return wfMsg('bibteximport-error-importing') . $title .'<br/>';
        }
        else
        {
            return wfMsg('bibteximport-error-article-exists') . $title .'<br/>';
        }
    }

  }
