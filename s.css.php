<?php
/**
 * LibreQuotes / Assets / Stylesheet (cached and minified)
 */

namespace LibreQuotes;

require_once 'core/index.php';

$style = $page->getFromCache('', ONE_WEEK);

if ($style === false) {

    $style = file_get_contents('views/style.css');

    $style = str_replace(
        array("\n", '    ', ': ', ';}', '> ', ' >', ', ', 'black', 'bold'),
        array('', '', ':', '}', '>', '>', ',', '#000', '800'),
        $style
    );

    $page->cache('', $style);

    header('Last-Modified: ' . date('r'));

} else {
    header('Last-Modified: ' . date('r', filemtime('views/style.css')));
}

$page->setExpiration(ONE_WEEK);

header('Content-Type: text/css; charset=UTF-8');

echo $style;
