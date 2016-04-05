<?php
/**
 * LibreQuotes / Assets / Sitemap
 */

namespace LibreQuotes;

require_once 'core/index.php';

$page->setExpiration(ONE_DAY);
header('Content-Type: application/xml; charset=UTF-8');

$sitemap = array(
        array('url' => '', 'lastmod' => NOW, 'priority' => '1.0'),
        array('url' => 'topics', 'lastmod' => NOW - ONE_HOUR, 'priority' => '0.8'),
        array('url' => 'authors', 'lastmod' => NOW - ONE_DAY, 'priority' => '0.8'),
        array('url' => 'newest', 'lastmod' => NOW - ONE_HOUR, 'priority' => '0.8'),
        array('url' => 'random', 'lastmod' => NOW, 'priority' => '0.5'),
        array('url' => 'about', 'lastmod' => filemtime('about.php'), 'priority' => '0.3')
    );

$famousTopics = topic::get('', ITEM_PER_PAGE, 'quotesNumber DESC');

foreach ($famousTopics as $t)
    array_push($sitemap, array('url' => 'topic?' . $t->getSlug(),
                               'lastmod' => NOW,
                               'priority' => '0.2'));

$newestQuotes = quote::get('', ITEM_PER_PAGE);

foreach ($newestQuotes as $q)
    array_push($sitemap, array('url' => 'quote?' . $q->id,
                               'lastmod' => $q->submissionDate,
                               'priority' => '0.1'));

echo '<', '?xml version="1.0" encoding="UTF-8"?', '>',
     '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ',
     'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 ',
     'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" ',
     'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

foreach ($sitemap as $params) {

    echo '<url><loc>', BASE_URL, $params['url'], '</loc>',
         '<lastmod>', date('Y-m-d', $params['lastmod']), '</lastmod>',
         '<priority>', $params['priority'], '</priority></url>';

}

echo '</urlset>';

exit;
