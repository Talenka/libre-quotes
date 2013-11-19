<?php
/**
 * LibreQuotes / Controller / Topics (list)
 */

namespace LibreQuotes;

require_once 'core/index.php';

$topicsNumber = $db->selectCount(topic::DB);

$orderColumn = (empty($_GET['by']) || $_GET['by'] !== 'name') ? 'quotesNumber' : 'name';
$orderDirection = ($orderColumn === 'quotesNumber') ? 'DESC' : 'ASC';

$orderNav = '<nav>' . $topicsNumber . ' ' . L('Topics') . ' ' . L('ordered by') . ' ' .
            $page->link('topics', L('quotes number'),
                        ($orderColumn == 'quotesNumber') ? 'class=a' : '') .
            $page->link('topics?by=name', L('alphabetic order'),
                        ($orderColumn == 'name') ? 'class=a' : '') .
            '</nav>';

$page->setTitle(L('Topics list'))
     ->setExpiration(ONE_DAY)
     ->paginate($topicsNumber, array('by' => $orderColumn))
     ->addList(topic::get('', $page->paginationLimits(), $orderColumn . ' ' . $orderDirection))
     ->addContent($orderNav)
     ->render();
