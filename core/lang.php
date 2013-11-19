<?php
/**
 * LibreQuotes / Core / Lang
 */

namespace LibreQuotes;

/** @var string */
const LANGS_FILE_PATH = 'lang/';

/** @var string[] */
$definedLanguages = array('en' => 'English',
                          'fr' => 'French');

/**
 * Translate the text in the user language, or returns the text if it fails.
 * @param string $text
 * @return string
 */
function L($text)
{
    global $sentences;

    return array_key_exists($text, $sentences) ? $sentences[$text] : $text;
}

/**
 * Define client language using cookie and http header "HTTP_ACCEPT_LANGUAGE".
 */
function defineClientLanguage()
{
    global $lang;

    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {

        $langs = (empty($_COOKIE['lang'])) ?
                     explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) :
                     array($_COOKIE['lang']);

        for ($i = 0, $j = sizeof($langs); $i < $j; $i++) {

            $language = substr($langs[$i], 0, 2);

            if (file_exists(LANGS_FILE_PATH . $language . '.php')) {

                $lang = $language;
                break;
            }
        }
    }

    include_once LANGS_FILE_PATH . $lang . '.php';
}
