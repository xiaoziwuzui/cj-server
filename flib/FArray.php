<?php

class FArray {
    var $array;

    public static function getCol($array, $col) {
        $step = 0;

        $new_array = array();
        foreach ($array as $key => $a) {
            if ($step == 0) {
                $cols = array_keys($a);
            }

            $new_array[$key] = $a[$col];
        }

        return $new_array;
    }

    public static function getInstance($array) {
        static $fArray = null;

        if (!$fArray) {
            $fArray = new self($array);
        }

        return $fArray;
    }

    public function __construct($array) {
        $this->array = $array;
    }

    public function getByPage($page, $limit) {
        if (!$this->array) {
            return null;
        }

        $counter = 0;
        $from = ($page - 1) * $limit;

        $index = 0;
        $retData = array();
        foreach ($this->array as $row) {
            $index++;

            if ($index <= $from) {
                continue;
            }

            if ($counter >= $limit) break;

            $retData[] = $row;
            $counter++;

        }

        return $retData;
    }

    /**
     * 数组排序
     * @param $arr
     * @param $keys
     * @param string $type
     * @return array
     */
    public static  function array_sort($arr, $keys, $type = 'asc')
    {
        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v) {
            $keysvalue[$k] = $v[$keys];
        }
        if ($type == 'asc') {
            asort($keysvalue);
        } else {
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }

    /**
     * 低于5。5版本的PHP array_column
     * @param $input 原数组
     * @param $columnKey 数组列
     * @param null $indexKey 索引列
     * @return array
     */
    public static function array_column($input, $columnKey, $indexKey = NULL)
    {
        if (!function_exists('array_column')) {
            $columnKeyIsNumber = (is_numeric($columnKey)) ? true : false;
            $indexKeyIsNull = (is_null($indexKey)) ? true : false;
            $indexKeyIsNumber = (is_numeric($indexKey)) ? true : false;
            $result = array();
            foreach ((array)$input as $key => $row) {
                if ($columnKeyIsNumber) {
                    $tmp = array_slice($row, $columnKey, 1);
                    $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : null;
                } else {
                    $tmp = isset($row[$columnKey]) ? $row[$columnKey] : null;
                }
                if (!$indexKeyIsNull) {
                    if ($indexKeyIsNumber) {
                        $key = array_slice($row, $indexKey, 1);
                        $key = (is_array($key) && !empty($key)) ? current($key) : null;
                        $key = is_null($key) ? 0 : $key;
                    } else {
                        $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                    }
                }
                $result[$key] = $tmp;
            }
            return $result;
        } else {
            return array_column($input, $columnKey, $indexKey);
        }
    }
}
