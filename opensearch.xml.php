<?php
/**
 * LibreQuotes / Assets / Opensearch
 */

namespace LibreQuotes;

require_once 'core/index.php';

$page->setExpiration(ONE_WEEK);
header('Content-Type: application/rss+xml; charset=UTF-8');

echo '<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/" ',
     'xmlns:moz="http://www.mozilla.org/2006/browser/search/">',
     '<ShortName>' . SITE_TITLE . '</ShortName>',
     '<Description>Search ' . SITE_TITLE . '</Description>',
     '<InputEncoding>UTF-8</InputEncoding>',
     '<Image width="16" height="16" type="image/x-icon">' . BASE_URL . 'favicon.ico</Image>',
     '<Url type="text/html" method="get" template="' . BASE_URL . 'search?q={searchTerms}"/>',
     '<moz:SearchForm>' . BASE_URL . 'search</moz:SearchForm>',
     '</OpenSearchDescription>';
