<?php
/**
 * LibreQuotes / Controller / Submit (new quote)
 */

namespace LibreQuotes;

require_once 'core/index.php';

$submitForm = new form();

$languagesOptions = array();

foreach ($definedLanguages as $code => $name) $languagesOptions[$code] = L($name);

$submitForm->addTextInput('text', 'Type the quote here', quote::TEXT_MAXLENGTH, false,
                          'required autofocus class="large stick-right"')
           ->addSubmitButton('Submit', 'class=stick-left')
           ->addTextInput('author', 'Author', author::NAME_MAXLENGTH,
                          empty($_POST['author']) ? false : $_POST['author'], 'list=famousAuthors')
           ->addTextInput('origin', 'Origin', origin::NAME_MAXLENGTH,
                          empty($_POST['origin']) ? false : stripslashes($_POST['origin']), 'list=famousOrigins')
           ->addSelect('lang', '', $lang, $languagesOptions);

if ($submitForm->isKeyValid()) {

    // Check if the author is already is the database, and create it otherwise.

    $authorFullName = form::sanitizeText($_POST['author'], author::NAME_MAXLENGTH, 'Anonymous');
    $authorSlugName = form::sanitizeSlug($authorFullName);

    $authorSql = author::getBySlug($authorSlugName);

    if ($authorSql) $db->update(author::DB, 'quotesNumber=quotesNumber+1', 'authorId=' . $authorSql->id, 1);

    else $db->insert(author::DB, 'slugName,fullName,quotesNumber',
                     '"' . $db->escapeString($authorSlugName) . '","' . $db->escapeString($authorFullName) . '",1');

    $authorId = $authorSql ? $authorSql->id : $db->insert_id;

    // Check if the origin is already is the database, and create it otherwise.

    $originName = form::sanitizeText($_POST['origin'], origin::NAME_MAXLENGTH, 'Unknown');

    $originSql = origin::get('name="' . $db->escapeString($originName) . '"');

    if (empty($originSql)) $db->insert(origin::DB, 'name', '"' . $db->escapeString($originName) . '"');

    $originId = empty($originSql) ? $db->insert_id : $originSql[0]->id;

    // Insert quote

    $quoteText = form::sanitizeText($_POST['text'], quote::TEXT_MAXLENGTH, '');
    $quoteText = ucfirst($quoteText);

    $db->insert(quote::DB, 'text,authorId,originId,lang,status,submissionDate',
                '"' . $db->escapeString($quoteText, true) . '", ' . $authorId . ', ' .
                $originId . ', "' . $lang . '", "submitted", UNIX_TIMESTAMP()');

    if ($db->insert_id != 0) $submissionSuccess = true;
}

$page->setTitle(L('Submit a new quote'));

if (isset($submissionSuccess) && $submissionSuccess === true)
    $page->addContent('<p class="notice success">' .
                      L('Your quote have been successfully submitted and is waiting for moderation.') . '</p>');

$page->addContent($submitForm->render())
     ->addContent(author::datalist())
     ->addContent(origin::datalist())
     ->render();
