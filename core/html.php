<?php
/**
 * LibreQuotes / Core / Html
 */

namespace LibreQuotes;

class html
{
    /** @var string */
    private $title = '';

    /** @var string */
    private $content = '';

    /** @var string[] */
    private $navigation = array();

    /** @var string */
    private $pagination = '';

    /** @var integer */
    private $paginationCurrentPage = 1;

    public $format = 'html';

    public function __construct()
    {
        $menu = array('topics' => L('Topics'),
                      'authors' => L('Authors'),
                      'topics' => L('Topics'),
                      'authors' => L('Authors'),
                      'search' => L('Search'),
                      'random' => L('Random'),
                      'about' => L('About'),
                      'submit' => L('Submit'));

        foreach ($menu as $url => $name)
            if (strpos(PHP_FILE, $url) === false)
                array_push($this->navigation, "<a href=$url>$name</a>");

        if (!empty($_GET['format'])) $this->format = $_GET['format'];
    }

    /**
     * @param  string            $title
     * @return \LibreQuotes\html
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param  string            $html
     * @return \LibreQuotes\html
     */
    public function addContent($html)
    {
        $this->content .= $html;

        return $this;
    }

    /**
     * @param  string[]          $items
     * @return \LibreQuotes\html
     */
    public function addList($items)
    {
        $this->addContent($this->ulist($items));

        return $this;
    }

    /**
     * @param  string            $title
     * @param  string            $content
     * @return \LibreQuotes\html
     */
    public function addSection($title, $content = '')
    {
        if ($this->format == 'json')
            return $this->addContent('{"' . $title . '":' . (empty($content) ? '{}' : $content) . '}');

        return $this->addContent('<h2>' . $title . '</h2>' . $content);
    }

    /**
     * @param  string $url
     * @param  string $title
     * @param  string $attributes
     * @return string
     */
    public function link($url, $title, $attributes = '')
    {
        return '<a href="' . $url . '"' . (empty($attributes) ? '' : ' ' . $attributes) . '>' . $title . '</a>';
    }

    /**
     * @param  string[] $items
     * @return string
     */
    public function ulist($items)
    {
        foreach ($items as $k => $item)
            if ($item instanceof \LibreQuotes\model) $items[$k] = $item->toString();

        if ($this->format == 'json') return '{"items":[' . implode(',', $items) . ']}';

        return '<ul><li>' . implode('</li><li>', $items) . '</li></ul>';
    }

    /**
     * @return string
     */
    public function render()
    {
        global $lang;

        if ($this->format == 'json') header('Content-Type: application/json; charset=UTF-8');
        else header('Content-Type: text/html; charset=UTF-8');

        $replacements = array('{pageTitle}' => $this->title,
                              '{siteTitle}' => SITE_TITLE,
                              '{mainNavigation}' => $this->buildNavigation(),
                              '{pagination}' => $this->pagination,
                              '{content}' => $this->content,
                              '{lang}' => $lang,
                              "\n" => '');

        if ($this->format == 'json') $output = file_get_contents('views/pageLayout.json');

        else $output = file_get_contents('views/pageLayout.html');

        $output = str_replace(array_keys($replacements), array_values($replacements), $output);

        echo $output;

        return $output;
    }

    /**
     * @return string
     */
    private function buildNavigation()
    {
        return implode(' ', $this->navigation);
    }

    /**
     * @param  string            $item
     * @return \LibreQuotes\html
     */
    public function addNavigation($item)
    {
        array_push($this->navigation, $item);

        return $this;
    }

    /**
     * Redirect the user to another page via HTTP header with HTML/JS fallback.
     *
     * @param string  $url        Where to redirect the user.
     * @param integer $statusCode optionnal redirection HTTP status code.
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function redirectTo($url, $statusCode = 200)
    {
        $statusCodes = array(
            301 => '301 Moved Permanently',
            307 => '307 Temporary Redirect',
            400 => '400 Bad Request',
            401 => '401 Unauthorized',
            403 => '403 Forbidden',
            404 => '404 Not Found',
            429 => '429 Too Many Requests',
            500 => '500 Internal Server Error');

        if (isset($statusCodes[$statusCode]))
            header('Status: ' . $statusCodes[$statusCode], false, $statusCode);

        if ($statusCode == 301) header('Location: ' . $url, false);

        else echo '<!doctype html><script>window.location="' . $url . '";</script>',
                  L('If nothing happen, '),
                  $this->link($url, L('click here to continue'));

        exit;
    }

    public function notFound()
    {
        $this->redirectTo('search?q=' . urlencode(URL_PARAMS), 404);
    }

    /**
     * Returns the cache file path
     *
     * @param  string $id Content identifier
     * @return string
     */
    private function cacheFilePath($id)
    {
        global $lang;

        return CACHE_PATH . PHP_FILE . '_' . $id . '_' . $lang . '.htm';
    }

    /**
     * Caches content in a static file (in the /cache/ directory)
     *
     * @param  string $id      Content identifier
     * @param  string $content Something to cache
     * @return string The content
     */
    public function cache($id, $content = '')
    {
        file_put_contents($this->cacheFilePath($id), $content, LOCK_EX);

        return $content;
    }

    /**
     * Retrieves content from
     *
     * @param  string       $id   Content identifier
     * @param  integer      $term content expiration (in seconds)
     * @return string|false The cached content (or false if there is no cache)
     */
    public function getFromCache($id, $term = 300)
    {
        $cFile = $this->cacheFilePath($id);

        return (!file_exists($cFile) || filemtime($cFile) < NOW - $term) ? false : file_get_contents($cFile);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param  array   $params
     * @param  string  $label
     * @param  boolean $current
     * @return string
     */
    private function paginationItem($params, $label, $current = false)
    {
        return $this->link(substr(PHP_FILE, 0, -4) . '?' . http_build_query($params, '', '&amp;'),
                           $label, $current ? 'class=a' : '');
    }

    /**
     * @param  integer           $itemsNumber
     * @param  array             $params
     * @return \LibreQuotes\html
     */
    public function paginate($itemsNumber = 1, $params = array())
    {
        $maxPage = ceil($itemsNumber / ITEM_PER_PAGE);

        if ($maxPage == 1) return $this;

        $currentPage = empty($_GET['page']) ? 1 : form::clampInt($_GET['page'], 1, $maxPage);

        $this->paginationCurrentPage = $currentPage;

        $result = '';

        if ($currentPage > 1) {

            $params['page'] = ($currentPage > 2) ? $currentPage - 1 : null;

            $result .= $this->paginationItem($params, '◀');
        }

        for ($i = max(1, $currentPage - 3); $i <= min($maxPage, $currentPage + 3); $i++) {

            $params['page'] = ($i > 1) ? $i : null;

            $result .= $this->paginationItem($params, $i, ($i == $currentPage) ? true : false);
        }

        if ($currentPage < $maxPage) {

            $params['page'] = $currentPage + 1;

            $result .= $this->paginationItem($params, '▶');
        }

        return $this->addPagination('<nav>' . $result . '</nav>');
    }

    /**
     * @param  string            $itemsNumber
     * @return \LibreQuotes\html
     */
    public function addPagination($html)
    {
        $this->pagination .= $html;

        return $this;
    }

    /**
     * @return string
     */
    public function paginationLimits()
    {
        return (ITEM_PER_PAGE * ($this->paginationCurrentPage - 1)) . ',' . ITEM_PER_PAGE;
    }

    /**
     * @param  integer           $expires
     * @return \LibreQuotes\html
     */
    public function setExpiration($expires = 0)
    {
        header('Last-Modified: ' . date('r', NOW));
        header('Expires: ' . date('r', NOW + $expires));
        header('Cache-Control: max-age=' . $expires);

        return $this;
    }

    /**
     * @param  integer $expires
     * @param  string  $param
     * @return string
     */
    public function renderFromCache($expires = 0, $params = '')
    {
        $this->setExpiration($expires);

        $cacheContent = $this->getFromCache(form::sanitizeSlug($params));

        if ($cacheContent !== false) {
            header('Content-Type: text/html; charset=UTF-8');
            echo $cacheContent;
        }

        return $cacheContent;
    }

    /**
     * @param  integer $expires
     * @param  string  $param
     * @return string
     */
    public function cacheWholePage($expires = 0, $params = '')
    {
        $this->setExpiration($expires);

        $this->cache(form::sanitizeSlug($params), $this->render());
    }
}
