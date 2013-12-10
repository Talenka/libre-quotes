<?php
/**
 * LibreQuotes / Model / (Quotes) author
 */

namespace LibreQuotes;

class author extends model
{
    /** @var string */
    const DB = 'lq_authors';

    /** @var integer */
    const NAME_MAXLENGTH = 255;

    /** @var integer mediumint(6) UNSIGNED */
    public $id = 0;

    /** @var string varchar(255) */
    public $slugName;

    /** @var string varchar(255) */
    public $fullName;

    /** @var integer smallint(4) UNSIGNED */
    public $quotesNumber = 0;

    /**
     * @param \mysqli_result $data
     */
    public function __construct($data)
    {
        $this->id = empty($data->authorId) ? 0 : (int) $data->authorId;
        $this->slugName = $data->slugName;
        $this->fullName = $data->fullName;
        $this->quotesNumber = empty($data->quotesNumber) ? 0 : (int) $data->quotesNumber;
    }

    /**
     * @return string
     */
    public function toString()
    {
        global $page;

        return ($page->format == 'json') ? $this->toJson() : $page->link('author?' . $this->slugName, $this->getName());
    }

    /**
     * @return string
     */
    public function toAtom()
    {
        return '<item>' .
               '<title>' . $this->getName() . '</title>' .
               '<guid isPermaLink="false">http://' . SERVER_NAME . '/author?' . $this->getSlug() . '</guid>' .
               '<link>http://' . SERVER_NAME . '/author?' . $this->getSlug() . '</link>' .
               '<category domain="http://' . SERVER_NAME . '/">' . SITE_TITLE . '</category>' .
               '<description><![CDATA[' . $this->getName() . ']]></description>' .
               '</item>';
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return '{"name":"' . $this->getName() . '",' .
               '"quotesNumber":' . $this->quotesNumber . ',' .
               '"url":"http://' . SERVER_NAME .'/author?name=' . $this->getSlug() . '&format=json"}';
    }

    /**
     * @param  string                    $slug
     * @return false|\LibreQuotes\author
     */
    public function getBySlug($slug)
    {
        $result = self::get('slugName="' . form::sanitizeSlug($slug) . '"', 1);

        return (sizeof($result) === 1) ? $result[0] : false;
    }

    /**
     * @param  string                $where
     * @param  (integer|string)      $limit
     * @param  string                $order
     * @return \LibreQuotes\author[]
     */
    public function get($where = '', $limit = 1, $order = 'slugName DESC')
    {
        global $db;

        return self::sqlToArray($db->select(author::DB, '*', $where, $limit, $order));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return L(utf8_encode($this->fullName));
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slugName;
    }

    /**
     * @return string
     */
    public function datalist()
    {
        global $page;

        if ($page->format == 'json') return '';

        $data = self::get('', 200, 'quotesNumber DESC');

        foreach ($data as $k => $a)
            $data[$k] = '<option value="' . utf8_encode($a->fullName) . '">' .
                        (($a->getName() != utf8_encode($a->fullName)) ? $a->getName() . '</option>' : '');

        return '<datalist id=famousAuthors>' . implode('', $data) . '</datalist>';
    }
}
