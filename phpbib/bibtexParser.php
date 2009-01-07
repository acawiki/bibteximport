<?php
//
// +------------------------------------------------------------------------+
// | phpBIB
// | $Id: bibtexParser.php,v 2.0 2005/07/22 08:58:54 dfolio Exp $
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
  /** BibTeX file parser
   * 
   * This script provide a bibTeX parser, and several method to render
   * bibliography. 
   *
   * It's provide a support for any categoy starting by '@'.
   * Exception for the special case <var>@STRING</var> which define a bibtex
   * constant. Nevertheless, if the category, or any fields are not common in
   * bibtex, they are not rendering when the bibliography is build...
   * The most common bibtex category :
   *  - @ARTICLE 
   *       {@link Bibliography::buildArticle()}
   *  - @BOOK, @INBOOK, @BOOKLET 
   *       {@link Bibliography::buildBook()}
   *  - @PROCEEDINGS, @INCOLLECTION, @CONFERENCE, @INPROCEEDINGS
   *       {@link Bibliography::buildInProceedings()}
   *  - @MASTERSTHESIS, @PHDTHESIS
   *	   {@link Bibliography::buildThesis()}}
   *  - @MANUAL, @AUDIOVISUAL, @TECHREPORT
   *       {@link Bibliography::buildReport()}}
   *  - @MISC, @WEBPAGE
   *  - @UNPUBLISHED, and other: not yet supported...
   *
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
   *
   * @todo define some other index such as author, year...
   */

if (!defined("BIB_PARSE_DEBUG")) define("BIB_PARSE_DEBUG",false);
/** An array which contains field to exclude (must be in lower case)*/
if (!isset($BIB_EXCLUDES_FIELD)) $BIB_EXCLUDES_FIELD=array();

/** Class  BibTexParser: BibTeX file parser
 *
 * This class provide a bibTeX file parser and several methods for bibliography
 * management. 
 *
 * This class was designed to be called statically. In fact you don't need to
 * define an object...
 *
 * @author David FOLIO
 * @version 1.0
 * @package phpBIB
 */
class BibTexParser{
  /** The bibliography data
   *
   * This is an array which contains bibliography entry
   * @var Array
   */
  var $files;
  var $bibArr=array();
  /** define if cache is enabled
   * @var Boolean
   * @access private
   */
  var $_useCache=false;
   /** The constructor
   * @param $bibfiles a list of bibTeX file to load
   * @param $useCache (dis)enable the cache management
   */
  function BibTexParser($bibfiles,$useCache=false){
    $this->bibArr=array();
    $this->files=$bibfiles;
    $this->_useCache=$useCache;    
    $this->load($this->files,$useCache);
  }
  /** Define the biblio data entry
   * @param Array the biblio data entry, which is build from BibtexParser
   * @see {@link BibtexParser}
   */
  function setBibData($biblio){
    if (!is_array($biblio)){
      trigger_error("BibTexParser::setBiblio > Bad parameter biblio=".
		    print_r($biblio,true).
		    " which must be an array of  biblio data entry\n", 
		    E_USER_ERROR); 
       return false;
    }
    $this->bibArr=$biblio;
  }
  /** Get the biblio data entry
   * @return Array the biblio data entry array
   */
  function getBibData(){return $this->bibArr;}

  /** Load and parse bibfiles
   * @param Mixed could be a single bibfile, or an array of bibfiles.
   * @param Boolean  (dis)enable the cache management
   */
  function load($bibfiles,$useCache=false){    
    $this->_useCache=$useCache;
    if ((!$this->_useCache) &&(isset($_SESSION["PHPBIB_BIBLIO"]))){ 
      unset($_SESSION["PHPBIB_BIBLIO"]);
    }elseif ($this->_useCache&& isset($_SESSION["PHPBIB_BIBLIO"])){
      if (BIB_PARSE_DEBUG) echo "<!-- load bib from cache-->";
      $this->bibArr=$_SESSION["PHPBIB_BIBLIO"];
      return true;
    }
    if (is_string($bibfiles)) $bibfiles=array($bibfiles);
    if (!is_array($bibfiles)){
      trigger_error("BibTexParser::load > Bad parameter bibfiles=".
		    print_r($bibfiles,true).
		    " which must be an array of string bibfile name or just a  string bibfile name\n", 
		    E_USER_ERROR); 
       return false;
    }
    $this->bibArr=BibTexParser::parse($bibfiles);
    if ($this->_useCache) {$_SESSION["PHPBIB_BIBLIO"]=$this->bibArr;  }
    return (true);
  }
  /** Save the biblio to a file
   * @param String $filename the file where to store the given biblio
   * @param  Array the biblio data entry to save
   */
  static function write($filename,$biblio=null){
    return @file_put_contents($filename, serialize($biblio));
  }
  /** Read the biblio to a file
   * @param String $filename the file where to store the given biblio
   * @param  Array the biblio data entry to save
   */
  static function read($filename){
    return @unserialize(file_get_contents($filename));
  }
  /** Delete the current biblio. 
   * If there the cache is enable, also remove the biblio from the cache.
   * 
   * @param Boolean  (dis)enable the cache management  
   */
  function destroy($useCache=false){
    if  ($useCache&&isset($_SESSION["PHPBIB_BIBLIO"])) unset($_SESSION["PHPBIB_BIBLIO"]);
    unset($this->bibArr);
    //$this=NULL;
    return true;
  }


  /** Parse bibfiles
   *
   * Here is the main parser method. This function can parse a single
   * bibTeX file or several defined in an array.
   * This function return the parser result in an array like:
   * <pre>
   * array(
   *       'category'=>array('bibkey'=>'the corresponding category',...),
   *       'a field'=>array('bibkey'=>'the corresponding field',...),
   *        ...
   *      );
   * </pre>
   *
   * All field (excepted the specified excluded one) are added in the resulting
   * array.
   *
   * If an error occur during the parse (like open bibfile fail) the function
   * return <var>FALSE</var>. 
   * @param Mixed bibfiles. 
   * An array (or a string for single) of bibfile (with their path!)
   * @return Mixed
   *
   */
  function parse($bibfiles,$excludeFields=null){
    global $BIB_EXCLUDES_FIELD;
    if (!isset($BIB_EXCLUDES_FIELD)) $BIB_EXCLUDES_FIELD=array();
    if (empty($excludeFields)) $excludeFields=$BIB_EXCLUDES_FIELD;

    if (!is_array($bibfiles)) $bibfiles=array($bibfiles);
    //init
    $count=-1;$cst_count=0;$bibkey=false;$unclose_field=false;$current_fieldname=$current_fieldval=false;
    $arFields=$item=$fieldvalue=$fieldname=$cst_reg=$cval_reg=array();
    foreach($bibfiles as $bibfile){
      if(!(file_exists($bibfile))){
	trigger_error("[".__CLASS__."::parse] bibfile $bibfile does not exists\n",E_USER_ERROR);
	return false;
      }
      //get file lines 
      $lines = file ($bibfile);
      $currentFields="";//if no empty: multi-lines fields
      $fieldVal="";
      foreach ($lines as $lineindex => $line) {
	$seg=trim($line);$beginField=false;$fieldVal="";
	if (empty($seg)||($seg=='\0')||($seg=='\n')||$seg[0]=='%') continue;
	$segupper=strtoupper($seg);
	
	//constant 
	if (strpos($segupper,'@STRING')!==false) {
	  list($cst,$cval)=BibtexParser::bibstring($seg);
	  $cst_reg[$cst_count]='/'.$cst.'\s*#?\s*(.*)/';
	  $cval_reg[$cst_count]=''.trim($cval).' $1';$cst_count++;
	  continue;
	}elseif (preg_match('/@(.*) { (.*)/x', $seg,$matches)>0){/*get category*/
	  $entry=trim($matches[1]);$rest=trim($matches[2]);
	  if (empty($rest)||($rest=='\0')||($rest=='\n')||$rest=='%') {$bibkey=false;continue;}
	  $bibkey=trim($rest,"\t\r\n\x0B\x20 ,");
	  $item["category"][$bibkey]=strtoupper($entry);
	  $count++;$fieldcount=-1;
	  continue;
	} // #of item increase
	elseif ((strpos($seg,'='))!==false ){ // one field begins
	  $beginField=true;
	  if (empty($bibkey)){
	    trigger_error("[".__CLASS__."::parse] Malformed bibfile no bibkey in used\n".
			  "at line $lineindex in $bibfile\n Segment:".$seg."\n",E_USER_ERROR);
	    return false;
	  }
	  if (preg_match('/(url|pdf|ps)\s*=\s*(.*)/', $seg,$matches)){
	    //print_r($matches);
	    $currentFields=strtolower(trim($matches[1]));
            if (!empty($excludeFields)&&in_array($currentFields,$excludeFields)) continue;
	    $fieldVal=trim($matches[2],"\t\r\n\x0B\x20 ,;\"{}");
	    $item[$currentFields][$bibkey]=$fieldVal;
	  }else{
	    if (preg_match('/(.*)\s*=\s*(.*)/', $seg,$matches)<1){
	      if (BIB_PARSE_DEBUG) echo "<p>At line $lineindex in $bibfile\n Segment:".$seg."</p>"; 
	      continue;
	    }else{
	      $currentFields=strtolower(trim($matches[1]));
              if (!empty($excludeFields)&&in_array($currentFields,$excludeFields)) continue;
	      $fieldVal=preg_replace($cst_reg,$cval_reg,trim($matches[2],"\t\r\n\x0B\x20 ,;\"{}"));
	      $haveConst=(trim($fieldVal)!==trim($matches[2]));
	      $item[$currentFields][$bibkey]=trim($fieldVal,"\t\r\n\x0B\x20 ,;\"{}");
	    }
	  }
	}
	//multi-lines fields
	if ((!$beginField)&&!empty($currentFields)&&(!empty($bibkey))&&isset($item[$currentFields][$bibkey])){	  
	  $fieldVal=trim($seg,"\t\r\n\x0B\x20 \"{}");
	  if (!empty($fieldVal)) $item[$currentFields][$bibkey].= " ".$fieldVal;
	  if (preg_match('/("|});?/',$seg,$matches)){$item[$currentFields][$bibkey]=trim($item[$currentFields][$bibkey],"\t\r\n\x0B\x20 ,;\"{}"); $currentFields="";}
	}else if (isset($item[$currentFields][$bibkey]))
	  $item[$currentFields][$bibkey]=trim($item[$currentFields][$bibkey],"\t\r\n\x0B\x20 ,\"{}");
      }//end foreach lines
    }//end foreach bibfiles
    foreach ($item as $fn=>$fields) {
      foreach ($fields as $key=>$val) {
        /*if (strpos($val,'$')!=false){ 
          $mvArr= preg_split('/\$/', $val);$val='';
          for($i=1; $i<count($mvArr);$i+=2){
            $val.=$mvArr[$i-1].' '.BibtexParser::bibMath($mvArr[$i]);
          }
        }*/
        $val=trim($val,"\t\r\n\x0B\x20 ,\"{}");
        $item[$fn][$key]=BibtexParser::strtr(str_replace(array('"','{','}','$'),'',$val));
        //echo "<!--[$fn] [$key]: ".$item[$fn][$key]." // $val -->\n";   
      }
    }
/*
elseif (isset($item[$currentFields][$bibkey])){
*/
    return $item;
  }
  /** Parse a <var>@STRING</var> (bibTeX constant defintion)
   *
   * This function parse a bibTeX constant <var>@STRING</var>, and then provide
   * an array with the constant name and the constant value:
   * <pre>arrray(const_name, const_value)</pre>.
   * If the specified string does not match a bibTeX constant
   * <var>@STRING</var>, the function return <var>FALSE</var>.
   * @param (String) the string to parse
   * @return (Mixed)
   */
  function bibstring($str){
    if(preg_match('/@string{(.*)=(.*)}/i',$str,$matches))
      if (count($matches)>2) return array(trim($matches[1]),trim($matches[2]));
    return false;
  } 
  /** Simple math translation*/
  function bibMath($str){
    if (empty($str)) return '';
    if(strpos($str,'_')!==false) $str=preg_replace('/\$(.*)_(.*)\$/','<sub>$1</sub>',$str);
    if(strpos($str,'^')!==false) $str=preg_replace('/^(.*)\s+/','<sup>$1</sup>',$str);
    return str_replace('$','|',$str);
  }
  /** Translate (la)TeX character to HTML entities
   *
   * This function returns a copy of str, translating all occurrences of each
   * character according a <var>$map</var> array. 
   * The map array it's an array  like:
   * <pre>
   * array("from latex"=>"to HTML markup",...)
   * </pre>
   *
   * This function is useful to translate user-supplied (la)Tex command to HTML
   * markup. But beacrefull, this function replace only (la)TeX command without
   * arguments! 
   * @param (String) the string to translate.
   * @param (Array)  additionnal  <var>$map</var>. Use '-1' to desactivate the
   * call to {@link  getLatexTranslationTable} and to not use
   * {@link get_html_translation_table} 
   * @return (String)
   */
  function strtr($str,$map=array()){
    if ($map!=-1){
      if (!is_array($map)) $map=array();
      $map=array_merge(BibTexParser::getLatexTranslationTable(),$map);
      $map= array_merge(get_html_translation_table(HTML_ENTITIES),$map);
    }
    if (is_array($map))  return @strtr($str, $map);
    return $str;
  }
  /**Returns the translation table used by {@link strtr}
   * @return (Array)
   */
  function getLatexTranslationTable(){
    $map=array();
    $map['=']=''; $map['{']=''; $map['}']=''; $map['\"']=' ';//clean bibkey
    $map['~']='&nbsp;';        $map['\\~']='~';
    $map['\\`a']='&agrave;';   $map['\\`A']='&Agrave;';
    $map['\\`e']='&egrave;';   $map['\\`E']='&Egrave;';
    $map['\\`\\i']='&igrave;'; $map['\\`\\I']='&Igrave;';
    $map['\\`o']='&ograve;';   $map['\\`O']='&Ograve;';
    $map['\\`u']='&ugrave;';   $map['\\`U']='&Ugrave;';
    $map['\\\'a']='&aacute;';  $map['\\\'A']='&Aacute;';
    $map['\\\'e']='&eacute;';  $map['\\\'E']='&Eacute;';
    $map['\\\'\\i']='&iacute;';$map['\\\'\\I']='&Iacute;';
    $map['\\\'o']='&oacute;';  $map['\\\'O']='&Oacute;';
    $map['\\\'u']='&uacute;';  $map['\\\'U']='&Uacute;';
    $map['\\^a']='&acirc;';    $map['\\^A']='&Acirc;';
    $map['\\^e']='&ecirc;';    $map['\\^E']='&Ecirc;';
    $map['\\^\\i']='&icirc;';  $map['\\^\\I']='&Icirc;';
    $map['\\^o']='&ocirc;';    $map['\\^O']='&Ocirc;';	
    $map['\\^u']='&ucirc;';    $map['\\^U']='&Ucirc;';	
    $map['\\:a']='&auml;';   $map['\\:A']='&Auml;'; 
    $map['\\:e']='&euml;';   $map['\\:E']='&Euml;'; 
    $map['\\:i']='&iuml;';   $map['\\:I']='&Iuml;';
    $map['\\:o']='&ouml;';   $map['\\:O']='&Ouml;'; 
    $map['\\:u']='&uuml;';   $map['\\:U']='&Uuml;'; 
    $map['\\oe']='&oelig;';   $map['\\OE']='&OElig;'; 
    //bind some (la)Tex command ...
    $map['\\TeX']='TeX';$map['\\LaTeX']='LaTeX';
    $map['\\dots']='&hellip;';      $map['\\ldots']='...';
    $map['--']='&ndash;';$map['---']='&mdash;';
    //bind some mathematical command
    $map['\\infty']='&infin;';
    return $map;
  }
}

?>
