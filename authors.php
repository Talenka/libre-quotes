<?php
/**
 * LibreQuotes / Controller / Authors (list)
 */

namespace LibreQuotes;

require_once 'core/index.php';

$authorsNumber = $db->selectCount(Author::DB);

$orderColumn = (empty($_GET['by']) || $_GET['by'] !== 'fullName') ? 'quotesNumber' : 'fullName';

$orderDirection = ($orderColumn === 'quotesNumber') ? 'DESC' : 'ASC';

$orderNav = '<nav>' . $authorsNumber . ' ' . L('Authors') . ' ' . L('ordered by') . ' ' .
            $page->link(
                'authors',
                L('quotes number'),
                ($orderColumn === 'quotesNumber') ? 'class=a' : ''
            ) .
            $page->link(
                'authors?by=fullName',
                L('alphabetic order'),
                ($orderColumn === 'fullName') ? 'class=a' : ''
            ) .
            '</nav>';

$page->setTitle(L('Authors list'))
     ->paginate($authorsNumber, array('by' => $orderColumn))
     ->setExpiration(ONE_DAY)
     ->addList(Author::get('', $page->paginationLimits(), $orderColumn . ' ' . $orderDirection))
     ->addContent($orderNav)
     ->render();
