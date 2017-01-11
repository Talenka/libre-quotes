<?php
/**
 * LibreQuotes / Model / Quotes
 */

namespace LibreQuotes;

class Quote extends Model
{
    /** @var string */
    const DB = 'lq_quotes';

    /** @var integer */
    const TEXT_MAXLENGTH = 255;

    /** @var integer */
    const ID_MAX = 1000000;

    /** @var integer mediumint(6) UNSIGNED */
    public $id;

    /** @var string varchar(255) */
    public $text;

    /** @var integer mediumint(6) */
    public $authorId;

    /** @var \LibreQuotes\author */
    public $author;

    /** @var integer mediumint(6) */
    public $originId;

    /** @var \LibreQuotes\origin */
    public $origin;

    /** @var string char(2) */
    public $lang;

    /** @var string enum('submitted', 'refused', 'revised', 'published') */
    public $status;

    /** @var integer int(10) UNIX_TIMESTAMP */
    public $submissionDate;

    /** @var string */
    public $url;

    /**
     * @param  \mysqli_result     $data
     * @return \LibreQuotes\quote
     */
    public function __construct($data)
    {
        $this->id = (int) $data->quoteId;
        $this->text = $data->text;
        $this->authorId = (int) $data->authorId;
        $this->author = new author($data);
        $this->originId = (int) $data->originId;
        $this->origin = new origin($data);
        $this->lang = $data->lang;
        $this->status = $data->status;
        $this->submissionDate = (int) $data->submissionDate;
        $this->url = 'quote?' . $this->id;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return utf8_encode($this->text);
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        global $page;

        return '<q>' . $page->link($this->url, $this->getText()) .
               '<cite>' . $this->author->toString() . '</cite></q>';
    }

    /**
     * @return string
     */
    public function toAtom()
    {
        return '<item>' .
               '<title>' . $this->getText() . '</title>' .
               '<guid isPermaLink="false">' . BASE_URL . $this->url . '</guid>' .
               '<link>' . BASE_URL . $this->url . '</link>' .
               '<pubDate>' . date('r', $this->submissionDate) . '</pubDate>' .
               '<category domain="' . BASE_URL . '">' . SITE_TITLE . '</category>' .
               '<description><![CDATA[' . $this->author->getName() . ']]></description>' .
               '</item>';
    }

    /**
     * @return string
     */
    public function toJson()
    {
        $t = $this->getTopics();

        for ($i = 0, $j = sizeof($t); $i < $j; $i++) {
            $t[$i] = $t[$i]->toJson();
        }

        return '{"id":' . $this->id . ',' .
               '"url":"' . BASE_URL . 'quote?id=' . $this->id . '&format=json",' .
               '"text":"' . $this->getText() . '",' .
               '"author":' . $this->author->toJson() . ',' .
               '"origin":' . $this->origin->toJson() . ',' .
               '"lang":"' . $this->lang . '",' .
               '"submissionDate":"' . date('r', $this->submissionDate) . '",' .
               '"topics":[' . implode(',', $t) . ']}';
    }

    /**
     * @return string
     */
    public function toBlock()
    {
        global $page;

        if ($page->format == 'json') {
            return $this->toJson();
        }

        return '<blockquote>' .
               '<p>' . $page->link($this->url, $this->getText()) . '</p>' .
               '<cite>' . $this->author->toString() .
               (($this->origin->name == 'Unknown') ? '' : ' ' . $this->origin->toString()) .
               '</cite>' .
               '</blockquote>';
    }

    /**
     * @param  string               $where
     * @param  (integer|string)     $limit
     * @param  string               $order
     * @return \LibreQuotes\quote[]
     */
    public function get($where = '', $limit = 1, $order = 'q.submissionDate DESC')
    {
        global $db;

        $sql = $db->select(
            quote::DB . ' q, ' . author::DB . ' a, ' . origin::DB . ' o',
            'q.*, a.slugName, a.fullName, a.quotesNumber, o.name, o.type, o.url',
            (empty($where) ? '' : $where . ' AND ') .
            'q.status="published" AND q.authorId = a.authorId AND q.originId = o.originId',
            $limit,
            $order
        );

        return self::sqlToArray($sql);
    }

    /**
     * @param  integer                  $id
     * @return false|\LibreQuotes\quote
     */
    public function getById($id = 0)
    {
        $result = self::get('q.quoteId=' . Form::clampInt($id, 1, self::ID_MAX), 1);

        return (sizeof($result) === 1) ? $result[0] : false;
    }

    /**
     * @return \LibreQuotes\topic[]
     */
    public function getTopics()
    {
        global $db;

        $sql = $db->select(
            mark::DB . ' m, ' . Topic::DB . ' t',
            't.*',
            'm.quoteId=' . $this->id . ' AND m.topicId = t.topicId',
            100,
            't.quotesNumber DESC'
        );

        return self::sqlToArray($sql, '\LibreQuotes\topic');
    }
}
