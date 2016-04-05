<?php
/**
 * LibreQuotes / Model / (Quotes) topic
 */

namespace LibreQuotes;

class topic extends model
{
    /** @var string */
    const DB = 'lq_topics';

    /** @var integer */
    const NAME_MAXLENGTH = 30;

    /** @var integer mediumint(6) UNSIGNED */
    public $id = 0;

    /** @var string varchar(30) */
    public $name;

    /** @var string varchar(30) */
    public $slug;

    /** @var integer smallint(4) UNSIGNED */
    public $quotesNumber;

    /** @var string */
    public $url;

    /**
     * @param \mysqli_result $data
     */
    public function __construct($data)
    {
        $this->id = empty($data->topicId) ? 0 : (int) $data->topicId;
        $this->name = $data->name;
        $this->slug = $data->slug;
        $this->quotesNumber = $data->quotesNumber;
        $this->url = 'topic?' . $this->slug;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        global $page;

        return $page->link($this->url, $this->getName());
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return '{"name":"' . $this->getName() . '",' .
               '"quotesNumber":' . $this->quotesNumber . ',' .
               '"url":"' . BASE_URL . 'topic?name=' . $this->getSlug() . '&format=json"}';
    }

    /**
     * @param  string               $where
     * @param  (integer|string)     $limit
     * @param  string               $order
     * @return \LibreQuotes\topic[]
     */
    public function get($where = '', $limit = 1, $order = 'name ASC')
    {
        global $db;

        return self::sqlToArray($db->select(topic::DB, '*', $where, $limit, $order));
    }

    /**
     * @param  string                   $slug
     * @return false|\LibreQuotes\topic
     */
    public function getBySlug($slug)
    {
        $result = self::get('slug="' . form::sanitizeSlug($slug) . '"', 1);

        return (sizeof($result) === 1) ? $result[0] : false;
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
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param  integer|string       $limits
     * @return \LibreQuotes\quote[]
     */
    public function getQuotes($limits = '')
    {
        global $db;

        $sql = $db->select(mark::DB . ' m, ' . quote::DB . ' q, ' . author::DB . ' a, ' . origin::DB . ' o',
                           'q.*, a.slugName, a.fullName, a.quotesNumber, o.name, o.type, o.url',
                           'm.topicId=' . $this->id . ' AND ' .
                           'm.quoteId = q.quoteId AND ' .
                           'q.status="published" AND ' .
                           'q.authorId = a.authorId AND ' .
                           'q.originId = o.originId',
                           ($limits == '') ? ITEM_PER_PAGE : $limits, 'q.submissionDate DESC');

        return self::sqlToArray($sql, '\LibreQuotes\quote');
    }

    /**
     * @return string
     */
    public function datalist()
    {
        global $page;

        if ($page->format == 'json') return '';

        $data = self::get('', 200, 'quotesNumber DESC');

        foreach ($data as $k => $t)
            $data[$k] = '<option value="' . utf8_encode($t->name) . '">' .
                        (($t->getName() != utf8_encode($t->name)) ? $t->getName() . '</option>' : '');

        return '<datalist id=famousTopics>' . implode('', $data) . '</datalist>';
    }
}
