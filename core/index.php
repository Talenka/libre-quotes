<?php
/**
 * LibreQuotes / Core
 */

namespace LibreQuotes;

if (file_exists('core/config.php')) require_once 'core/config.php';
else {
    header('Location: install');
    exit;
}

// if (DEBUG) error_reporting(E_ALL);
ini_set('display_errors', 'off');

/** @var integer Seconds per hour */
const ONE_HOUR = 3600;

/** @var integer Seconds per day */
const ONE_DAY = 86400;

/** @var integer Seconds per week */
const ONE_WEEK = 604800;

/** @var integer Http status code for an unknown content */
const NOT_FOUND = 404;

/** @var string Current running script (e.g. /index.php) */
define('PHP_FILE', $_SERVER['SCRIPT_NAME']);

/** @var string url query part (after the "?") */
define('URL_PARAMS', $_SERVER['QUERY_STRING']);

/**
 * @var integer current time in UNIX format (seconds since 1970-01-01 00:00:00)
 *
 * This constant definition not only improve code readability, but also increase
 * performance since the time() is not without cost.
 * Finally, this ensures that time is the same everywhere in the running script.
 */
define('NOW', time());

require_once 'core/error.php';
require_once 'core/database.php';
require_once 'core/html.php';
require_once 'core/model.php';
require_once 'core/form.php';
require_once 'core/lang.php';

require_once 'models/author.php';
require_once 'models/origin.php';
require_once 'models/quote.php';
require_once 'models/topic.php';
require_once 'models/mark.php';

global $lang;

/** @var string */
$lang = 'en';

defineClientLanguage();

$page = new html();
$db = new database(new \mysqli(SQL_HOST, SQL_USER, SQL_PASSWORD, SQL_DB));
