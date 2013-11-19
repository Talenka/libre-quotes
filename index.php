<?php
/**
 * LibreQuotes / Controller / Homepage
 */

namespace LibreQuotes;

require_once 'core/index.php';

header('Expires: ' . date('r', NOW + ONE_HOUR));

$stats = $page->getFromCache('stats', ONE_DAY);
$quoteOfTheDay = $page->getFromCache('quoteOfTheDay', ONE_DAY);

if ($stats === false || $quoteOfTheDay === false)
    $quotesNumber = $db->selectCount(quote::DB, 'status="published"');

if ($stats === false) {

    $authorsNumber = $db->selectCount(author::DB);

    $stats = $page->cache('stats',
                          '<strong>' . $quotesNumber . '</strong> ' . L('quotes from') . ' ' .
                          '<strong>' . $authorsNumber . '</strong> ' . L('Authors'));
}

if ($quoteOfTheDay === false) {

    $randomQuote = quote::get('', rand(0, $quotesNumber - 1) . ',1');
    $randomQuote = $randomQuote[0];

    $quoteOfTheDay = $page->cache('quoteOfTheDay', $randomQuote->toBlock());
}

$newest = $page->getFromCache('newest', ONE_HOUR);

if ($newest === false) $newest = $page->cache('newest', $page->ulist(quote::get('', 10)));

$page->setTitle(L('Tons of inspiring thoughs'))
     ->addContent($stats)
     ->addSection($page->link('random', L('Quote of the day')), $quoteOfTheDay)
     ->addSection($page->link('newest', L('Newest quotes')), $newest)
     ->render();
