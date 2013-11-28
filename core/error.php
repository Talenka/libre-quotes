<?php
/**
 * LibreQuotes / Core / Error
 */

namespace LibreQuotes;

class error extends \Exception
{
    /**
     * @param string           $message
     * @param integer          $code
     * @param \Exception|error $previous
     */
    public function __construct($message = '', $code = 0, $previous = NULL)
    {
        parent::__construct($message, $code, $previous);

        header('Content-Type: text/html; charset=utf-8');

        echo '<!doctype html>',
             '<title>', L('Oups, something wrong happen!'), '</title>',
             '<style>', file_get_contents('views/style.css'),
             'p a{color:#369;text-decoration:underline}',
             '</style>',
             '<header><div><h1><a href="', HOME, '">', SITE_TITLE, '</a></h1></div></header>',
             '<main><div>';

        if(DEBUG) {

            $githubLink = preg_replace('/^.+\/libre-quotes\//',
                                       'https://github.com/Talenka/libre-quotes/blob/master/',
                                       $this->file) . '#L' . $this->line;

            $fileName = preg_replace('/^.+\/libre-quotes\//', '', $this->file) ;

            $where = 'In <a href="' . $githubLink . '">' . $fileName . ' at line ' . $this->line . '</a>';

            $bugLink = 'https://github.com/Talenka/libre-quotes/issues/new?labels=bug&amp;title=' .
                       urldecode('Error: ' . $this->message) . '&amp;body=' .
                       urlencode($where .' from <a href="https://github.com/Talenka/libre-quotes/blob/master' .
                                 $_SERVER['PHP_SELF'] . '">' . $_SERVER['PHP_SELF'] . '</a>');

            echo '<nav><a href="javascript:history.go(-1)">◀</a><a href="' . $bugLink . '" target=_blank>Report the error</a></nav>';
        }

        echo '<h1>', L('Oups, something wrong happen!'), '</h1>',
             '<p>', L('And this is probably our fault, sorry...'), '</p>',
             '<p><strong>', L('Try to go back to'),
             ' <a href="javascript:history.go(-1)">' . L('the previous page'), '</a> ',
             L('or return to'), ' <a href="', HOME, '">', L('the homepage'), '</a>.</strong></p>';

        if (DEBUG) {

            echo '<p>', L('Technical details'), ': ',
                 $this->message, ' (', $this->code, ')</p>',
                 '<p style="font-size:.9em">', $where,'</p>';
        }

        echo '</div></main>';

        exit;
    }
}
