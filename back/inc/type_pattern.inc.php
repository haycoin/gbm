<?php
/**
 * General pattern used into GBM
 * 
 * @author Alec Avedisyan
 * @copyright 2017 Sparkou.com
 * @package GBM
 * @version 0.9.2
 * @subpackage Conceptual
 */

define("PATTERN_INT"			,'^[+-]?[0-9]*$');
define("PATTERN_ID"				,'^[0-9]+$');
define("PATTERN_FLOAT"			,'^[+-]?[0-9]+\.?[0-9]*$');
define("PATTERN_EMAIL"			,'^[a-zA-Z0-9]+[_a-zA-Z0-9-]*(\.[_a-z0-9-]+)*@[a-z?0-9]+(-[a-z?0-9]+)*(\.[a-z?0-9-]+)*(\.[a-z]{2,4})$');
define("PATTERN_PASSWORD"		,'^[a-zA-Z0-9_&=/+/-/*/(/)/?]{6,20}$'); // Minimum 6 caractères
define("PATTERN_TEXT"			, utf8_encode('^[-a-zA-Z0-9À-ÿ,./_& \+@/(/)]*$'));
define("PATTERN_TEXT_ONLY"		, utf8_encode('^[-a-zA-ZÀ-ÿ ]*$'));
define("PATTERN_TEXT_QUOTES"	, utf8_encode("^[-a-zA-Z0-9À-ÿ,./_& \+\!\?@/(/)\'\\\"\n\r]*$"));
define("PATTERN_DATE_CALENDAR"		,'^[0-9]{2}.[0-9]{2}.[0-9]{4}$');	// This tree are the same but must be differanciate 
define("PATTERN_DATE_CALENDAR_MONTH",'^[0-9]{2}.[0-9]{4}$');	// This tree are the same but must be differanciate 
define("PATTERN_DATE_CALENDAR_SPLIT",'^([0-9]{2}).([0-9]{2}).([0-9]{4})$');
define("PATTERN_DATE_CALENDAR_ANTE"	,'^[0-9]{2}.[0-9]{2}.[0-56-9]{4}$'); 
define("PATTERN_DATE_CALENDAR_POST"	,'^[0-9]{2}.[0-9]{2}.[0-45-9]{4}$');
define("PATTERN_DATE"			,'^([0-9]{0,2})[./-]([0-9]{0,2})[./-]([0-9]{4})$');
define("PATTERN_DATE_EPOCH"		,'^([0-9]{0,2})[/.-]([0-9]{0,2})[/.-]([0-9]{4})$'); // EXACTEMENT LA MEME CHOSE QUE AU DESSUS MAIS C UNE FEINTE AVEC L'inversment des points ... indispensable
define("PATTERN_ALL"			,'^[^\'"]*$'); // DO NOT USE not enought secure, if it's a encoding problem add header('Content-type: text/html; charset=iso-8859-1');
define("PATTERN_PHONE_INTERNATIONAL", '^[+]?[0-9]{6,20}$');


/**
 * power_trim()
 * Remove : SPACE / . , - ( AND )
 * 
 * @param mixed $chaine
 * @return string
 */
function power_trim($chaine){
	$chaine = trim($chaine);
	$chaine = str_replace('/','',$chaine);
	$chaine = str_replace('.','',$chaine);
	$chaine = str_replace(',','',$chaine);
	$chaine = str_replace('-','',$chaine);
	$chaine = str_replace(' ','',$chaine);
	$chaine = str_replace('(','',$chaine);
	$chaine = str_replace(')','',$chaine);
	return $chaine;
}

/**
 * power_trim_number()
 * Remove : SPACE , AND ' 
 * 
 * @param mixed $chaine
 * @return string
 */
function power_trim_number($chaine){
	$chaine = trim($chaine);
	$chaine = str_replace(',','',$chaine);
	$chaine = str_replace("'",'',$chaine);
	$chaine = str_replace(' ','',$chaine);
	return (float)$chaine;
}
