<?php
//
// +------------------------------------------------------------------------+
// | phpBIB
// | $Id: references.php,v 2.0 2005/07/22 08:58:54 dfolio Exp $
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
  /** phpBIB references pages
   * 
   * This script provide the main phpBIB output design.
   * Use {@link ) $BIB_BIBFILES} global variable to define the bibfile list to
   * include.
   *
   * You can include this page into your desired target page between the
   * <var><body></var> tag.
   * You may customize this file, or design your own inspired by this one.
   * 
   * Becarefull, this 'example' is mainly designed to be used with phpAntre, so
   * take a caution on how some link are build.
   *
   * @version   $Revision: 2.0 $
   * @author David FOLIO <dfolio@free.fr>
   * @copyright Copyright &copy; 2003-2005, Antre Team
   * @license http://dfolio.free.fr/license.txt
   *
   * @package phpBIB
   * @todo a search engine
   */


/** Need the {@link  BibtexParser "bibtex parser"} */
require_once(dirname(__FILE__)."/bibtexParser.php");
/** Need the {@link  Bibliography} */
require_once(dirname(__FILE__).'/bibliography.php');

if (!isset($BIB_BIBFILES))
  $BIB_BIBFILES=(isset($GLOBALS["BIB_BIBFILES"]))?$GLOBALS["BIB_BIBFILES"] :array(dirname(__FILE__)."/references.bib",);

/** back*/
if (!empty($_SERVER["HTTP_REFERER"]))
  $back_index='<a href="'.Bibliography::href($_SERVER["HTTP_REFERER"]).'" title="Back">&lt; '.__("Back")."</a>";
else
  $back_index='<a href="'.Bibliography::href('',array("index"=>'')).'" title="Back to references index">&lt; '.__("References index")."</a>";

/** get showBib query string or set to default  */
$showBib=isset($_REQUEST["showBib"])? true: (isset($GLOBALS["showBib"]) ? $GLOBALS["showBib"]:false);
/** get showSubItem query string or set to default */
$showSubItem=isset($_REQUEST["showSubItem"])? true: (isset($GLOBALS["showBib"]) ? $GLOBALS["showSubItem"]:true);
/** get sort query string or set to default */
$sort=isset($_REQUEST["sort"])? $_REQUEST["sort"]: "author";
//$useCache=BIB_USE_CACHE&&(!isset($_REQUEST["nocache"]));
$useCache=false;
/** Build the biblio */
$myBIB=new Bibliography($BIB_BIBFILES,$useCache);

if (isset($_REQUEST["biblio"])||isset($_REQUEST["sort"])){ 
  //display all bibliography entries
  echo ' <h2>'.__("Bibiography").": ".__("sort by")." $sort".'</h2> '."\n";
  echo '<p style="text-align:center;font-size:75%;">'.$myBIB->sortList("::")."</p>";
  echo $back_index."<br/>\n";
  echo  $myBIB->dump($sort, $showBib, true);

}elseif(isset($_REQUEST["bibkey"])){
  //display only the bibkey entry
  echo ' <h2>'.$_REQUEST["bibkey"].'</h2> '."\n";
  echo $back_index."<br/>\n";
  echo '  <p id="bibview">'.$myBIB->item2str($_REQUEST["bibkey"])
    .$myBIB->getAbstract($_REQUEST["bibkey"])
    .$myBIB->getKeywords($_REQUEST["bibkey"])."";
  //  if ($showBib)
  echo $myBIB->item2bib($_REQUEST["bibkey"]);
  echo "</p>\n";

}elseif(isset($_REQUEST["category"])){
  //display only the keyword entry
  echo ' <h2>'.__($_REQUEST["category"]).' </h2> '."\n";
  echo $back_index."<br/>\n";
  echo  $myBIB->categoryView($_REQUEST["category"], $showBib, true);

}elseif(isset($_REQUEST["year"])){
  //display only the keyword entry
  echo ' <h2>'.__("Publication of year").' &quot;'.$_REQUEST["year"].'&quot; </h2> '."\n";
  echo $back_index."<br/>\n";
  echo  $myBIB->yearView($_REQUEST["year"], $showBib, true);

}elseif(isset($_REQUEST["keyword"])){
  //display only the keyword entry
  echo ' <h2>'.__("Publication about").' &quot;'.$_REQUEST["keyword"].'&quot; </h2> '."\n";
  echo $back_index."<br/>\n";
  echo  $myBIB->keywordView($_REQUEST["keyword"], $showBib, true);

}elseif(isset($_REQUEST["author"])){
  //display authors
  if (empty($_REQUEST["author"])){
    echo ' <h2 id="author">'.__('Selection by author')." </h2>\n";
    echo $back_index."<br/>\n";
    echo  'Authors list: '.$myBIB->authorsList();  
  }/*elseif (strlen($_REQUEST["author"])<2){
    echo ' <h2 id="author">'.__('Selection by author initial').' &quot;'.$_REQUEST["author"]."&quot; </h2>\n";
    echo $back_index."<br/>\n";
    echo  'Authors list: '.$myBIB->authorsList($_REQUEST["author"]);  
  }*/else{
    echo ' <h2>'.__("Publication of").' &quot;'.$_REQUEST["author"].'&quot; </h2> '."\n";
    echo $back_index."<br/>\n";
    echo $myBIB->authorView($_REQUEST["author"], $showBib, true);
  }
}else{
 // default show index view
  //if(isset($_REQUEST["index"])){

  //reference index page
  echo ' <h1 id="index">'.__("Index").'</h1> '."\n";
 // echo '<div style="text-align:center;"><a href="'.Bibliography::href("biblio").'" title="View complete bibliography"> '.__("Complete Bibliography")."</a></div><br/>\n";
?>
    <ul id="index_menu">
       <li><a href="<?=Bibliography::href('',array("biblio"=>''))?>" title="View complete bibliography"><?=__("Complete Bibliography")?></a></li>
       <li><a href="<?=Bibliography::href('',array("index"=>''))?>#category" title="Selection by category"><?=__("Selection by category")?></a></li>
       <li><a href="<?=Bibliography::href('',array("index"=>''))?>#year" title="Selection by year"><?=__("Selection by year")?></a></li>
       <li><a href="<?=Bibliography::href('',array("index"=>''))?>#author" title="Selection by author"><?=__("Selection by author")?></a></li>
    </ul><br/>

<?php
  echo ' <h2 id="category">'.__('Selection by category')."</h2>\n";
  echo  $myBIB->categoriesList();
  echo ' <h2 id="year">'.__('Selection by year')."</h2>\n";
  echo  $myBIB->yearsList();
  echo ' <h2 id="keyword">'.__('Selection by keyword')."</h2>\n";
  echo  $myBIB->keywordsList();
  echo ' <h2 id="author">'.__('Selection by author')."</h2>\n";
  echo  'Alphabetical list: '.$myBIB->authorsList(true);
  echo '<a href="'.Bibliography::href('',array("author"=>'')).'" title="View complete authors list"> '.__("Complete authors list")."</a><br/>\n";

  /*echo "<!-- form test !--> \n";
  if (isset($_REQUEST["author"])) echo "<!-- Request ".$_REQUEST["author"]." -->  \n";
  if (isset($_REQUEST["qauthor"])) echo "<!-- REQUEST, q ".$_REQUEST["qauthor"]." -->  \n";
  if (isset($_POST["qauthor"])) echo "<!-- POST:".$_POST["qauthor"]." -->  \n";
  */
?>
<!--  
  <p>
       <form action="<?=Bibliography::href() ?>" method="post">
         <label for="bibf_search_author"><?= __('Query author') ?>: </label>
         <input name="qauthor" id="bibf_search_author" type="text" maxlength="256" value="<?= isset($_REQUEST["author"])?$_REQUEST["author"]:"" ?>" />
         <input type="submit" value="<?= __('ok'); ?>" />
       </form>
    </p>
    <p class="comment">
       <ul>
         <li>Use can use partial author query.<br/>
             Exemple: query &quot;thor&quot;, for &quot;Author&quot;.
         </li>
         <li>Use  &quot;OR&quot; (use capital) to search several authors publication.<br/>
             Exemple: Author1 OR Author2
	 </li>
       </ul>
    </p>

-->
<?php
}//end :default show index view

// now don't need biblio any more
//unset($myBIB);
$myBIB->destroy(!$useCache);
/*disclaimer*/
//echo '<h2 id="disclaimer">'. __("Disclaimer").'</h2>';
//echo __("disclaimer_contents");
?>

<br/><hr/>
<p style="font-size:85%;padding:4px;text-align:center">
<a href="http://dfolio.free.fr/phpBIB" title="phpBIB"><img src="<?= BIB_ICONS_PATH?>/phpBIB.png" alt="phpBIB powered"/></a><br/>
This document are automaticaly generated from a BibTeX file and powered by  <a href="http://dfolio.free.fr/phpBIB" title="phpBIB">phpBIB</a>.
</p>
