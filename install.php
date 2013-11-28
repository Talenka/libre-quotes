<?php
/**
 * LibreQuotes / Controllers / Installation
 */

namespace LibreQuotes;

ini_set('display_errors', 'off');

if (file_exists('core/config.php')) require_once 'core/config.php';
else require_once 'core/config.example.php';

header('Content-Type: text/html; charset=utf-8');

$step = 0;
$messages = array();

if (PHP_VERSION >= 5 && class_exists('mysqli')) $step = 1;

if ($step >= 1 &&
    file_put_contents('core/test.php', '<?php /* TEST */ ?>') &&
    unlink('core/test.php') &&
    file_put_contents('cache/test.php', '<?php /* TEST */ ?>') &&
    unlink('cache/test.php')) $step = 2;

if ($step == 2 &&
    !empty($_POST['SITE_TITLE']) &&
    strlen($_POST['SITE_TITLE']) >= 2 &&
    strlen($_POST['SITE_TITLE']) <= 50 &&
    !empty($_POST['SERVER_NAME']) &&
    strlen($_POST['SERVER_NAME']) >= 4 &&
    strlen($_POST['SERVER_NAME']) <= 255 &&
    !empty($_POST['SQL_HOST']) &&
    strlen($_POST['SQL_HOST']) <= 255 &&
    !empty($_POST['SQL_USER']) &&
    strlen($_POST['SQL_USER']) <= 255 &&
    !empty($_POST['SQL_PASSWORD']) &&
    strlen($_POST['SQL_PASSWORD']) <= 255 &&
    !empty($_POST['SQL_DB']) &&
    strlen($_POST['SQL_DB']) <= 255 &&
    !empty($_POST['SQL_TABLE_PREFIX']) &&
    strlen($_POST['SQL_TABLE_PREFIX']) <= 50 &&
    !empty($_POST['ADMIN_PASS']) &&
    strlen($_POST['ADMIN_PASS']) <= 255 &&
    !empty($_POST['CRYPT_SALT']) &&
    strlen($_POST['CRYPT_SALT']) == 22 &&
    !empty($_POST['HOME']) &&
    strlen($_POST['HOME']) <= 255 &&
    !empty($_POST['CACHE_PATH']) &&
    strlen($_POST['CACHE_PATH']) <= 255 &&
    !empty($_POST['ITEM_PER_PAGE']) &&
    strlen($_POST['ITEM_PER_PAGE']) <= 3 &&
    (int) $_POST['ITEM_PER_PAGE'] <= 100) {

    $ADMIN_PASS_HASH = crypt($_POST['ADMIN_PASS'], '$2a$07$' . $_POST['CRYPT_SALT'] . '$');

    if (file_put_contents('core/config.php',
        '<' . '?php' . "\n" .
        '/' . '**' . "\n" .
        ' * LibreQuotes / Core / Config' . "\n" .
        ' *' . '/' . "\n" .
        "\n" .
        'namespace LibreQuotes;' . "\n" .
        "\n" .
        'const DEBUG = ' . ((empty($_POST['DEBUG']) || $_POST['DEBUG'] == 'true') ? 'true' : 'false') . ';' . "\n" .
        'const SITE_TITLE = \'' . $_POST['SITE_TITLE'] . '\';' . "\n" .
        'const CRYPT_SALT = \'' . $_POST['CRYPT_SALT'] . '\';' . "\n" .
        'const SQL_HOST = \'' . $_POST['SQL_HOST'] . '\';' . "\n" .
        'const SQL_USER = \'' . $_POST['SQL_USER'] . '\';' . "\n" .
        'const SQL_PASSWORD = \'' . $_POST['SQL_PASSWORD'] . '\';' . "\n" .
        'const SQL_DB = \'' . $_POST['SQL_DB'] . '\';' . "\n" .
        'const SQL_TABLE_PREFIX = \'' . $_POST['SQL_TABLE_PREFIX'] . '\';' . "\n" .
        'const ADMIN_PASS_HASH = \'' . $ADMIN_PASS_HASH . '\';' . "\n" .
        'const SERVER_NAME = \'' . $_POST['SERVER_NAME'] . '\';' . "\n" .
        'const HOME = \'' . $_POST['HOME'] . '\';' . "\n" .
        'const CACHE_PATH = \'' . $_POST['CACHE_PATH'] . '\';' . "\n" .
        'const ITEM_PER_PAGE = ' . (int) $_POST['ITEM_PER_PAGE'] . ';')) {

        header('Location: /install');
    }
    else array_push($messages, 'Unable to create configuration file <tt>core/config.php</tt>');

}

if ($step == 2 && file_exists('core/config.php')) {
    $dbObject = new \mysqli(SQL_HOST, SQL_USER, SQL_PASSWORD, SQL_DB);

    if ($dbObject->connect_error)
        array_push($messages, $dbObject->connect_error);

    else $step = 3;
}

if ($step == 3) {

    $dbError = false;

    $sql = $dbObject->query('SELECT COUNT(*) as n FROM `' . SQL_TABLE_PREFIX . 'authors`');

    if ($sql === false || !empty($dbObject->error) || (int) $sql->fetch_object()->n < 1) $dbError = true;

    $sql = $dbObject->query('SELECT COUNT(*) as n FROM `' . SQL_TABLE_PREFIX . 'marks`');

    if ($sql === false || !empty($dbObject->error) || (int) $sql->fetch_object()->n < 1) $dbError = true;

    $sql = $dbObject->query('SELECT COUNT(*) as n FROM `' . SQL_TABLE_PREFIX . 'origins`');

    if ($sql === false || !empty($dbObject->error) || (int) $sql->fetch_object()->n < 1) $dbError = true;

    $sql = $dbObject->query('SELECT COUNT(*) as n FROM `' . SQL_TABLE_PREFIX . 'quotes`');

    if ($sql === false || !empty($dbObject->error) || (int) $sql->fetch_object()->n < 1) $dbError = true;

    $sql = $dbObject->query('SELECT COUNT(*) as n FROM `' . SQL_TABLE_PREFIX . 'topics`');

    if ($sql === false || !empty($dbObject->error) || (int) $sql->fetch_object()->n < 1) $dbError = true;

    if ($dbError === false) $step = 4;
    elseif (!empty($_POST['create-tables'])) {

        $sqlQueries = explode(';', str_replace('`lq_', '`' . SQL_TABLE_PREFIX, file_get_contents('core/install.sql')));

        foreach ($sqlQueries as $query) {
            $sql = $dbObject->query($query);

            if ($sql === false) {
                array_push($messages, "Problem with sql query : “" . $query . "” (" . $this->dbObject->error . ")");

                break;
            }
        }
    }
}

?><!doctype html>
<html lang="en">
  <head>
    <title>Installation</title>
    <style>
      <?php echo file_get_contents('views/style.css'); ?>
      label {display:block;margin:0 0 12px;font-size:.8em;padding:0 3px}
      fieldset {border: 1px #4F443B solid;border-radius:10px}
    </style>
  </head>
  <body>
    <header>
      <div>
        <h1 id="title"><? echo SITE_TITLE; ?></h1>
      </div>
    </header>
    <main>
      <div>
        <nav>
          Report <a href="https://github.com/Talenka/libre-quotes/issues/new?labels=bug&amp;title=Problem+during+installation+process+step+<?php echo $step; ?>">bug</a><a href="https://github.com/Talenka/libre-quotes/issues">suggestion</a>
        </nav>
        <h1>Hi fellow !</h1>
        <p>There is few steps left:</p>
        <ol>
<?php if ($step == 0) { ?>
          <li style="font-weight:700;color:red">Check that you have PHP 5 or later, and MySql 4.1 or later</li>
          <li>Check that PHP have write permission on <tt>core</tt> and <tt>cache</tt> directories</li>
          <li>Create the <tt>core/config.php</tt> file</li>
          <li>Create database tables with some data</li>
          <li>Suppress the file <tt>install.php</tt> (Optionnal but recommended)</li>
<?php } elseif ($step == 1) { ?>
          <li style="color:green">Check that you have PHP 5 or later, and MySql 4.1 or later</li>
          <li style="font-weight:700;color:red">Check that PHP have write permission on <tt>core</tt> and <tt>cache</tt> directories</li>
          <li>Create the <tt>core/config.php</tt> file</li>
          <li>Create database tables with some data</li>
          <li>Suppress the file <tt>install.php</tt> (Optionnal but recommended)</li>
<?php } elseif ($step == 2) { ?>
          <li style="color:green">Check that you have PHP 5 or later, and MySql 4.1 or later</li>
          <li style="color:green">Check that PHP have write permission on <tt>core</tt> and <tt>cache</tt> directories</li>
          <li style="font-weight:700">Create the <tt>core/config.php</tt> file</li>
          <li>Create database tables with some data</li>
          <li>Suppress the file <tt>install.php</tt> (Optionnal but recommended)</li>
<?php } elseif ($step == 3) { ?>
          <li style="color:green">Check that you have PHP 5 or later, and MySql 4.1 or later</li>
          <li style="color:green">Check that PHP have write permission on <tt>core</tt> and <tt>cache</tt> directories</li>
          <li style="color:green">Create the <tt>core/config.php</tt> file</li>
          <li style="font-weight:700">Create database tables with some data</li>
          <li>Suppress the file <tt>install.php</tt> (Optionnal but recommended)</li>
<?php } else { ?>
          <li style="color:green">Check that you have PHP 5 or later, and MySql 4.1 or later</li>
          <li style="color:green">Check that PHP have write permission on <tt>core</tt> and <tt>cache</tt> directories</li>
          <li style="color:green">Create the <tt>core/config.php</tt> file</li>
          <li style="color:green">Create database tables with some data</li>
          <li style="font-weight:700">Suppress this file <tt>install.php</tt> (Optionnal but recommended)</li>
<?php } ?>
        </ol>

<?php if (!empty($messages)) echo '<h3>Houston we have a problem:</h3><ul><li>', implode('</li><li>', $messages), '</li></ul>'; ?>

<?php if ($step == 0) { ?>
        <h2>PHP and MySql installation</h2>
        <p>To install/update your server, see <strong><a href="http://www.php.net/manual/en/install.php" target="_blank">php.net/manual/en/install</a></strong></p>
<?php } elseif ($step == 2) { ?>
        <h2>Configuration assistant</h2>
        <form action="install" method="post">

          <fieldset>
          <legend>Site identity</legend>

          <input type="text" name="SITE_TITLE" id="SITE_TITLE" value="<?php echo SITE_TITLE; ?>" onKeyUp="updateTitle(this.value)" pattern="\w{2,50}" maxlength="50" required autofocus>
          <label for="SITE_TITLE">The &lt;title&gt; of the site</label>

          <input type="text" name="SERVER_NAME" id="SERVER_NAME" value="<?php echo $_SERVER['SERVER_NAME']; ?>" pattern=".{4,255}" maxlength="255" required>
          <label for="SERVER_NAME">Canonical server name</label>

          </fieldset>

          <fieldset>
          <legend>MySQL database</legend>

          <input type="text" name="SQL_HOST" id="SQL_HOST" value="<?php echo SQL_HOST; ?>" required maxlength="255">
          <label for="SQL_HOST">The MySql database host ("localhost" in 99% of cases)</label>

          <input type="text" name="SQL_USER" id="SQL_USER" value="<?php echo SQL_USER; ?>" required maxlength="255">
          <label for="SQL_USER">Your MySql database username (often "root", albeit it is not recommended)</label>

          <input type="text" name="SQL_PASSWORD" id="SQL_PASSWORD" value="<?php echo SQL_PASSWORD; ?>" required maxlength="255">
          <label for="SQL_PASSWORD">Your MySql database password (a long and unique one is strongly recommended)</label>

          <input type="text" name="SQL_DB" id="SQL_DB" value="<?php echo SQL_DB; ?>" required maxlength="255">
          <label for="SQL_DB">Your MySql database name</label>

          <input type="text" name="SQL_TABLE_PREFIX" id="SQL_TABLE_PREFIX" value="<?php echo SQL_TABLE_PREFIX; ?>" maxlength="50">
          <label for="SQL_TABLE_PREFIX">MySQL table prefix for this site's table</label>
          </fieldset>

          <fieldset>
          <legend>Security</legend>

          <input type="password" name="ADMIN_PASS" id="ADMIN_PASS" value="" required maxlength="255">
          <label for="ADMIN_PASS">Choose a long and unique password.</label>

          <input type="text" name="CRYPT_SALT" id="CRYPT_SALT" value="<?php echo CRYPT_SALT; ?>" pattern="[a-zA-Z0-9\.\/]{22}" required maxlength="255">
          <input type="button" value="Generate random salt" onClick="generateSalt()">
          <label for="CRYPT_SALT">The password hashing salt (22 random letters, numbers, slashs or dots)</label>

          <label><input type="checkbox" name="DEBUG" checked="<?php echo (CRYPT_SALT ? 'true' : 'false'); ?>" value="true"> Enable debug mode (for tests, not production)</label>

          </fieldset>

          <fieldset>
          <legend>Advanced</legend>

          <input type="text" name="HOME" id="HOME" value="<?php echo HOME; ?>" required maxlength="255">
          <label for="HOME">Your site homepage URL relatively to this directory.</label>

          <input type="text" name="CACHE_PATH" id="CACHE_PATH" value="<?php echo CACHE_PATH; ?>" required maxlength="255">
          <label for="CACHE_PATH">Cache directory path</label>

          <input type="range" min=5 max=100 step=1 name="ITEM_PER_PAGE" id="ITEM_PER_PAGE" value="<?php echo ITEM_PER_PAGE; ?>" required onKeyUp="updateItemPerPage()" onMouseMove="updateItemPerPage()" maxlength="3">
          <strong id="_ITEM_PER_PAGE"><?php echo ITEM_PER_PAGE; ?></strong>
          <label for="ITEM_PER_PAGE">Number of elements (quotes, authors, topics, etc.) displayed per page.</label>

          </fieldset>

          <input type="submit" value="Create core/config.php">
        </form>
<?php } elseif ($step == 3) { ?>
        <h2>Database initialization</h2>
        <form action="install" method="post">
          <input type="submit" name="create-tables" value="Create database tables">
          <p>Or run manually these queries on your database:</p>
          <textarea style="width:99%;height:500px">
<?php echo str_replace('`lq_', '`' . SQL_TABLE_PREFIX, file_get_contents('core/install.sql')); ?>
          </textarea>
        </form>
<?php } elseif ($step == 4) { ?>
        <h2>Installation is complete</h2>
        <p>You should check that everything work properly.
        The <a href="/admin" target="_blank">administration panel</a> is here, you may want to bookmark it.
        If an error happen, you can try to edit the configuration file <tt>core/config.php</tt>.</p>
        <p>When you'll ready, you should delete this file <tt>install.php</tt>.</p>
<?php } ?>
      </div>
    </main>
    <script>

function getById(id) {
    return document.getElementById(id);
}

function updateTitle(t) {
    document.title = getById('title').innerHTML = t;
}

function generateSalt() {
    var characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./',
        salt = '',
        i;

    for (i = 0; i < 22; i++) salt += characters[Math.floor(Math.random()*64)];

    getById('CRYPT_SALT').value = salt;
}

function updateItemPerPage() {
    getById('_ITEM_PER_PAGE').innerHTML = getById('ITEM_PER_PAGE').value;
}

    </script>
  </body>
</html>
<?php

exit;
