<?php
//
// +------------------------------------------------------------------------+
// | phpBIB
// | $Id: bibliography.php,v 2.0 2005/07/22 08:58:54 dfolio Exp $
// | Creation Date   : Tue Apr 28 2005
// | LastUpdate $Date: 2005/07/22 08:58:54 $
// +------------------------------------------------------------------------+
// | Copyright (c) 2003-2005 David FOLIO, Antre Team
// | Email         dfolio@free.fr
// | Web           http://dfolio.free.fr
// +------------------------------------------------------------------------+
// | This source file is subject to BSD License, that is available
// | at http://opensource.org/licenses/bsd-license.php
// | or http://dfolio.free.fr/license.txt
// +------------------------------------------------------------------------+
//
  /** Bibliography management
   * 
   * This script provide several method to render bibliography, usually build
   * from the {@link BibtexParser}.
   *
   *
   * This script is provided "as is" for free. No support is available.  I am
   * not responsible for any loss due to the use of this program.
   * You may modify the program for your own purpose. If you think your
   * modification may benefit others, please send me a copy to: 
   * {@link mailto:dfolio@free.fr dfolio@free.fr}. 
   * Thank you!
   *
   * If you think it is helpful, please kindly add a link pointing to: 
   * {@link http://dfolio.free.fr}
   *
   * @version   $Revision: 2.0 $
   * @author David FOLIO <dfolio@free.fr>
   * @copyright Copyright &copy; 2003-2005, Antre Team
   * @license http://dfolio.free.fr/license.txt
   *
   * @package phpBIB
   */
/** Needs phpbib constants*/
include_once(dirname(__FILE__).'/constants.php');
/** Need the {@link  BibtexParser "bibtex parser"} */
require_once(dirname(__FILE__).'/bibtexParser.php');

/** Class  Bibliography: bibliography management
 *
 *
 * @author David FOLIO
 * @version 1.1
 * @package phpBIB
 */
class Bibliography{
  /** The bibliography data
   *
   * This is an array which contains bibliography entry, usually build with
   * {@link BibtexParser}
   * @var Array
   */
  var $biblio;
  /** 
   * Provide cited entry
   */
  var $arrCited;
  /** define if cache is enabled
   * @var Boolean
   * @access private
   */
  var $_useCache;

  /** The constructor
   * @param $bibfiles a list of bibTeX file to load
   * @param $useCache (dis)enable the cache management
   */
  function Bibliography($bibfiles=null,$useCache=false){
    $this->arrCited=array();
    $this->biblio=array();
    $this->_useCache=$useCache;
    if (!empty($bibfiles)) {
      $bibTeX=new BibTexParser($bibfiles,$this->_useCache);
      //$bibTeX->load($bibfiles,$useCache);
      $this->biblio=$bibTeX->getBibData();
      $bibTeX->destroy($this->_useCache);
      unset($bibTeX);
    }
    $this->initLang();
  }
  /** Define the biblio data entry
   * @param Array the biblio data entry, which is build from BibtexParser
   * @see {@link BibtexParser}
   */
  function setBiblio($biblio){
    if (!is_array($biblio)){
      trigger_error("Bibliography::setBiblio > Bad parameter biblio=".
		    print_r($biblio,true).
		    " which must be an array of  biblio data entry\n", 
		    E_USER_ERROR); 
       return false;
    }
    $this->biblio=$biblio;
  }
  /** Get the biblio data entry
   * @return Array the biblio data entry array
   */
  function getBiblio(){return $this->biblio;}

  /** Delete the current biblio. 
   * If there the cache is enable, also remove the biblio from the cache.
   * 
   * @param Boolean  (dis)enable the cache management  
   */
  function destroy($useCache=false){
    if  ($useCache&&isset($_SESSION["PHPBIB_BIBLIO"])) unset($_SESSION["PHPBIB_BIBLIO"]);
    unset($this->biblio);
    //$this=NULL;
    return true;
  }
  
  /* Some usefull function to access to some fields ---------------------------- */

  /** Get author 
   * 
   * Get author fields for the bikey value. If no author fields are found then
   * the function return <var>FALSE</var>.
   * @param (String) a bikey value
   * @return (Mixed)
   * @todo ameliorer la generation des author en eliminant les 'and' inutiles...
   */
  function getAuthor($bibkey){
    if (isset($this->biblio["author"][$bibkey])){
      $authors=$this->biblio["author"][$bibkey];
      $str='';$list=array();
      if (!empty($authors)&&is_string($authors)){
	$ltmp=explode(' and ',$authors);
	foreach($ltmp as $anAuthor){
	  $anAuthor=(trim($anAuthor));
	  if (!array_key_exists($anAuthor,$list))
	    $list[$anAuthor]='<a href="'.Bibliography::href('',array('bibAuthor'=>$anAuthor)).
	      '" title="'.__('Author').': '.$anAuthor.'">'.$anAuthor."</a>";
	}
	ksort($list);$str=implode(", ", $list);
	if(($pos=strrpos($str,','))!==false){
	  $str =substr($str,0,$pos).' '.__(' and ').' '.substr($str,$pos+1);
	}
      }
      return " ".BIB_AUTHOR_START.$str.". ".BIB_AUTHOR_END." ";
    }elseif (isset($this->biblio["editor"][$bibkey])) 
       return " ".BIB_AUTHOR_START.$this->biblio["editor"][$bibkey].". ".BIB_AUTHOR_END." ";
    else return false;
  }

  /** Get title
   * 
   * Get title fields for the bikey value. If no title fields are found then
   * the function return <var>FALSE</var>.
   * @param (String) a bikey value
   * @return (Mixed)
   */
  function getTitle($bibkey){
    if (isset($this->biblio["title"][$bibkey])) return BIB_TITLE_START.trim($this->biblio["title"][$bibkey])."".BIB_TITLE_END;
    else return false;
  } 
  /** Get Date
   * 
   * Get date  field for the bikey value. 
   * To build the date, the year field is required, and the month field is
   * optional. If the month fields is available  
   * If no year fields are found then the function return <var>FALSE</var>.
   * @param (String) a bikey value
   * @return (Mixed)
   */
  function getDate($bibkey){
    if (isset($this->biblio["year"][$bibkey])){ 
      $date=(isset($this->biblio["month"][$bibkey])?$this->biblio["month"][$bibkey] :"")." ".$this->biblio["year"][$bibkey];
      return " ".BIB_DATE_START.
	((strtotime($date)!=-1) ? __(date(" F ",strtotime($date))):$date).
	$this->biblio["year"][$bibkey].". ".BIB_DATE_END;
    }
    return false;
  }
  /** Get abstract
   * 
   * Get abstract field for the bikey value. 
   * @param (String) a bikey value
   * @return (String)
   */
  function getAbstract($bibkey){
    return (isset($this->biblio["abstract"][$bibkey])?
	    BIB_ABSTRACT_START.'<strong>'.__("Abstract").':</strong> '.$this->biblio["abstract"][$bibkey].BIB_ABSTRACT_END."\n":"");
  }
  /** Get keywords
   * 
   * Get keywords field for the bikey value. 
   * @param (String) a bikey value
   * @return (String)
   */
  function getKeywords($bibkey){
    if (!isset($this->biblio["keywords"][$bibkey])) return "";
    $keywords=$this->biblio["keywords"][$bibkey];
    $list=array();
    if (!empty($keywords)&& is_string($keywords)){
      $ltmp=explode(',',$keywords);
      foreach($ltmp as $akeyword){
	$akeyword=strtoupper(trim($akeyword));
	if (!array_key_exists($akeyword,$list))
	  $list[$akeyword]='<a href="'.Bibliography::href('',array('bibKeyword'=>$akeyword)).'" title="'.$akeyword.'">'.$akeyword."</a>";
      }
      ksort($list);
      return BIB_KEYWORDS_START.'<strong>'.__("Keyword(s)").':</strong> '.
	' '.implode(", ", $list).".".BIB_KEYWORDS_END."\n";
    }
    return '';
  }
  
  function getWeb($bibkey){
    $str=(isset($this->biblio["url"][$bibkey])? 
          __('Available at:')." ".str_replace('%s',$this->biblio["url"][$bibkey] ,BIB_URL_PATERN)
          .(isset($this->biblio["lastchecked"][$bibkey])? ' ('.__('last seen')." ".$this->biblio["lastchecked"][$bibkey].')' :"")
          :"");

    if (strlen($str)>1)   return  BIB_WEB_START.$str.BIB_WEB_END."\n";
    return '';
  }
   function getAnnote($bibkey){
      return  (isset($this->biblio["annote"][$bibkey])? 
               BIB_ANNOTE_START.$this->biblio["annote"][$bibkey].BIB_ANNOTE_END."\n"
               :"");
  }
  function getBookInfo($bibkey){
    $str=  (isset($this->biblio["isbn"][$bibkey])? 
             BIB_ISBN_START.'ISBN: '.$this->biblio["isbn"][$bibkey].BIB_ISBN_END.""
            :"");
      
    $str=(isset($this->biblio["issn"][$bibkey])? 
          ((strlen($str)>1)?trim($str).', ':'').BIB_ISBN_START.'ISSN: '.$this->biblio["issn"][$bibkey].BIB_ISBN_END.""
        :"");
    if (strlen($str)>1)   return trim($str).'.';
    return '';
  }
  function getIcons($bibkey){
    return BIB_ICONS_START
      .'<a href="'.Bibliography::href('',array('bibKey'=>$bibkey)).'" title="View bibTeX of '.$bibkey.'">'
      .str_replace('%s',$bibkey,BIB_ICONS_IMG_PATERN).'</a>'
      .(isset($this->biblio["doi"][$bibkey])? " &nbsp;".str_replace('%s',$this->biblio["doi"][$bibkey],BIB_DOI_PATERN) :'')
      .BIB_ICONS_END;
  }
  
  /* Some usefull function to build some entry --------------------------------- */
  /**#@+ 
   * If required fields are not set then the function return <var>FALSE</var>.
   *
   */
  /** Design @article category
   *
   *  Desin an article from a journal or magazine. 
   *  - Required fields: author, title, journal, year. 
   *  - Optional fields: volume, number, pages, month.
   * @param (String) a bikey value
   * @return (Mixed)
   **/
  function buildArticle($bibkey){
    if (!isset($this->biblio["author"][$bibkey])||!isset($this->biblio["title"][$bibkey])||
	!isset($this->biblio["journal"][$bibkey])||!isset($this->biblio["year"][$bibkey]))
      return false;
    return " ".$this->getAuthor($bibkey)
      .$this->getTitle($bibkey)
      .BIB_JOURNAL_START.$this->biblio["journal"][$bibkey].BIB_JOURNAL_END
      .trim(isset($this->biblio["volume"][$bibkey])? ", Vol. ".$this->biblio["volume"][$bibkey] :"")
      .trim(isset($this->biblio["number"][$bibkey])? "(".$this->biblio["number"][$bibkey].")" :"")
      .trim(isset($this->biblio["pages"][$bibkey])? "&nbsp;: p.&nbsp;".$this->biblio["pages"][$bibkey] :"").". "
      .$this->getDate($bibkey)
      .$this->getBookInfo($bibkey)
      .$this->getWeb($bibkey)
      ."";
  }
  /** Design @book category
   *
   * Design a book with an explicit publisher. 
   *  - Required fields: author or editor, title, publisher, year.
   *  - Optional fields: volume, number, series, address, edition, month.
   * @param (String) a bikey value
   * @return (Mixed)
   */
  function buildBook($bibkey){
    if (!isset($this->biblio["author"][$bibkey])&&!isset($this->biblio["editor"][$bibkey]))   return "<!-- NO AUHTOR OR EDITOR -->";
    if (!isset($this->biblio["title"][$bibkey])||!isset($this->biblio["publisher"][$bibkey])) return false;
    return " ".$this->getAuthor($bibkey)
      .$this->getTitle($bibkey)
      .BIB_PUBLISHER_START.$this->biblio["publisher"][$bibkey].BIB_PUBLISHER_END
      .trim(isset($this->biblio["edition"][$bibkey])?", ".$this->biblio["edition"][$bibkey] :"")
      .trim(isset($this->biblio["series"][$bibkey])?", ".$this->biblio["series"][$bibkey] :"")
      .trim(isset($this->biblio["volume"][$bibkey])? ", Vol. ".$this->biblio["volume"][$bibkey] :"")
      .trim(isset($this->biblio["number"][$bibkey])? "(".$this->biblio["number"][$bibkey].")" :"")
      .trim(isset($this->biblio["pages"][$bibkey])? "&nbsp;: p.&nbsp;".$this->biblio["pages"][$bibkey] :"").". "
      .(isset($this->biblio["address"][$bibkey])?BIB_ADDRESS_START.$this->biblio["address"][$bibkey].". ".BIB_ADDRESS_END :"")
      .$this->getDate($bibkey)
      .$this->getBookInfo($bibkey)
      .$this->getWeb($bibkey)
      ."";
  }
  /** Design @book category
   *
   * Design a book with an explicit publisher. 
   *  - Required fields: title.
   *  - Optional fields: author, howpublished, address, month, year, note.
   * @param (String) a bikey value
   * @return (Mixed)
   */
 /* function buildBookLet($bibkey){
    if (!isset($this->biblio["title"][$bibkey]))    return false;
    return " ".$this->getAuthor($bibkey)
      .$this->getTitle($bibkey)
      .(isset($this->biblio["edition"][$bibkey])?", ".$this->biblio["edition"][$bibkey] :" ")
      .(isset($this->biblio["series"][$bibkey])?", ".$this->biblio["series"][$bibkey] :" ")
      .(isset($this->biblio["volume"][$bibkey])? ", Vol. ".$this->biblio["volume"][$bibkey] :"")
      .(isset($this->biblio["number"][$bibkey])? "(".$this->biblio["number"][$bibkey].")" :"")
      .(isset($this->biblio["pages"][$bibkey])? "&nbsp;: p.&nbsp;".$this->biblio["pages"][$bibkey] :"").". "
      .(isset($this->biblio["address"][$bibkey])?BIB_ADDRESS_START.$this->biblio["address"][$bibkey].". ".BIB_ADDRESS_END :" ")
      .$this->getDate($bibkey)
      ."";
  }*/
  /** Design @inproceedings category
   *
   * Design an article in a conference proceedings. 
   * - Required fields: author, title, booktitle, year. 
   * - Optional fields: editor, volume or number, series, pages, address, month, organization, publisher.
   * @param (String) a bikey value
   * @return (Mixed)
   */
  function buildInProceedings($bibkey){
    if (!isset($this->biblio["author"][$bibkey])||!isset($this->biblio["title"][$bibkey])||
	!isset($this->biblio["booktitle"][$bibkey])||!isset($this->biblio["year"][$bibkey]))
      return false;
    return " ".$this->getAuthor($bibkey)
      .$this->getTitle($bibkey)
      .BIB_BOOKTITLE_START.$this->biblio["booktitle"][$bibkey].BIB_BOOKTITLE_END
      .trim(isset($this->biblio["publisher"][$bibkey])? ", ".$this->biblio["publisher"][$bibkey] :"")
      .trim(isset($this->biblio["editor"][$bibkey])?", ".$this->biblio["editor"][$bibkey] :"")
      .trim(isset($this->biblio["series"][$bibkey])?", ".$this->biblio["series"][$bibkey] :"")
      .trim(isset($this->biblio["volume"][$bibkey])?", Vol. ".$this->biblio["volume"][$bibkey] :"")
      .trim(isset($this->biblio["number"][$bibkey])?"(".$this->biblio["number"][$bibkey].")" :"")
      .trim(isset($this->biblio["pages"][$bibkey])?"&nbsp;: p.&nbsp;".$this->biblio["pages"][$bibkey] :"").". "
      .(isset($this->biblio["organization"][$bibkey])?$this->biblio["organization"][$bibkey] :"")
      .(isset($this->biblio["address"][$bibkey])?BIB_ADDRESS_START.$this->biblio["address"][$bibkey].". ".BIB_ADDRESS_END :" ")
      .$this->getDate($bibkey)
      .$this->getBookInfo($bibkey)
      .$this->getWeb($bibkey)
      ."";
  }
  /** Design thesis like category
   *
   * Design a  PhD or Master's thesis. 
   *  - Required fields: author, title, school, year. 
   *  - Optional fields: type, address, month.
   * @param (String) a bikey value
   * @return (Mixed)
   */
  function buildThesis($bibkey){
    if (!isset($this->biblio["author"][$bibkey])||!isset($this->biblio["title"][$bibkey])||
	!isset($this->biblio["school"][$bibkey])||!isset($this->biblio["year"][$bibkey]))
      return false;
    return " ".$this->getAuthor($bibkey)
      .$this->getTitle($bibkey)
      .BIB_SCHOOL_START
      .(isset($this->biblio["type"][$bibkey])? $this->biblio["type"][$bibkey].", " :" ")
      .$this->biblio["school"][$bibkey].BIB_SCHOOL_END
      .(isset($this->biblio["address"][$bibkey])?" ".BIB_ADDRESS_START.$this->biblio["address"][$bibkey].". ".BIB_ADDRESS_END :" ")
      .$this->getDate($bibkey)
      .$this->getWeb($bibkey)
      ."";
  }
  /** Design report category
   *
   * A report published or some audiovisual/film material.
   *  - Required fields: author, title, institution, year. 
   *  - Optional fields: type, number, address, month. 
   * @param (String) a bikey value
   * @return (Mixed)
   */
  function buildReport($bibkey){
    if (!isset($this->biblio["author"][$bibkey])||!isset($this->biblio["title"][$bibkey])||
	!isset($this->biblio["institution"][$bibkey])||!isset($this->biblio["year"][$bibkey]))
      return false;
    return " ".$this->getAuthor($bibkey)
      .trim(isset($this->biblio["type"][$bibkey])? " ".$this->biblio["type"][$bibkey].":" :" ")
      .trim(isset($this->biblio["number"][$bibkey])?" ".$this->biblio["number"][$bibkey].":" :"")
      .$this->getTitle($bibkey)
      .BIB_INSTITUTION_START.$this->biblio["institution"][$bibkey].BIB_INSTITUTION_END
      .(isset($this->biblio["address"][$bibkey])?BIB_ADDRESS_START.$this->biblio["address"][$bibkey].". ".BIB_ADDRESS_END :"")
      .$this->getDate($bibkey)
      .$this->getWeb($bibkey)
      ."";
  }
  /** Design @misc category
   *
   *  Desin a misc entry
   *  - Optional fields: author, title, howpublished, address,  year, month.
   * @param (String) a bikey value
   * @return (Mixed)
   **/
  function buildMisc($bibkey){
    return " ".$this->getAuthor($bibkey)
      .$this->getTitle($bibkey)
      .(isset($this->biblio["howpublished"][$bibkey])? " ".$this->biblio["howpublished"][$bibkey] :"")
      .(isset($this->biblio["address"][$bibkey])?", ".BIB_ADDRESS_START.$this->biblio["address"][$bibkey].". ".BIB_ADDRESS_END :" ")
      .$this->getDate($bibkey)
      .$this->getBookInfo($bibkey)
      .$this->getWeb($bibkey)
      ."";
  }
  
  /**#@-*/

  /** Translate bibitem to string
   *
   * This is the main function to render a bibitem. It's check the category field
   * (which must be set, otherwise the function return <var>FALSE</var>) in
   * order to design the the bibitem.
   *
   * You can add your own category. In this case you have to design the
   * corresponding "build" function, as proposed above.
   *
   * @param (String) a bikey value
   * @param (Array) the array in which to search
   * @return (Mixed)
   */
  function item2str($bibkey){
    if (!isset($this->biblio["category"][$bibkey])) return false;
    $item_start="";$item_end=$this->getAnnote($bibkey);
    switch($this->biblio["category"][$bibkey]){
      case "UNPUBLISHED":
      default:
	return "<!--not designed-->\n";
      case "ARTICLE":
	return $item_start.$this->buildArticle($bibkey).$item_end;
      case "INBOOK":
        if (!(isset($this->biblio["pages"][$bibkey])||isset($this->biblio["chapter"][$bibkey]))) return false;
	if (!isset($this->biblio["type"][$bibkey]))$this->biblio["type"][$bibkey]=__("InBook");
      case "BOOKLET":
	if (!isset($this->biblio["author"][$bibkey]))$this->biblio["author"][$bibkey]=" ";
	if (!isset($this->biblio["editor"][$bibkey]))$this->biblio["editor"][$bibkey]=" ";
	if (!isset($this->biblio["publisher"][$bibkey]))$this->biblio["publisher"][$bibkey]=" ";
	if (!isset($this->biblio["year"][$bibkey]))$this->biblio["year"][$bibkey]=" ";
	if (!isset($this->biblio["type"][$bibkey]))$this->biblio["type"][$bibkey]=__("Booklet");
      case "BOOK":
	if (!isset($this->biblio["type"][$bibkey]))$this->biblio["type"][$bibkey]=__("Book");
	return $item_start.$this->buildBook($bibkey).$item_end;
      case "PROCEEDINGS":if (!isset($this->biblio["booktitle"][$bibkey]))$this->biblio["booktitle"][$bibkey]=__("Proceeding");
      case "INCOLLECTION":if (!isset($this->biblio["publisher"][$bibkey])) return false;
      case "CONFERENCE":
      case "INPROCEEDINGS":
	return $item_start.$this->buildInProceedings($bibkey).$item_end;
      case "MASTERSTHESIS":if (!isset($this->biblio["type"][$bibkey]))$this->biblio["type"][$bibkey]=__("Master thesis");
      case "PHDTHESIS":if (!isset($this->biblio["type"][$bibkey]))$this->biblio["type"][$bibkey]=__("PhD thesis");
	return $item_start.$this->buildThesis($bibkey).$item_end; 
      case "MANUAL":	
	if (!isset($this->biblio["type"][$bibkey]))$this->biblio["type"][$bibkey]=__("Manual");
	if (!isset($this->biblio["author"][$bibkey]))$this->biblio["author"][$bibkey]="";	
	if (!isset($this->biblio["title"][$bibkey]))$this->biblio["title"][$bibkey]="";
	if (!isset($this->biblio["institution"][$bibkey]))$this->biblio["institution"][$bibkey]="";
	if (!isset($this->biblio["year"][$bibkey]))$this->biblio["year"][$bibkey]="";	
      case "AUDIOVISUAL": if (!isset($this->biblio["type"][$bibkey]))$this->biblio["type"][$bibkey]=__("Audiovisal");
      case "TECHREPORT":  if (!isset($this->biblio["type"][$bibkey]))$this->biblio["type"][$bibkey]=__("Technical report");
	return $item_start.$this->buildReport($bibkey).$item_end;
    case "WEBPAGE":
    case "MISC":
      return $item_start.$this->buildMisc($bibkey).$item_end;
    }
  } 
  /** Translate bibitem to bibTeX
   *
   * This function (re)build the bibitem entry into bibtex format, in order to
   * be included in your HTML page.
   * @param (String) a bikey value
   * @param (Array) the array in which to search
   * @return (Mixed)
   */
  function item2bib($bibkey){
    if (!isset($this->biblio["category"][$bibkey])) return false;
    $html=BIB_BIBENTRY_START.'<strong>'.__("BibTeX Reference").':</strong> <div class="bibTeX">@'.$this->biblio["category"][$bibkey].'{'.$bibkey.",<br/>\n";
    foreach($this->biblio as $field=>$fval){
      if(($field==="category")||(!isset($fval[$bibkey]))||empty($fval[$bibkey])) continue;
      $html.= "\t&nbsp;&nbsp;".$field.' = {'.$fval[$bibkey]."},<br/> \n";
    }
    return $html."};\n</div>".BIB_BIBENTRY_END."\n";
  }


  /** Generic build view 
   * @param (String) the view must be one of used field (eg keyword, authors, category...).
   * @param (Boolean) if <var>TRUE</var> include the corresponding bibTeX
   * @param (Boolean) if <var>TRUE</var> include subitem (abstract,
   * keywords,...)
   * @param String a view filter function
   * @param Mixed the filter argument
   * @return (Mixed)
   * @sa stringViewFilter(), authorViewFilter()
   * @access private
   */
  function _buildView($view="", $showBib=false, $showSubItem=false,$usekSort=false,
		      $filter_func="stringViewFilter", $filter=""){
    if (!isset($this->biblio[$view])) {
      if (BIB_PARSE_DEBUG) echo "<!-- no view:$view -->\n";
      return false; 
    }
    if ($usekSort) ksort($this->biblio[$view]);
    //if (BIB_PARSE_DEBUG)  echo "<!-- VIEW $view:( $filter_func)$filter:$pattern -->\n";
    $html='';
    //if (BIB_PARSE_DEBUG) $html.= "\n<!-- [$view] -->\n"; 
    $list=array();$i=0;
    foreach($this->biblio[$view] as $bibkey=>$item){
      if(in_array($bibkey,$list)){continue;}	
      if (!empty($filter_func)&&( $filter_func!=="")){
	if (!call_user_func(array(get_class($this),$filter_func),$filter,strtoupper(trim($this->biblio[$view][$bibkey])))) {
	  continue;}
      }
      $i++;
      //if (BIB_PARSE_DEBUG) $html.= "\n<!-- [$i] $bibkey -->\n";
      $list[]=$bibkey;
      $item_start=sprintf(BIB_BIBITEM_START,$bibkey);
      $item_start.=BIB_BIBKEY_START.$bibkey.$this->getIcons($bibkey).BIB_BIBKEY_END;
      $item_end=" ".BIB_BIBITEM_END."\n";
      $html.= $item_start.$this->item2str($bibkey).$item_end;
      if ($showBib||$showSubItem) $html.=BIB_SUBITEM_START;
      if ($showSubItem)	$html.="\n".$this->getAbstract($bibkey).$this->getKeywords($bibkey)."";
      if ($showBib) $html.= $this->item2bib($bibkey);
      if ($showBib||$showSubItem) $html.=BIB_SUBITEM_END."\n";      
    }
    return $html."\n";
  }

  /**Dump the bibliography
   *
   * This is the main function to build the bibliography view
   * @param (String) define the sorting method (if not empty).
   * If it's set must be one of {@link BIB_SORT_LIST}.
   * If it's empty don't sort the bibliography.
   * @param (Boolean) if <var>TRUE</var> include the corresponding bibTeX
   * @param (Boolean) if <var>TRUE</var> include subitem (abstract,
   * keywords,...)
   * @return (Mixed)
   */
  function dump($sort="", $showBib=false, $showSubItem=false){
    if (!empty($sort)){
      if (strpos(BIB_SORT_LIST,$sort)===false) $sort="author";
      if (strpos($sort,"author")!==false){
	if ($sort{0}=='r'){
	  $sort=substr($sort,1);
	  if(krsort($this->biblio[$sort])===false) echo "can't sort by $sort";
	}else if(ksort($this->biblio[$sort])===false) echo "can't sort by $sort";
      }else{
	if ($sort{0}=='r'){
	  $sort=substr($sort,1);
	  if(arsort($this->biblio[$sort])===false) echo "can't sort by $sort";
	}else if(asort($this->biblio[$sort])===false) echo "can't sort by $sort";
      }
    }else $sort="author";
    return BIB_VIEW_START
      ."<!-- buildView($sort,$showBib, $showSubItem ...)-->"
      .$this->_buildView($sort,$showBib, $showSubItem,false, "", "")
      ."\n".BIB_VIEW_END." \n";
   }

  /* Some usefull function to build view --------------------------------------- */

  /**Build bibkeys view 
   * @param (String) the category.
   * @param (Boolean) if <var>TRUE</var> include the corresponding bibTeX
   * @param (Boolean) if <var>TRUE</var> include subitem (abstract, keywords,...)
   * @return (Mixed)
   */
  function bibkeysView(&$bibkeys, $showBib=false, $showSubItem=false,$usekSort=false){
    if (is_string($bibkeys)){
      $bibkeysStr=$bibkeys;$bibkeys=array();
      $bibkeysStr=str_replace(' ','',trim($bibkeysStr));
      if (strpos($bibkeysStr,',')!=false) $bibkeys=explode(',',$bibkeysStr);
      else $bibkeys[]=$bibkeysStr;
    }/* else is an array!*/
    if ($usekSort) ksort($bibkeys);
    $html='';
    $list=array();$i=0;
    foreach($bibkeys as $bibkey){
      if(in_array($bibkey,$list)){continue;}	
      $i++;$list[]=$bibkey;
      $item_start=sprintf(BIB_BIBITEM_START,$bibkey);
      $item_start.=BIB_BIBKEY_START.$bibkey.$this->getIcons($bibkey).BIB_BIBKEY_END;
      $item_end=" ".BIB_BIBITEM_END."\n";
      $html.= $item_start.$this->item2str($bibkey).$item_end;
      if ($showBib||$showSubItem) $html.=BIB_SUBITEM_START;
      if ($showSubItem)	$html.="\n".$this->getAbstract($bibkey).$this->getKeywords($bibkey)."";
      if ($showBib) $html.= $this->item2bib($bibkey);
      if ($showBib||$showSubItem) $html.=BIB_SUBITEM_END."\n";      
    }
    return  BIB_VIEW_START.$html.BIB_VIEW_END."\n";
  }
  /**Build year view 
  /**Build category view 
   * @param (String) the category.
   * @param (Boolean) if <var>TRUE</var> include the corresponding bibTeX
   * @param (Boolean) if <var>TRUE</var> include subitem (abstract, keywords,...)
   * @return (Mixed)
   */
  function categoryView($category="", $showBib=false, $showSubItem=false){
    if (!isset($this->biblio["category"])) return false; 
    return BIB_VIEW_START
      .$this->_buildView("category", $showBib, $showSubItem,true,"stringViewFilter", $category)
      ."\n".BIB_VIEW_END."\n";
  }
  /**Build year view 
   * @param (String) the category.
   * @param (Boolean) if <var>TRUE</var> include the corresponding bibTeX
   * @param (Boolean) if <var>TRUE</var> include subitem (abstract, keywords,...)
   * @return (Mixed)
   */
  function yearView($year="", $showBib=false, $showSubItem=false){
    if (!isset($this->biblio["year"])) return false; 
    return BIB_VIEW_START
      .$this->_buildView("year",$showBib, $showSubItem,true,"stringViewFilter", $year)
      ."\n".BIB_VIEW_END."\n";
  }
  /**Build keywords view 
   * @param (String) the keyword.
   * If it's empty build the complete bibliography.
   * @param (Boolean) if <var>TRUE</var> include the corresponding bibTeX
   * @param (Boolean) if <var>TRUE</var> include subitem (abstract, keywords,...)
   * @return (Mixed)
   */
  function keywordView($keyword="", $showBib=false, $showSubItem=false){
    if (!isset($this->biblio["keywords"])) return false; 
    return BIB_VIEW_START
      .$this->_buildView( "keywords",$showBib, $showSubItem,true,"stringViewFilter", $keyword)
      ."\n".BIB_VIEW_END."\n";
  }
  /**Build author view 
   * @param (String) the author.
   * @param (Boolean) if <var>TRUE</var> include the corresponding bibTeX
   * @param (Boolean) if <var>TRUE</var> include subitem (abstract, keywords,...)
   * @return (Mixed)
   */
  function authorView($author="", $showBib=false, $showSubItem=false){
    if (!isset($this->biblio["author"])) return false; 
    if (strpos($author,' OR ')!==false){
      $author=explode(' OR ',$author);       
    } 
    return BIB_VIEW_START
      .$this->_buildView("author",$showBib, $showSubItem,true,"authorViewFilter", $author)
      ."\n".BIB_VIEW_END."\n";
  }

  /* Some usefull function to build some "list" -------------------------------- */
  /** A simple string view filter.
   * @param String the filter argument
   * @param String the value to parse
   * @return TRUE if the $filter is found in the $val, false otherwise
   */
  function stringViewFilter($filter,$val){
    if(!empty($filter)&&(is_string($filter))&&(strpos($val,strtoupper(trim($filter)))!==false)) {return true;}
    return false;    
  }
  /** The author  view filter.
   * @param Mixed the filter argument
   * @param String the value to parse
   * @return TRUE if the $filter is found in the $val, false otherwise
   */
  function authorViewFilter($filter,$val){
    if(empty($val))return false;
    $authors=array();
    if (is_string($filter)){
      $filter=strtoupper(trim($filter));
      if (strpos($filter,' OR ')!==false) $authors=explode(' OR ',$filter);
      else $authors[]=$filter;      
    }else $authors=$filter;
    
    if (count($authors)<2){
      foreach($authors as $k=>$author){
	if (empty($author)) continue;
        //if (BIB_PARSE_DEBUG)
        if (strpos($author,' AND ')!==false){
          $authorsAND=explode(' AND ',$filter);
          foreach($authorsAND as $an){if (strpos($val,$an)===false) return false;}
          return true;
        }elseif(strpos($val,$author)!==false) {return true;}
      }      
    } else
      if(preg_match('/('.implode('|',$authors).')/i',$val)) {return true;}
    return false;    
  }
  /* Some usefull function to build some "list" -------------------------------- */
  /**Build categories list
   *
   * This function build categroy list of the bibliography.
   * @return String
   */
  function categoriesList(){
    if (!isset($this->biblio["category"])) return false;
    $list=array();
    foreach($this->biblio["category"] as $bibkey=>$category){
      if (!array_key_exists($category,$list))
	$list[$category]='<li><a href="'.Bibliography::href('',array('bibCategory'=>$category)).'" title="'.__('Category').': '.$category.'">'.$category."</a></li>";
    }
    ksort($list);
    return BIB_CATEGORIES_LIST_START.implode("\n", $list).BIB_CATEGORIES_LIST_END."<br/>\n";
  }

  /**Build years list
   *
   * This function build categroy list of the bibliography.
   * @return String
   */
  function yearsList(){  
    if (!isset($this->biblio["year"])) return false;  
    $list=array();
    foreach($this->biblio["year"] as $bibkey=>$year){
      if (!array_key_exists($year,$list))
	$list[$year]='<li><a href="'.Bibliography::href('',array('bibYear'=>$year)).
	  '" title="'.__('Year').': '.$year.'">'.$year."</a></li>";
    }
    ksort($list);
    return BIB_YEARS_LIST_START.implode("\n", $list).BIB_YEARS_LIST_END."<br/>\n";
  }

  /**Build keywords list
   *
   * This function build the keywords list of the bibliography.
   * @return String
   */
  function keywordsList(){
    if (!isset($this->biblio["keywords"])) return false;
    $list=array();
    foreach($this->biblio["keywords"] as $bibkey=>$keywords){
      $ltmp=explode(',',$keywords);
      foreach($ltmp as $akeyword){
	$akeyword=strtoupper(trim($akeyword));
	if (!array_key_exists($akeyword,$list))
	  $list[$akeyword]='<li><a href="'.Bibliography::href('',array('bibKeyword'=>$akeyword)).'" title="'.$akeyword.'">'.$akeyword."</a></li>";
      }
    }   
    ksort($list);
    //echo "\n\n<!-- ";print_r($list);echo " -->\n\n";
    return BIB_KEYWORDS_LIST_START.implode("\n", $list).BIB_KEYWORDS_LIST_END."<br/>\n";
  }
  /**Build authors list
   *
   * This function build author list of the bibliography.
   * @return String
   */
  function authorsList($letter=false){  
    if (is_string($letter)) $letter=strtoupper($letter);
    if (!isset($this->biblio["author"])) return false;  
    $list=array();
    if ($letter===true){
      foreach($this->biblio["author"] as $bibkey=>$authors){
	$ltmp=explode(' and ',$authors);
	foreach($ltmp as $anAuthor){
	  $anAuthor=(trim($anAuthor));$ai=Bibliography::getAuthorInitial($anAuthor);
	  if (!array_key_exists($ai,$list)){	   
	    $list[$ai]='<li><a href="'.Bibliography::href('',array('bibAuthor'=>$ai)).
	      '" title="'.__('Author').': '.$ai.'">'.$ai."</a></li>";
	  }
	}
      }
    }else{
      foreach($this->biblio["author"] as $bibkey=>$authors){
	$ltmp=explode(' and ',$authors);
	foreach($ltmp as $anAuthor){
	  $anAuthor=(trim($anAuthor));$ai=Bibliography::getAuthorInitial($anAuthor);
	  if (!array_key_exists($anAuthor,$list) &&($letter&&($ai==$letter)||!$letter)){	   
	    $list[$anAuthor]='<li><a href="'.Bibliography::href('',array('bibAuthor'=>$anAuthor)).
	      '" title="'.__('Author').': '.$anAuthor.'">'.$anAuthor."</a></li>";
	  }
	}
      }
    }
    uksort($list,array("Bibliography","authorcmp"));
    return BIB_AUTHORS_LIST_START.implode("\n", $list).BIB_AUTHORS_LIST_END."<br/>\n";  
  }

  /**Build sort list
   *
   * This function build the know sort list of the bibliography.
   * @param (String) define the separator to use between different 'sort'
   * category.
   * @return (String) HTML code
   */
  function sortList($sepList=""){
    $html="";
    $sort_list=explode(',',BIB_SORT_LIST);
    foreach($sort_list as $asort){ 
      $label=__("sort by").' '.$asort;
      if ($asort{0}=='r') $label.=' ('.__("reverse").')';
      $html.='<a href="'.Bibliography::href('',array("sort"=>$asort)).'" title="'.$label.'" > '.$label."</a>".$sepList;
    }
    return $html;
  }
/*
  function searchSubject($subject){ 
    $list=array();
    $subjects=strtoupper(implode(' ',$subject));
    if (isset($this->biblio["bibkey"])){
      
    }
  }
*/
  /** Build URI
   *
   * This function build an internal (to the package) href URI.
   * @param (String) base URL (index page)
   * @param (Array) an array of query string, such as:
   * <code>array("query"=>"val",...)</code>
   * For empty query just set an empty val.
   * @param (Array) a list of query to remove
   * @return (String) the href string...
   */
  function href($url="",$arr_queries=null,$removes=null){
    if (empty($url)) $url=BIB_INDEX;    
    $queries="";
    if (empty($arr_queries)||!is_array($arr_queries))$arr_queries=array(); 
    if (empty($removes)&&(!is_bool($removes)))
      $removes=array("bibAuthor",'bibCategory','bibYear','bibKeyword','bibKey','biblio',
        'bibkey','bibCite','bibref','bibRef');
    $qstr=(empty($_SERVER["QUERY_STRING"])) ? "": $_SERVER["QUERY_STRING"];
    // are there some query string defined?
    if (($p=strpos($url,'?'))!==false){
      $qstr=substr($url,$p+1).'&'.$qstr;
      $url=substr($url,0,$p);
    }
    if (!empty($qstr)&&($removes!==true)){
      $qt=explode('&', urldecode($qstr));
      foreach($qt as $q){
	if (strpos($q,'=')!==false){list($qa,$qv)=explode('=',$q);
	}else{$qa=$q;$qv="";}
	if (in_array($qa,$removes)&&!in_array($qa,array_keys($arr_queries))) continue;
	if (!isset($arr_queries[$qa])) $arr_queries[$qa]=$qv;
      }
    }    
    foreach($arr_queries as $qa => $qv){
      /*if ((is_array($removes)&& @in_array($qa,$removes))) unset($arr_queries[$qa]);
      else*/
      if (empty($qv))  $queries .= "&$qa";
      else             $queries .= "&$qa=".($qv);
    }
    if ($queries[0]=='&')$queries[0]='?'; 
    return $url . htmlspecialchars($queries);
  }

  /** Build a bibkey URI
   *
   * This function provide a usefull way to build a bibliography key entry.
   * Becarefull, this function does not check if the corresponding
   * <var>$ref</var> is defined in the bibliography!
   * @param (String) a bibkeyt reference
   * @param (String) index where are located the reference page, and where the
   * 'bibKey' are binded
   * @return (String) anchor HTML markup
   */  
  function bibkey($ref,$index=BIB_INDEX){
    return '<a class="ref" href="'.Bibliography::href($index, array('bibKey'=>$ref)).'" title="'.__("cite").' '.$ref.' ">'.$ref."</a>";
  }

  /** Build a citation.
   *
   * This function provide a usefull way to cite a bibliography entry.
   * Becarefull, this function does not check if the corresponding
   * <var>$ref</var> is defined in the bibliography!
   * @param (String) a bibkeyt reference
   * @param (String) index where are located the reference page, and where the
   * 'bibKey' are binded
   * @return (String) anchor HTML markup
   */  
  function cite($ref,$index=""){
    if (empty($index)) $index=$_SERVER["PHP_SELF"];    

    if (!in_array($ref,$this->arrCited))$this->arrCited[]=$ref;
    return '<a class="ref" href="#'.$ref.'" title="'.__("cite").' '.$ref.' ">'.$ref."</a>";
      //$this->bibkey($ref,$index);
  }

  function getCited(){
    $cited="\n<dl>\n";
    foreach($this->arrCited as $ref){
      $cited.=" <dt class=\"bibkey\" id=\"$ref\">$ref</dt>".
	"<dd>".$this->item2str($ref)."</dd>\n";
    }
    return $cited."</dl>\n";
  }

  /** Initialize language support
   *
   *  This function load all language file defined in <var>'./lang/'.$lang</var>.
   *  All file with '.php' is this directory was considered as a laqnguage file.
   * @param (String) a two letter country iso639 code  (such as 'en', 'fr'...) which
   * must be a directory available in './lang' diretory.
   * @return (Boolean) <var>$TRUE</var> if success
   */
  function initLang($lang=""){
    if (empty($lang)){
      $checkLang=array("language","Lang",'user_lang','user_langage');
      foreach ($checkLang as $aLang){
        if (isset($_REQUEST[$aLang])) {$lang=$_REQUEST[$aLang];break;}
        elseif (isset($_SESSION[$aLang])) {$lang=$_SESSION[$aLang];break;}
        elseif (isset($GLOBALS[$aLang])) {$lang=$GLOBALS[$aLang];break;}
        $aLang=strtoupper($aLang);
        if (isset($_REQUEST[$aLang])) {$lang=$_REQUEST[$aLang];break;}
        elseif (isset($_SESSION[$aLang])) {$lang=$_SESSION[$aLang];break;}
        elseif (isset($GLOBALS[$aLang])) {$lang=$GLOBALS[$aLang];break;}
      }
      if (empty($lang)) $lang=BIB_DEFAULT_LANG;
    }
    $dir=dirname(__FILE__).'/lang/'.$lang;
    if (!is_dir($dir)){
      trigger_error("[".__CLASS__."::initLang] $dir is not a directory \n",E_USER_WARNING);
      return false;
    }
    if (function_exists("glob")){
      $files = glob("$dir/*.php");/*print_r($files);*/
      foreach ((array)$files as $filename) {if (is_readable($filename)) include $filename;}
      return $lang;
    }else{
      if ($dh = opendir($dir)){
        while(($file = readdir($dh))!== false) {
          if (strpos($file,'.php')!==false)  include $dir.'/'.$file;
        }
        closedir($dh);
        return $lang;
      }
    }
    return false;
  }
  
  /** Authors comparison
   *
   * This function provide an author comparison for sort criteriurm.
   *
   * Note that this comparison is case sensitive.
   * @param (String) first author
   * @param (String) second author
   * @return (Int) 
   *  - < 0 if a is less than b,
   *  - > 0 if a  is greater than b,
   *  -   0 if they are equal. 
   */
  function authorcmp ($a, $b) {
    $ai=Bibliography::getAuthorInitial($a);
    $bi=Bibliography::getAuthorInitial($b);
    if ($ai!==$bi) return ($ai<$bi) ? -1 : +1;
    $posA=strrpos($a,'.');  $posB=strrpos($b,'.');
    if (($posA===false)&&($posB===false)){
      return strcmp($a,$b);    
    }elseif(($posA===false)&&($posB!==false)){
      return strcmp($a,substr($b,$posB+1));
    }elseif(($posA!==false)&&($posB===false)){
      return strcmp(substr($a,$posA+1),$b);
    }else{
      return strcmp(substr($a,$posA+1),substr($b,$posB+1));
    }
    //return strcmp($a,$b);//inutile...
  }
  /** Try to guess author initial
   *
   * This method parse an author name to guess its 'first letter' initial.
   * @param (String) author to parse
   * @return (String) the initial (first letter name) or false if the given
   * parameter is not a string...
   */
  function getAuthorInitial($author,$isFirst=true){
    if (!is_string($author)) { 
      if (BIB_PARSE_DEBUG) echo "$author not a string  ";
      return false;
    }
    if (($posA=strrpos($author,'.'))!==false){
      $author=trim(substr($author,$posA+1));
      return Bibliography::getAuthorInitial($author,false);
    } elseif (($posA=strrpos($author,' '))!==false){
      $arr=explode(' ', $author);
      foreach($arr as $ka=>$a){$a=trim($a);
	if (($isFirst&&$ka<1)|| (strlen($a)<2)) continue; //||in_array($a,array("Le","De",... )
	return $a[0];
      }
    }
    return strtoupper($author[0]);
  }

} /*end of Bibliography class*/
 
/** Build a citation.
 *
 * This is just a link to {@link Bibliography::cite()} method
 * @param (String) a bibkey reference
 * @param (String) index where are located the reference page, and where the
 * 'bibKey' are binded
 * @return (String) anchor HTML markup
 */ 
function cite($ref,$index=BIB_INDEX){
  return Bibliography::bibkey($ref,$index);
}

if (!function_exists("__")){

/** Internationalisation function
 * @param (String) the string to translate
 * @return (String)
 */
  function __($str){
    return (!empty($GLOBALS['__l10n'][$str])) ? $GLOBALS['__l10n'][$str] : $str;
  }
 }

?>
