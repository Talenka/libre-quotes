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
             'a{color:#369;text-decoration:underline}',
             '</style>',
             '<main><div>',
             '<h1>', L('Oups, something wrong happen!'), '</h1>',
             '<p>', L('And this is probably our fault, sorry...'), '</p>',
             '<p><strong>', L('Try to go back to'),
             ' <a href="javascript:history.go(-1)">' . L('the previous page'), '</a> ',
             L('or return to'), ' <a href="', HOME, '">', L('the homepage'), '</a>.</strong></p>';

        if (DEBUG) {

            $githubLink = preg_replace('/^.+\/libre-quotes\//',
                                       'https://github.com/Talenka/libre-quotes/blob/master/',
                                       $this->file) . '#L' . $this->line;

            $fileName = preg_replace('/^.+\/libre-quotes\//', '', $this->file) ;

            echo '<p>', L('Technical details'), ': ',
                 $this->message, ' (', $this->code, ')</p>',
                 '<p style="font-size:.9em">In <a href="', $githubLink, '">',
                 $fileName, ' at line ', $this->line, '</p>';
        }

        echo '</div></main>';

        exit;
    }
}
