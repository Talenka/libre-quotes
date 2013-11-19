<?php
/**
 * LibreQuotes / Core / Model
 */

namespace LibreQuotes;

class model
{
    /**
     * @param  object[] $items
     * @return string[]
     */
    public function toStrings($items)
    {
        for ($i = 0, $j = sizeof($items); $i < $j; $i++) $items[$i] = $items[$i]->toString();

        return $items;
    }

    /**
     * @param  \mysqli_result
     * @return \LibreQuotes\model[]
     */
    public function sqlToArray($sql, $obj = '')
    {
        $result = array();

        if ($obj == '') $obj = get_called_class();

        while ($q = $sql->fetch_object()) array_push($result, new $obj($q));

        $sql->free();

        return $result;
    }
}
