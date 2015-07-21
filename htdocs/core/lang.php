<?php

/** Get the best matching language **/
function getBestMatchingLanguage(){
	$langs = " ".(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])?$_SERVER['HTTP_ACCEPT_LANGUAGE']:"en");
	$languages = array(
		'en',
		'de',
	);
    foreach($languages as $code) {
        $pos = strpos($langs, $code);
        if(intval($pos) != 0) {
            $position[$code] = intval($pos);
        }
    }
    $lang = 'en';
    if(!empty($position)) {
        foreach($languages as $code) {
            if(isset($position[$code]) &&
               $position[$code] == min($position)) {
                    $lang = $code;
            }
        }
    }
    return $lang;
}
/** gen localization string **/
function getLang($lang){
	
	$locale = "en_US";
	
	switch ($lang) {
		case "de":
			$locale = "de_DE";
			break;
		case "ch":
			$locale = "de_DE";
			break;
		case "en":
			$locale = "en_US";
			break;
		default:
			$locale = "en_US";
			break;
	}
	return $locale.".utf8";
}
/** set gettext lang */
function setLang($locale, $domain, $encoding){
	putenv("LC_ALL=".$locale);
	setlocale(LC_ALL, $locale);
	bindtextdomain($domain, CORE_DIR."lang/");
	bind_textdomain_codeset($domain, $encoding);
	textdomain($domain);
}

global $lang;

$lang = getLang(getBestMatchingLanguage());
setLang($lang, "grades", "UTF-8");

?>