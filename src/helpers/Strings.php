<?php
/**
 * Copyright 2014-18 Bryan Selner
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */


/******************************************************************************
 *
 *
 *
 *
 *  String-Related Utils
 *
 *
 *
 *
 *
 ******************************************************************************/


/**
 * Strip punctuation from text.
 * http://nadeausoftware.com/articles/2007/9/php_tip_how_strip_punctuation_characters_web_page
 * @param $text
 * @return mixed
 */
function strip_punctuation( $text )
{
    $urlbrackets    = '\[\]\(\)';
    $urlspacebefore = ':;\'_\*%@&?!' . $urlbrackets;
    $urlspaceafter  = '\.,:;\'\-_\*@&\/\\\\\?!#' . $urlbrackets;
    $urlall         = '\.,:;\'\-_\*%@&\/\\\\\?!#' . $urlbrackets;

    $specialquotes  = '\'"\*<>';

    $fullstop       = '\x{002E}\x{FE52}\x{FF0E}';
    $comma          = '\x{002C}\x{FE50}\x{FF0C}';
    $arabsep        = '\x{066B}\x{066C}';
    $numseparators  = $fullstop . $comma . $arabsep;

    $numbersign     = '\x{0023}\x{FE5F}\x{FF03}';
    $percent        = '\x{066A}\x{0025}\x{066A}\x{FE6A}\x{FF05}\x{2030}\x{2031}';
    $prime          = '\x{2032}\x{2033}\x{2034}\x{2057}';
    $nummodifiers   = $numbersign . $percent . $prime;

    return preg_replace(
        array(
            // Remove separator, control, formatting, surrogate,
            // open/close quotes.
            '/[\p{Z}\p{Cc}\p{Cf}\p{Cs}\p{Pi}\p{Pf}]/u',
            // Remove other punctuation except special cases
            '/\p{Po}(?<![' . $specialquotes .
            $numseparators . $urlall . $nummodifiers . '])/u',
            // Remove non-URL open/close brackets, except URL brackets.
            '/[\p{Ps}\p{Pe}](?<![' . $urlbrackets . '])/u',
            // Remove special quotes, dashes, connectors, number
            // separators, and URL characters followed by a space
            '/[' . $specialquotes . $numseparators . $urlspaceafter .
            '\p{Pd}\p{Pc}]+((?= )|$)/u',
            // Remove special quotes, connectors, and URL characters
            // preceded by a space
            '/((?<= )|^)[' . $specialquotes . $urlspacebefore . '\p{Pc}]+/u',
            // Remove dashes preceded by a space, but not followed by a number
            '/((?<= )|^)\p{Pd}+(?![\p{N}\p{Sc}])/u',
            // Remove consecutive spaces
            '/ +/',
        ),
        ' ',
        $text );
}



function isValueURLEncoded($str)
{
    if (strlen($str) <= 0) return 0;
    return (substr_count_array($str, array("%22", "&", "=", "+", "-", "%7C", "%3C")) > 0);
}


function remove_prefix($text, $prefix) {
    if(0 === strpos($text, $prefix))
        $text = substr($text, strlen($prefix)).'';
    return $text;
}

function remove_postfix($text, $postfix) {
    if(substr($text, strlen($text) - strlen($postfix)) === $postfix)
        $text = substr($text, 0, (strlen($text)-strlen($postfix)));
    return $text;
}

function cleanupTextValue($v, $prefixRemove=null, $postfixRemove=null)
{
    if(empty($v)|| !is_string($v))
        return $v;

    if(!empty($prefixRemove))
        $v = remove_prefix($v, $prefixRemove);

    if(!empty($postfixRemove))
        $v = remove_postfix($v, $postfixRemove);

    $v = html_entity_decode($v);
    $v = preg_replace(array('/\s{2,}/', '/[\t]/', '/[\n]/', '/\s{1,}/'), ' ', $v);
    $v = clean_utf8($v);
    $v = trim($v);

    if(empty($v))
        $v = null;

    return $v;
}


/**
 * Cleanup a string to make a slug of it
 * Removes special characters, replaces blanks with a separator, and trim it
 *
 * @param     string $slug        the text to slugify
 * @param     string $replacement the separator used by slug
 * @return    string               the slugified text
 */
function cleanupSlugPart($slug, $replacement = '-')
{
	$slug = clean_utf8($slug);

    // transliterate
    $slug = mb_convert_encoding($slug, "ASCII", "auto");

    // lowercase
    if (function_exists('mb_strtolower')) {
        $slug = mb_strtolower($slug);
    } else {
        $slug = strtolower($slug);
    }

    // remove accents resulting from OSX's iconv
    $slug = str_replace(array('\'', '`', '^'), '', $slug);

    // replace non letter or digits with separator
    $slug = preg_replace('/\W+/', $replacement, $slug);

    // trim
    $slug = trim($slug, $replacement);

    if (empty($slug)) {
        return 'UNKNOWN';
    }

    return $slug;
}


function getTodayAsString($delim = "-")
{
    $fmt = "Y" . $delim . "m" . $delim . "d";
    return date($fmt);
}

function getNowAsString($delim = "-")
{
    $fmt = join($delim, array("%Y", "%m", "%d", "%H", "%M", "%S"));
    return strftime($fmt, time());
}



define('REMOVE_PUNCT', 0x001);
define('LOWERCASE', 0x002);
define('HTML_DECODE', 0x004);
define('URL_ENCODE', 0x008);
define('REPLACE_SPACES_WITH_HYPHENS', 0x010);
define('REMOVE_EXTRA_WHITESPACE', 0x020);
define('REMOVE_ALL_SPACES', 0x040);
define('SIMPLE_TEXT_CLEANUP', HTML_DECODE | REMOVE_EXTRA_WHITESPACE );
define('ADVANCED_TEXT_CLEANUP', HTML_DECODE | REMOVE_EXTRA_WHITESPACE | REMOVE_PUNCT );
define('FOR_LOOKUP_VALUE_MATCHING', REMOVE_PUNCT | LOWERCASE | HTML_DECODE | REMOVE_EXTRA_WHITESPACE | REMOVE_ALL_SPACES );
define('DEFAULT_SCRUB', REMOVE_PUNCT | HTML_DECODE | LOWERCASE | REMOVE_EXTRA_WHITESPACE );

//And so on, 0x8, 0x10, 0x20, 0x40, 0x80, 0x100, 0x200, 0x400, 0x800 etc..


function strScrub($str, $flags = null)
{
    if($flags == null)  $flags = REMOVE_EXTRA_WHITESPACE;

    if(strlen($str) == 0) return $str;

    // If this isn't a valid string we can process,
    // log a warning and return the value back to the caller untouched.
    //
    if($str == null || !isset($str) || !is_string($str))
    {
        LogWarning("strScrub was called with an invalid value to scrub (not a string, null, or similar.  Cannot scrub the passed value: " . var_export($str, true));
        return $str;
    }

    $ret = $str;


    if ($flags & HTML_DECODE)
    {
        $ret = html_entity_decode($ret);
    }

    if ($flags & REMOVE_PUNCT)  // has to come after HTML_DECODE
    {
        $ret = strip_punctuation($ret);
    }

    if ($flags & REMOVE_ALL_SPACES)
    {
        $ret = trim($ret);
        if($ret != null)
        {
            $ret  = str_replace(" ", "", $ret);
        }
    }

    if ($flags & REMOVE_EXTRA_WHITESPACE)
    {
        $ret = trim($ret);
        if($ret != null)
        {
            $ret  = str_replace(array("   ", "  ", "    "), " ", $ret);
            $ret  = str_replace(array("   ", "  ", "    "), " ", $ret);
        }
        $ret = trim($ret);
    }


    if ($flags & REPLACE_SPACES_WITH_HYPHENS) // has to come after REMOVE_EXTRA_WHITESPACE
    {
        $ret  = str_replace(" ", "-", $ret); // do it twice to catch the multiples
    }


    if ($flags & LOWERCASE)
    {
        $ret = strtolower($ret);
    }

    if ($flags & URL_ENCODE)
    {
        $ret  = urlencode($ret);
    }

    return $ret;
}

function intceil($number)
{
    if(is_string($number)) $number = floatval($number);

    $ret = ( is_numeric($number) ) ? ceil($number) : false;
    if ($ret != false) $ret = intval($ret);

    return $ret;
}


function clean_utf8($string, $control = true)
{
    $string = iconv('UTF-8', 'UTF-8//IGNORE', $string);

    if ($control === true) {
        return preg_replace('~\p{C}+~u', '', $string);
    }

    return preg_replace(array('~\r\n?~', '~[^\P{C}\t\n]+~u'), array("\n", ''), $string);
}


function replaceTokensInString($formatString, $arrVariables)
{
//    $variables = array("first_name"=>"John","last_name"=>"Smith","status"=>"won");
//    $string = 'Dear {FIRST_NAME} {LAST_NAME}, we wanted to tell you that you {STATUS} the competition.';
    $ret = $formatString;
    foreach($arrVariables as $key => $value){
	    $ret = str_replace('{'.$key.'}', $value, $ret);
	    $ret = str_replace('{'.strtoupper($key).'}', $value, $ret);
	    $ret = str_replace('{'.strtolower($key).'}', $value, $ret);
    }

    return $ret;
}