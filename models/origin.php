<?php
/**
 * LibreQuotes / Model / (Quotes) origin
 */

namespace LibreQuotes;

class origin extends model
{
    /** @var string */
    const DB = 'lq_origins';

    /** @var integer */
    const NAME_MAXLENGTH = 255;

    /** @var integer mediumint(6) UNSIGNED */
    public $id = 0;

    /** @var string varchar(255) */
    public $name = 'Unknown';

    /** @var string varchar(20) */
    public $type;

    /** @var string varchar(255) DEFAULT NULL */
    public $url;

    /**
     * @param \mysqli_result $data
     */
    public function __construct($data)
    {
        $this->id = empty($data->originId) ? 0 : (int) $data->originId;
        $this->name = $data->name;
        $this->type = $data->type;
        $this->url = $data->url;
    }

    /**
     * @return string
     */
    public function toString()
    {
        global $page;

        if ($page->format == 'json') return $this->toJson();

        $name = $this->getName();

        if (!empty($this->type) && $this->type != 'miscellaneous') $name .= ' (' . L($this->type) . ')';
        return '<em>in</em> ' . (empty($this->url) ? $name : $page->link($this->url, $name, 'target=_blank'));
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return '{"name":"' . L($this->name) . '",' .
               '"type":"' . L($this->type) . '",' .
               '"url":"' . $this->url . '"}';
    }

    /**
     * @param  string                $where
     * @param  (integer|string)      $limit
     * @param  string                $order
     * @return \LibreQuotes\origin[]
     */
    public function get($where = '', $limit = 1, $order = 'name DESC')
    {
        global $db;

        return self::sqlToArray($db->select(origin::DB, '*', $where, $limit, $order));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return L(utf8_encode($this->name));
    }

    /**
     * @return string
     */
    public function datalist()
    {
        global $page;

        if ($page->format == 'json') return '';

        $data = self::get('', 200);

        foreach ($data as $k => $a)
            $data[$k] = '<option value="' . utf8_encode($a->name) . '">' .
                        (($a->getName() != utf8_encode($a->name)) ? $a->getName() . '</option>' : '');

        return '<datalist id=famousOrigins>' . implode('', $data) . '</datalist>';
    }
}
