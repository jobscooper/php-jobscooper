<?php
/**
 * Created by PhpStorm.
 * User: bryan
 * Date: 10/11/17
 * Time: 11:01 AM
 */

namespace JobScooper\Logging;

/**
 * Formats incoming records into a one-line string optimized for CSV file usage
 *
 * @author Bryan Selner <dev@bryanselner.com>
 */
class CSVLogFormatter extends \Monolog\Formatter\LineFormatter
{
    private $_fpcsv = null;
    private $_delimiter = ",";
    private $_columns = array('datetime', 'level');
    private $_format = '"%datetime%","%level_name%",';

    /**
     * {@inheritdoc}
     */
    public function __construct($format = null, $dateFormat = null, $allowInlineLineBreaks = false, $ignoreEmptyContextAndExtra = false)
    {
        parent::__construct($this->_format, static::SIMPLE_DATE, true, true);
        // output up to 5MB is kept in memory, if it becomes bigger it will automatically be written to a temporary file
    }

    /**
     * {@inheritdoc}
     */
    function __destruct()
    {
        if(!is_null($this->_fpcsv))
            fclose($this->_fpcsv);
    }

    function getColumnNames()
    {
        return $this->_columns;
    }

    /**
     * {@inheritdoc}
     */

    private function _getValueArray($k, $v)
    {
        $ret = array();

        if ($v instanceof \DateTime) {
            $this->checkColumnName($k);
            $ret[$k] = $v->format(static::SIMPLE_DATE);
        } elseif (is_array($v)) {
            foreach ($v[$k] as $key => $val) {
                $r = $this->_getValueArray($key, $val);
                $ret = array_merge_recursive_distinct($ret, $r);
            }
        }
        else
        {
            $this->checkColumnName($k);
            $ret[$k] = strval($v);
        }

    }
    public function format(array $record)
    {
        $outRecord = array_fill_keys($this->getColumnNames(), null);

        foreach ($record as $k => $v) {
            if ($v instanceof \DateTime) {
                $this->checkColumnName($k);
                $outRecord[$k] = $v->format(static::SIMPLE_DATE);
            } elseif (is_array($v)) {
                foreach ($record[$k] as $key => $val) {
                    $this->checkColumnName($key);
                    $outRecord[$key] = strval($val);
                }
                unset($record[$k]);
            }
            elseif(is_object($v))
            {
                foreach (object_to_array($v) as $key => $val) {
                    $this->checkColumnName($key);
                    $outRecord[$key] = strval($val);
                }
            }
            else {
                $this->checkColumnName($k);
                $outRecord[$k] = strval($v);
            }
        }

        $this->_columns = array_keys($outRecord);

        $fptmp = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');
        fseek($fptmp, 0);
        fputcsv($fptmp, $outRecord, $delimiter = $this->_delimiter, $enclosure = '"', $escape_char = "\\");
        rewind($fptmp);
        // put it all in a variable
        $str = stream_get_contents($fptmp);
        fclose($fptmp);
        return $str;

    }

    /**
     * We intend using the data included with a log entry as CSV data, likely
     * displaying it in a spreadsheet.  This method checks to make sure we have
     * the column name already or adds it if we do not.
     *
     * @param  $columnName
     */
    private function checkColumnName($columnName)
    {
        if(!in_array(strval($columnName), $this->_columns))
            $this->_columns[] = strval($columnName);
    }
}
