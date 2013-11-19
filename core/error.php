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

        echo '<doctype html>',
             '<h1>' . L('Oups, something wrong happen!') . '</h1>',
             '<p>' . L('And this is probably our fault, sorry...') . '</p>',
             '<p>' . L('Try to go back to'),
             ' <a href="javascript:history.go(-1)">' . L('the previous page') . '</a> ',
             L('or return to') . ' <a href="' . HOME . '">' . L('the homepage') . '</a>.</p>';

        if (DEBUG) echo '<h2>' . L('Technical details') . '</h2>',
                        '<p><strong>', $this->message, '</strong> (', $this->code, ')</p>',
                        '<p>In ', $this->file, ' at line #', $this->line, '</p>';

        exit;
    }
}
