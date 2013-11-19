<?php
/**
 * LibreQuotes / Assets / Atom feed
 * @todo suppress this page and make every controllers able to render Atom feeds as well as HTML (and JSON ?)
 */

namespace LibreQuotes;

require_once 'core/index.php';

$page->setExpiration(ONE_DAY);

header('Content-Type: application/rss+xml; charset=UTF-8');

echo '<?xml version="1.0" encoding="UTF-8"?>',
     '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" ',
     'xmlns:atom="http://www.w3.org/2005/Atom">',
     '<channel>',
     '<title>' . SITE_TITLE . '</title>',
     '<link>http://' . SERVER_NAME . '/</link>',
     '<description>' . L('Tons of inspiring thoughs') . '</description>',
     '<language>' . $lang . '</language>',
     '<atom:link href="http://' . SERVER_NAME . '/feed" rel="self" type="application/rss+xml" />';

$newest = quote::get('', ITEM_PER_PAGE);

foreach ($newest as $q) echo $q->toAtom();

echo '</channel></rss>';
