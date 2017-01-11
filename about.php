<?php
/**
 * LibreQuotes / Controller / About (this site)
 */

namespace LibreQuotes;

require_once 'core/index.php';

if (!$page->renderFromCache(ONE_WEEK)) {

    $page->setTitle(L('About'))
         ->addContent('<p>' . SITE_TITLE . ' is a simple quotes recorder. ' .
                      'Because others quotes sites are far too complicated. ' .
                      SITE_TITLE . ' is also free, as in free speech. ' .
                      'Unless specified otherwise, quotes are in ' .
                      '<a href="https://en.wikipedia.org/wiki/Public_domain">public domain</a>. ' .
                      'We foster quotes re-use by making them available in interoperable format (rss, json). ' .
                      SITE_TITLE . ' source code itself is published under ' .
                      '<a href="https://gnu.org/licenses/gpl.html">GNU/GPL</a> on ' .
                      '<a href="https://github.com/Talenka/libre-quotes/">GitHub</a>. ' .
                      '</p>')
         ->addSection(
             L('Who is behind'),
             '<p>Just me, <a href="https://boudah.pl">Boudah Talenka</a>, doing this in my spare time.</p>'
         )
         ->addSection(
             L('Under the hood'),
             '<p>' . SITE_TITLE . ' is baked with love on ' .
             '<a href="https://en.wikipedia.org/wiki/LAMP_%28software_bundle%29">LAMP</a> using ' .
             '<a href="https://www.sublimetext.com">SublimeText</a>, ' .
             '<a href="https://filezilla-project.org/">Filezilla</a> and ' .
             '<a href="https://www.mozilla.org/fr/firefox/new/">Firefox</a>.</p>'
         )
         ->cacheWholePage(ONE_WEEK);
}
