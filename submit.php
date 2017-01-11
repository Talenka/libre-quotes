<?php
/**
 * LibreQuotes / Controller / Submit (new quote)
 */

namespace LibreQuotes;

require_once 'core/index.php';

$submitForm = new Form();

$languagesOptions = array();

foreach ($definedLanguages as $code => $name) {
    $languagesOptions[$code] = L($name);
}

$submitForm->addTextInput(
    'text',
    'Type the quote here',
    Quote::TEXT_MAXLENGTH,
    false,
    'required autofocus class="big to-right"'
)
->addSubmitButton('Submit', 'class=to-left')
->addTextInput(
    'author',
    'Author',
    Author::NAME_MAXLENGTH,
    empty($_POST['author']) ? false : $_POST['author'],
    'list=famousAuthors'
)
->addTextInput(
    'origin',
    'Origin',
    Origin::NAME_MAXLENGTH,
    empty($_POST['origin']) ? false : stripslashes($_POST['origin']),
    'list=famousOrigins'
)
->addSelect('lang', '', $lang, $languagesOptions);

if ($submitForm->isKeyValid()) {

    // Check if the author is already is the database, and create it otherwise.

    $authorFullName = Form::sanitizeText($_POST['author'], author::NAME_MAXLENGTH, 'Anonymous');
    $authorSlugName = Form::sanitizeSlug($authorFullName);

    $authorSql = Author::getBySlug($authorSlugName);

    if ($authorSql) {
        $db->update(
            Author::DB,
            'quotesNumber=quotesNumber+1',
            'authorId=' . $authorSql->id,
            1
        );

    } else {
        $db->insert(
            Author::DB,
            'slugName,fullName,quotesNumber',
            '"' . $db->escapeString($authorSlugName) . '","' . $db->escapeString($authorFullName) . '",1'
        );
    }

    $authorId = $authorSql ? $authorSql->id : $db->insert_id;

    // Check if the origin is already is the database, and create it otherwise.

    $originName = Form::sanitizeText($_POST['origin'], Origin::NAME_MAXLENGTH, 'Unknown');

    $originSql = Origin::get('name="' . $db->escapeString($originName) . '"');

    if (empty($originSql)) {
        $db->insert(Origin::DB, 'name', '"' . $db->escapeString($originName) . '"');
    }

    $originId = empty($originSql) ? $db->insert_id : $originSql[0]->id;

    // Insert quote

    $quoteText = Form::sanitizeText($_POST['text'], Quote::TEXT_MAXLENGTH, '');
    $quoteText = ucfirst($quoteText);

    $db->insert(
        Quote::DB,
        'text,authorId,originId,lang,status,submissionDate',
        '"' . $db->escapeString($quoteText, true) . '", ' . $authorId . ', ' .
        $originId . ', "' . $lang . '", "submitted", UNIX_TIMESTAMP()'
    );

    if ($db->insert_id != 0) {
        $submissionSuccess = true;
    }
}

$page->setTitle(L('Submit a new quote'));

if (isset($submissionSuccess) && $submissionSuccess === true) {
    $page->addContent('<p class="info ok">' .
                      L('Your quote have been successfully submitted and is waiting for moderation.') . '</p>');
}

$page->addContent($submitForm->render())
     ->addContent(Author::datalist())
     ->addContent(Origin::datalist())
     ->render();
