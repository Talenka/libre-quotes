<?php
/**
 * LibreQuotes / Controller / Newest (quotes)
 */

namespace LibreQuotes;

require_once 'core/index.php';

$quotesNumber = $db->selectCount(Quote::DB, 'status="published"');

$page->setTitle(L('Newest quotes'))
     ->setExpiration(ONE_HOUR)
     ->paginate($quotesNumber)
     ->addList(Quote::get('', $page->paginationLimits()))
     ->render();
