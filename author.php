<?php
/**
 * LibreQuotes / Controller / Author (details)
 */

namespace LibreQuotes;

require_once 'core/index.php';

$Author = Author::getBySlug(empty($_GET['name']) ? URL_PARAMS : $_GET['name']);

if ($Author) {

    $page->setTitle($Author->quotesNumber . ' ' . L('quotes from') . ' ' . $Author->getName())
         ->paginate($Author->quotesNumber, array('name' => $Author->getSlug()))
         ->addList(Quote::get('a.authorId=' . $Author->id, $page->paginationLimits()))
         ->setExpiration(ONE_DAY)
         ->render();

} else {
    $page->notFound();
}
