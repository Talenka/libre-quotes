<?php
/**
 * LibreQuotes / Core / Form
 */

namespace LibreQuotes;

class form
{
    /** @var string */
    private $url;

    /** @var string */
    public $html;

    /** @var string "post" or "get" */
    private $sendMethod;

    /** @var integer */
    private $term;

    /**
     * Wraps the html code into a form
     *
     * If no url is specified, we assume the target is the current script
     * Example : PHP_FILE = '/lost-password.php', so $url = 'lost-password'
     *
     * @param string  $url        Form destination URL.
     * @param string  $sendMethod "post" or "get".
     * @param integer $term       form expiration from now (in seconds)
     */
    public function __construct($url = '', $sendMethod = 'post', $term = 600)
    {
        if (empty($url)) $url = substr(PHP_FILE, 1, -4);

        $this->url = $url;
        $this->html = '';
        $this->sendMethod = $sendMethod;
        $this->term = $term;
    }

    /**
     * @param  string            $name
     * @param  string            $label
     * @param  integer           $maxlength
     * @param  (string|false)    $value
     * @param  string            $attributes
     * @return \LibreQuotes\form
     */
    public function addPasswordInput($name, $label, $maxlength, $value = false, $attributes = '')
    {
        $this->html .= '<input type=password name=' . $name .
                       ' placeholder="' . L($label) . '" maxlength=' . $maxlength .
                       (($value === false) ? '' : ' value="' . $value . '"') .
                       (empty($attributes) ? '' : ' ' . $attributes) .
                       '>';

        return $this;
    }

    /**
     * @param  string            $name
     * @param  string            $label
     * @param  (string|false)    $value
     * @param  string[]          $options
     * @param  string            $attributes
     * @return \LibreQuotes\form
     */
    public function addSelect($name, $label = '', $value = false, $options = array(), $attributes = '')
    {
        $this->html .= '<label for=' . $name . '>' . $label . '</label>' .
                       '<select name=' . $name . ' id=' . $name .
                       (empty($attributes) ? '' : ' ' . $attributes) . '>';

        foreach ($options as $val => $lab) {
            $this->html .= '<option value="' . $val . '"' .
            (($val === $value) ? ' selected' : '') . '>' . $lab . '</option>';
        }

        $this->html .= '</select>';

        return $this;
    }

    /**
     * @param  string            $label
     * @param  string            $attributes
     * @return \LibreQuotes\form
     */
    public function addSubmitButton($label = 'Submit', $attributes = '')
    {
        $this->html .= '<input type=submit value="' . L($label) . '"' .
                       (empty($attributes) ? '' : ' ' . $attributes) . '>';

        return $this;
    }

    /**
     * @param  string            $name
     * @param  string            $label
     * @param  integer           $maxlength
     * @param  (string|false)    $value
     * @param  string            $attributes
     * @return \LibreQuotes\form
     */
    public function addTextInput($name, $label, $maxlength, $value = false, $attributes = '')
    {
        $this->html .= '<input type=text name=' . $name .
                       ' placeholder="' . L($label) . '" maxlength=' . $maxlength .
                       (($value === false) ? '' : ' value="' . $value . '"') .
                       (empty($attributes) ? '' : ' ' . $attributes) .
                       '>';

        return $this;
    }

    /**
     * @param  string $str
     * @return string
     */
    public function blowfishDisgest($str)
    {
        return crypt($str, '$2a$07$' . CRYPT_SALT . '$');
    }

    /**
     * @param  integer $i
     * @param  integer $minimum
     * @param  integer $maximum
     * @return integer
     */
    public function clampInt($i, $minimum, $maximum)
    {
        return min(max($minimum, (int) $i), $maximum);
    }

    /**
     * Basic form protection against CSRF attack.
     * @return string Html code for the hidden input containing the key.
     */
    private function generateKey()
    {
        return '<input type=hidden name=formKey value=' .
               base_convert(NOW + $this->term, 10, 36) . 'O' .
               $this->hashText(NOW + $this->term) . '>';
    }

    /**
     * Return ephemeral md5 hash of a text.
     *
     * Ephemeral means here that the hash expires when the current user IP or
     * browser changes.
     *
     * @param  string $text
     * @return string
     */
    private function hashText($text)
    {
        return base_convert(md5($text . $_SERVER['REMOTE_ADDR'] .
                                $_SERVER['HTTP_USER_AGENT'] . CRYPT_SALT), 16, 36);
    }

    /**
     * Check whether the form key sent by user is valid and not expired.
     * @return boolean
     */
    public function isKeyValid()
    {
        if (empty($_POST['formKey'])) return false;
        else {

            list($expire, $hash) = explode('O', $_POST['formKey']);

            $expire = base_convert($expire, 36, 10);

            return ($expire > NOW && $this->hashText($expire) === $hash);
        }
    }

    /**
     * @return string
     */
    public function render()
    {
        global $page;

        if ($page->format == 'json') return '';
        return '<form action=' . $this->url .' method=' . $this->sendMethod . '>' .
               $this->html . $this->generateKey() . '</form>';
    }

    /**
     * @param  string $slug
     * @return string
     */
    public function sanitizeSlug($slug)
    {
        $slug = mb_convert_case($slug, MB_CASE_LOWER, 'UTF-8');

        $slug = str_replace(array('æ', 'œ', 'ß'),
                            array('ae', 'oe', 'ss'), $slug);

        $slug = strtr(utf8_decode($slug),
                      utf8_decode(' éèêëåàâäãáùûüúòôöõøìîïÿñ®π€¥ωƒﬁµ@©ç–_'),
                      '-eeeeaaaaaauuuuoooooiiiynrpeyofhmacc--');

        return preg_replace('/[^a-z\-]/', '', $slug);
    }

    /**
     * @param string  $text
     * @param integer $maxlength
     * @param string defaultName
     * @return string
     */
    public function sanitizeText($text, $maxlength, $defaultText = '')
    {
        $text = mb_substr(trim(stripslashes($text)), 0, $maxlength, 'UTF-8');
        $text = mb_convert_encoding($text, 'ISO-8859-1', 'utf-8');

        if (empty($text) || mb_strlen($text) < 2) $text = $defaultText;
        return $text;
    }
}
