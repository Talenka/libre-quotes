<?php
/**
 * LibreQuotes / Administration
 */

namespace LibreQuotes;

require_once 'core/index.php';

define('ADMIN_COOKIE', form::blowfishDisgest(date('YmdH') . $_SERVER['REMOTE_ADDR']));

// To bypass password protection
define('ADMIN_OPEN', false);

// Page protected by password (access valid one hour max)

if (!ADMIN_OPEN && (empty($_COOKIE['lqAdmin']) || $_COOKIE['lqAdmin'] != ADMIN_COOKIE)) {
    $page->setTitle('Connection');

    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on')
        $page->redirectTo('https://' . SERVER_NAME . '/admin');

    if (CRYPT_BLOWFISH != 1)
        throw new Error("bcrypt not supported. See http://php.net/crypt");

    $connectForm = new form('admin', 'post', 60);

    $connectForm->addPasswordInput('password', 'Type the password here', 255,
                                   false, 'required autofocus class="big to-right"')
                ->addSubmitButton('Connect', 'class=to-left');

    if ($connectForm->isKeyValid()) {

        if (form::blowfishDisgest($_POST['password']) == ADMIN_PASS_HASH) {
            setcookie('lqAdmin', ADMIN_COOKIE, NOW + ONE_HOUR, '/', SERVER_NAME, true, true);

            $page->redirectTo('admin?info=' . urlencode(L('You are successfully connected')));

        } else $page->addContent('<p class=info>' . L('Error: wrong password') . '</p>');
    }

    $page->addContent($connectForm->render());

} else { // If client is the administrator

    $page->setTitle('Administration');

    define('ACTION', empty($_GET['action']) ? 'dashboard' : $_GET['action']);

    if (ACTION == 'dashboard') {

        if (!empty($_GET['info'])) $page->addContent('<p class=info>' . urldecode($_GET['info']). '</p>');

        $newQuotes = quote::sqlToArray($db->select(quote::DB . ' q, ' . author::DB . ' a, ' . origin::DB . ' o',
                           'q.*, a.slugName, a.fullName, a.quotesNumber, o.name, o.type, o.url',
                           'q.status="submitted" AND q.authorId = a.authorId AND q.originId = o.originId',
                           ITEM_PER_PAGE, 'q.submissionDate DESC'));

        if (sizeof($newQuotes) > 0) {

            foreach ($newQuotes as $k => $q)
                $newQuotes[$k] = '<nav>' .
                                 $page->link('admin?action=delete-quote&amp;id=' . $q->id, L('Delete')) .
                                 $page->link('admin?action=edit-quote&amp;id=' . $q->id, L('Edit')) .
                                 $page->link('admin?action=publish-quote&amp;id=' . $q->id, L('Publish')) .
                                 '</nav>' . $q->toString();

            $page->addSection(L('Newest quotes'), $page->ulist($newQuotes));
        }

        // $page->addSection(L('New topics'), '...');

        // delete, edit

        $cacheList = scandir(CACHE_PATH);

        foreach ($cacheList as $k => $f) if (is_file(CACHE_PATH . '/' . $f)) {
            $cacheList[$k] = $f . ' (' . round(filesize(CACHE_PATH . '/' . $f) / 1000, 1) . ' Ko)' .
                             '<nav>' .
                             $page->link('admin?action=see-cache&amp;file=' . urlencode($f), L('See')) .
                             $page->link('admin?action=purge-cache&amp;file=' . urlencode($f), L('Purge')) .
                             '</nav>';

        } else unset($cacheList[$k]);

        $page->addSection(L('Cached contents'), $page->ulist($cacheList));

        // delete

    } elseif (ACTION == 'publish-quote' && !empty($_GET['id'])) {

        $id = form::clampInt($_GET['id'], 1, quote::ID_MAX);

        $db->update(quote::DB, 'status = "published"', 'quoteId=' . $id);

        $page->redirectTo('admin?info=' . urlencode(L('Quote') . ' #' . $id . ' ' . L('was published')));

    } elseif (ACTION == 'edit-quote' && !empty($_GET['id'])) {

        $id = form::clampInt($_GET['id'], 1, quote::ID_MAX);

        /**
         * @todo implement
         */

    } elseif (ACTION == 'delete-quote' && !empty($_GET['id'])) {

        $id = form::clampInt($_GET['id'], 1, quote::ID_MAX);

        $db->update(quote::DB, 'status = "refused"', 'quoteId=' . $id);

        $page->redirectTo('admin?info=' . urlencode(L('Quote') . ' #' . $id . ' ' . L('was refused')));

    } elseif (ACTION == 'purge-cache' && !empty($_GET['file'])) {

        $file = $_GET['file'];

        if (file_exists(CACHE_PATH . '/' . $file) && is_file(CACHE_PATH . '/' . $file)) {

            if (unlink(CACHE_PATH . '/' . $file))
                $page->redirectTo('admin?info=' . urlencode(L('Cache successfully purged')));

            else $page->redirectTo('admin?info=' . urlencode(L('Error: cached content not purged')));

        } else $page->redirectTo('admin?info=' . urlencode(L('File does not exists')));
    } elseif (ACTION == 'see-cache' && !empty($_GET['file'])) {

        $file = $_GET['file'];

        if (file_exists(CACHE_PATH . '/' . $file) && is_file(CACHE_PATH . '/' . $file))

            $page->addSection(L('Cached contents') . ' ' . $file,
                              '<pre style="background:#fff;border:1px #ccc solid;padding:4px;width:100%;' .
                              'word-wrap:break-word;display:block;white-space:pre-wrap">' .
                              htmlentities(file_get_contents(CACHE_PATH . '/' . $file),
                                           ENT_QUOTES, 'UTF-8') .
                              '</pre>');

        else $page->redirectTo('admin?info=' . urlencode(L('File does not exists')));
    }
}

$page->render();
