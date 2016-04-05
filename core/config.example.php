<?php
/**
 * LibreQuotes / Core / Config
 */

namespace LibreQuotes;

/** @var boolean Enable debug mode */
const DEBUG = true;

/** @var string The [title] of the site */
const SITE_TITLE = 'LibreQuotes';

/** @var string The password hashing salt (choose a 22-characters unique one) */
const CRYPT_SALT = 'QOYL9m7Fv8i2GQ8Xen2nGJ';
// Compute in php : crypt(, '$2a$07$' . CRYPT_SALT . '$');

/** @var string The MySql database host ("localhost" in 99% of cases) */
const SQL_HOST = 'localhost';

/** @var string The MySql database username */
const SQL_USER = 'root';

/** @var string The MySql database password (choose a long and unique one) */
const SQL_PASSWORD = '';

/** @var string Your MySql database name */
const SQL_DB = 'librequotes';

/** @var string MySQL table prefix */
const SQL_TABLE_PREFIX = 'lq_';

/** @var string The Blowfish digest of admin password */
const ADMIN_PASS_HASH = '$2a$07$b1u7/Rmxm4Zv.4Ha024h8OapIOJCBUOuAudAWYbVUQ25m9VSLaxmy';
// Compute in php : crypt('anotherLongAndUniquePassword', '$2a$07$' . CRYPT_SALT . '$');

/** @var string The canonical protocol and domain name of the site, ending by a slash */
const BASE_URL = 'http://libre-quotes.boudah.pl/';

/** @var string home page url */
const HOME = '/';

/** @var string */
const CACHE_PATH = 'cache';

/** @var integer */
const ITEM_PER_PAGE = 20;
