<?php
/**
 * LibreQuotes / Controller / Quote (details)
 */

namespace LibreQuotes;

require_once 'core/index.php';

$theQuote = Quote::getById(empty($_GET['id']) ? URL_PARAMS : $_GET['id']);

if ($theQuote) {

    $addTopicForm = new Form('quote?' . $theQuote->id);

    $addTopicForm->addTextInput(
        'name',
        'New topic',
        Topic::NAME_MAXLENGTH,
        false,
        'required autofocus class=to-right list=famousTopics style="width:120px"'
    )
    ->addSubmitButton('Add', 'class=to-left');

    if ($addTopicForm->isKeyValid()) {

        // Check if topic name exists, else we create new topic

        $topicName = Form::sanitizeText($_POST['name'], Topic::NAME_MAXLENGTH, '');

        if (!empty($topicName)) {

            $topicSlug = Form::sanitizeSlug($topicName);
            $topicSql = Topic::getBySlug($topicSlug);

            if ($topicSql) {
                $db->update(Topic::DB, 'quotesNumber=quotesNumber+1', 'topicId=' . $topicSql->id, 1);

            } else {
                $db->insert(
                    Topic::DB,
                    'slug,name,quotesNumber',
                    '"' . $db->escapeString($topicSlug) . '","' . $db->escapeString($topicName) . '",1'
                );
            }

            $topicId = $topicSql ? $topicSql->id : $db->insert_id;

            // Check if this quote is marked with this topic, else we add a mark.

            $markNumber = $db->selectCount(Mark::DB, 'topicId=' . $topicId . ' AND quoteId = ' . $theQuote->id);

            if ($markNumber === 0) {
                $db->insert(Mark::DB, 'topicId,quoteId', $topicId . ',' . $theQuote->id);
            }
        }
    }

    $topics = Model::toStrings($theQuote->getTopics());

    $topicsList = '';

    if (!empty($topics)) {
        $topicsList .= '<p>♦ ' . implode(' ♦ ', $topics). '</p>';
    }

    $topicsList .= $addTopicForm->render() . topic::datalist();

    $page->setTitle(L('Quote') . ' #' . $theQuote->id)
         ->addContent($theQuote->toBlock())
         ->addSection(L('Topics'), $topicsList)
         ->render();

} else {
    $page->redirectTo('search?q=' . urlencode(URL_PARAMS), NOT_FOUND);
}
