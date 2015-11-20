<?php
/**
 * LibreQuotes / Controller / Search
 */

namespace LibreQuotes;

require_once 'core/index.php';

$search = new form('', 'get');

$searchPhrase = (substr($_SERVER['REQUEST_URI'], 0, 7) == '/search') ?
                (empty($_GET['q']) ? URL_PARAMS : trim($_GET['q'])) :
                substr($_SERVER['REQUEST_URI'], 1);

$searchTypes = array('all', 'authors', 'quotes', 'topics');

$searchType = (empty($_GET['type']) || !in_array($_GET['type'], $searchTypes)) ?
               'all' : $_GET['type'];

$searchResults = array();

if (!empty($searchPhrase)) {
    if ($searchType == 'authors' || $searchType == 'all') {

        $authorsResults = author::get('slugName LIKE "%' . form::sanitizeSlug($searchPhrase) . '%"',
                                      ITEM_PER_PAGE, 'quotesNumber DESC');

        $searchResults = array_merge($searchResults, $authorsResults);
    }

    if ($searchType == 'quotes' || $searchType == 'all') {

        $quotesResults = quote::get('q.text LIKE "%' . $db->escapeString($searchPhrase) . '%"',
                                    ITEM_PER_PAGE, 'submissionDate DESC');

        // Highlight the searched text in the quote
        foreach ($quotesResults as $key => $value) {
          $quotesResults[$key]->text = str_ireplace($searchPhrase,
                                                    '<b class=ok>' . $searchPhrase . '</b>',
                                                    $quotesResults[$key]->text);
        }

        $searchResults = array_merge($searchResults, $quotesResults);
    }

    if ($searchType == 'topics' || $searchType == 'all') {

        $topicsResults = topic::get('slug LIKE "%' . form::sanitizeSlug($searchPhrase) . '%"',
                                      ITEM_PER_PAGE, 'quotesNumber DESC');

        $searchResults = array_merge($searchResults, $topicsResults);
    }
}

$search->addTextInput('q', L('What are you looking for?'), 255,
                      empty($searchPhrase) ? false : $searchPhrase,
                      'required autofocus class="big to-right"')
       ->addSubmitButton(L('Search'), 'class=to-left')
       ->addSelect('type', '',
                   empty($_GET['type']) ? false : $_GET['type'],
                   array('all' => L('All'),
                         'authors' => L('Authors'),
                         'quotes' => L('Quote'),
                         'topics' => L('Topics')));

$page->setTitle(L('Search'))
     ->addContent($search->render())
     ->addList($searchResults)
     ->render();
