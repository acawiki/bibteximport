<?php
//
// +------------------------------------------------------------------------+
// | phpBIB
// | $Id: constants.php,v 2.0 2005/07/22 08:58:54 dfolio Exp $
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
  /** phpBIB constant/configuration
   * 
   * This script provide the main phpBIB constant.
   * You may customize some constants value to your own purpose.
   *
   * @version   $Revision: 2.0 $
   * @author David FOLIO <dfolio@free.fr>
   * @copyright Copyright &copy; 2003-2005, Antre Team
   * @license http://dfolio.free.fr/license.txt
   *
   * @package phpBIB
   */
/*Define _df_ function if not defined...*/
if (!function_exists('_df_')){
  function _df_($cname,$cval=null){
    if (!defined($cname)) define($cname,$cval);
  }
}
/** (Dis)Enable bibtex parser debug message*/
_df_("BIB_PARSE_DEBUG",true);

if (!defined("BIB_INDEX")) define("BIB_INDEX","references.php");
if (!defined("BIB_USE_CACHE")) define("BIB_USE_CACHE",true);

if (!isset($BIB_BIBFILES))
/**
 * This global variable define the bibfiles list to parse, required in {@link references.php}
 * @global (array) $GLOBALS["BIB_BIBFILES"]
 * @name $BIB_BIBFILES 
*/
  $BIB_BIBFILES= (isset($GLOBALS["BIB_BIBFILES"])) ? $GLOBALS["BIB_BIBFILES"]: array(dirname(__FILE__).'/references.bib');

_df_("BIB_DEFAULT_LANG","fr");
_df_("BIB_ICONS_PATH","images");

/**#@+
 * Item rendering constant
 * You may customize as your own purpose. You may also want to modify the
 * corresponding cascading style sheet file.
 */
/** Bibliography start environment*/
//_df_("BIB_BIBLIO_START",'<dl id="biblio">');
/** Bibliography end environment*/
//_df_("BIB_BIBLIO_END",'</dl>');
/**  Bibliography start environment*/
_df_("BIB_VIEW_START",'<dl id="bibView">');
/** Bibliography end environment*/
_df_("BIB_VIEW_END",'</dl>');
/** Bibitem start environment*/
_df_("BIB_BIBITEM_START",'<dt id="%s">');
/** Bibitem syart environment*/
_df_("BIB_SUBITEM_START",'<dd>');
/**#@+ 
 * Subitem is used for not common item fields, such as 'abstract',
 * 'keywords'... which are usefull here!
 */
/** Subitem end environment*/
_df_("BIB_SUBITEM_END",'</dd>');
/** Subitem end environment*/
_df_("BIB_BIBITEM_END",'</dt>');
/**#@-*/
/** Bibentry start environment*/
_df_("BIB_BIBENTRY_START",'<p class="bibEntry">');
/** Bibentry end environment*/
_df_("BIB_BIBENTRY_END",'</p>');

/** Bibentry start environment*/
_df_("BIB_ABSTRACT_START",'<p class="bibAbstract">');
/** Bibentry end environment*/
_df_("BIB_ABSTRACT_END",'</p>');

/** Bibentry start environment*/
_df_("BIB_KEYWORDS_START",'<p class="bibKeywords">');
/** Bibentry end environment*/
_df_("BIB_KEYWORDS_END",'</p>');

/** Bibkey start fields*/
_df_("BIB_BIBKEY_START",'<span class="bibkey">');
/** Bibkey start fields*/
_df_("BIB_BIBKEY_END",'</span><br/>'."\n&nbsp;");
/** Author start fields*/
_df_("BIB_AUTHOR_START",'<span class="bibAuthor">');
/** Author end fields*/
_df_("BIB_AUTHOR_END",'</span>');
/** Title start fields*/
_df_("BIB_TITLE_START",'<span class="bibTitle">&quot;');
/** Title end fields*/
_df_("BIB_TITLE_END",'&quot;</span>. ');
/** Date start fields*/
_df_("BIB_DATE_END",'</span>');
/** Date end fields*/
_df_("BIB_DATE_START",'<span class="bibDate">');
/** Booktitle start fields*/
_df_("BIB_BOOKTITLE_START",'<span class="bibBooktitle">');
/** Booktitle end fields*/
_df_("BIB_BOOKTITLE_END",'</span>');
/** Journal start fields*/
_df_("BIB_JOURNAL_START",'<span class="bibJournal">');
/** Journal end fields*/
_df_("BIB_JOURNAL_END",'</span>');
/** Publisher start fields*/
_df_("BIB_PUBLISHER_START",'<span class="bibPublisher">');
/** Publisher end fields*/
_df_("BIB_PUBLISHER_END",'</span>');
/** School start fields*/
_df_("BIB_SCHOOL_START",'<span class="bibSchool">');
/** School end fields*/
_df_("BIB_SCHOOL_END",'.</span>');
/** Institution start fields*/
_df_("BIB_INSTITUTION_START",'<span class="bibInstitution">');
/** Institution end fields*/
_df_("BIB_INSTITUTION_END",'</span>');
/** Pages start fields*/
_df_("BIB_PAGES_START",'');
/** Pages end fields*/
_df_("BIB_PAGES_END",'');
/** Address start fields*/
_df_("BIB_ADDRESS_START",'');
/** Address end fields*/
_df_("BIB_ADDRESS_END",'');
/** Web part start*/
_df_("BIB_WEB_START",'<div class="bibWeb">');
/** Web part start*/
_df_("BIB_WEB_END",'</div>');
/** URL partern*/
_df_("BIB_URL_PATERN",'<span class="bibURI"><a href="%s" title="Visite the URL">%s</a></span>');
/** BIB Icons partern*/
_df_("BIB_ICONS_START",' &nbsp;<span class="bibIcons">');
/** BIB Icons partern*/
_df_("BIB_ICONS_END",'</span>');
/** BIB Icons partern*/
_df_("BIB_ICONS_IMG_PATERN",'<img src="'.BIB_ICONS_PATH.'/bibTeX.png" alt="BIB:%s"/>');
/** DOI partern*/
_df_("BIB_DOI_PATERN",'<span class="bibDOI"><a href="http://dx.doi.org/%s" title="DOI:%s"><img src="'.BIB_ICONS_PATH.'/doi.gif" alt="DOI:%s"/></a></span>');

/** Annote part start*/
_df_("BIB_ANNOTE_START",'<div class="bibAnnote">');
/** Annote part start*/
_df_("BIB_ANNOTE_END",'</div>');
/** ISBN part start*/
_df_("BIB_ISBN_START",'<span class="bibISBN">');
/** ISBN part start*/
_df_("BIB_ISBN_END",'</span>');
/**#@- */

/** Define a comma separate list of view. */
_df_("BIB_VIEW_LIST","index,biblio,author,keyword,bibkey,year,category");

/** Categories list start fields*/
_df_("BIB_CATEGORIES_LIST_START",'<ul id="BIBcategoriesList">');
/** Categories list end fields*/
_df_("BIB_CATEGORIES_LIST_END",'</ul>');
/** Years list start fields*/
_df_("BIB_YEARS_LIST_START",'<ul id="BIByearsList">');
/** Years list end fields*/
_df_("BIB_YEARS_LIST_END",'</ul>');
/** Keywords list start fields*/
_df_("BIB_KEYWORDS_LIST_START",'<ul id="BIBkeywordsList">');
/** Keywords list end fields*/
_df_("BIB_KEYWORDS_LIST_END",'</ul>');
/** Authors list start fields*/
_df_("BIB_AUTHORS_LIST_START",'<ul id="BIBauthorsList">');
/** Authors list end fields*/
_df_("BIB_AUTHORS_LIST_END",'</ul>');



/** Define a comma separate list of sort cartegory.
 * All category prefixed by 'r' _df the "reverse" sort.
 */
_df_("BIB_SORT_LIST","category,year,author,rcategory,ryear,rauthor");

?>