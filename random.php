<?php
/**
 * LibreQuotes / Controller / Random (quotes)
 */

namespace LibreQuotes;

require_once 'core/index.php';

$quotesNumber = $db->selectCount(Quote::DB, 'status="published"');

$randomQuoteIds = array();

for ($i = 0; $i < ITEM_PER_PAGE; $i++) {
    $randomQuoteIds[$i] = mt_rand(1, $quotesNumber);
}

$randomQuotes = Quote::get('', ITEM_PER_PAGE, '(quoteId = ' . implode(' OR quoteId=', $randomQuoteIds) . ')');

$page->setTitle(L('Random quotes'))
     ->addPagination('<nav>' .
                     $page->link('random', L('Others random quotes')) .
                     $page->link('newest', L('Newest quotes')) .
                     '</nav>')
     ->addList($randomQuotes)
     ->render();
