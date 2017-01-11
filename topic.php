<?php
/**
 * LibreQuotes / Controller / Topic (details)
 */

namespace LibreQuotes;

require_once 'core/index.php';

$Topic = Topic::getBySlug(empty($_GET['name']) ? URL_PARAMS : $_GET['name']);

if ($Topic) {

    $page->setTitle($Topic->quotesNumber . ' ' . L('quotes about') . ' ' . $Topic->getName())
         ->paginate($Topic->quotesNumber, array('name' => $Topic->getSlug()))
         ->addList($Topic->getQuotes($page->paginationLimits()))
         ->setExpiration(ONE_DAY)
         ->render();

} else {
    $page->notFound();
}
