<?php
/**
 * LibreQuotes / Core / Database
 */

namespace LibreQuotes;

class database
{
    /** @var \mysqli */
    private $dbObject;

    /**
     * @param \mysqli $dbObject
     */
    public function __construct($dbObject)
    {
        if ($dbObject->connect_error)
            throw new error($dbObject->connect_error, $dbObject->connect_errno);

        $this->dbObject = $dbObject;
    }

    /**
     * @param  string  $str
     * @param  boolean $leaveSimpleQuote
     * @return string
     */
    public function escapeString($str, $leaveSimpleQuote = false)
    {
        $str = $this->dbObject->real_escape_string($str);

        return $leaveSimpleQuote ? str_replace("\\'", "'", $str) : $str;
    }

    /**
     * @param  string         $query
     * @return \mysqli_result
     */
    private function execute($query)
    {
        $result = $this->dbObject->query($query);

        if ($result === false)
            throw new error("Problem with sql query : “" . $query . "” (" . $this->dbObject->error . ")");

        else {

            $this->insert_id = $this->dbObject->insert_id;

            return $result;
        }
    }

    /**
     * Insert entry in database
     * @param  string         $table   SQL table name.
     * @param  string         $columns Coma-separated columns list
     * @param  string         $values  Coma-separated data list
     * @return \mysqli_result
     */
    public function insert($table, $columns, $values)
    {
        return $this->execute("INSERT INTO $table ($columns) VALUES($values)");
    }

    /**
     * Execute a SQL selection query
     * @param  string         $table SQL table name.
     * @param  string         $cols  List of columns to return (all by default).
     * @param  string         $where Conditionnal filter.
     * @param  string|integer $limit Maximum number of result returned (or a range).
     * @param  string         $order "ASC"ending or "DESC"ending order
     * @return \mysqli_result SQL ressource
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function select($table, $cols = '*', $where = '', $limit = '', $order = '')
    {
        return $this->execute('SELECT ' . $cols . ' FROM ' . $table .
                              (empty($where) ? '' : ' WHERE ' . $where) .
                              (empty($order) ? '' : ' ORDER BY ' . $order) .
                              (empty($limit) ? '' : ' LIMIT ' . $limit));
    }

    /**
     * Count items from an SQL table with optionnal filter
     * @param  string  $table SQL table name.
     * @param  string  $where Optionnal conditionnal filter.
     * @return integer Number of row in the table satisfying the WHERE condition.
     */
    public function selectCount($table, $where = '')
    {
        $sql = $this->select($table, 'COUNT(*) as n', $where);

        $result = (int) $sql->fetch_object()->n;

        $sql->free();

        return $result;
    }

    /**
     * Update database entry
     * @param  string         $table  SQL table name.
     * @param  string         $sets   modifications
     * @param  string         $where  conditions
     * @param  integer        $limits maximum modified entries
     * @return \mysqli_result
     */
    public function update($table, $sets, $where, $limits = 1)
    {
        return $this->execute("UPDATE $table SET $sets WHERE $where LIMIT $limits");
    }
}
