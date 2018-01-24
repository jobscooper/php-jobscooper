<?php
/**
 * Copyright 2014-18 Bryan Selner
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */
namespace JobScooper\Logging;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class CSVLogHandler extends StreamHandler
{
    private $_streamUri = null;
    private $_formatter = null;

    /**
     * {@inheritdoc}
     */
    function __construct($stream, $level = Logger::DEBUG, $bubble = true, $filePermission = null, $useLocking = false)
    {
        parent::__construct($stream, $level, $bubble, $filePermission, $useLocking);
        $this->_streamUri = stream_get_meta_data($this->stream)['uri'];
        $this->_formatter = new CSVLogFormatter();

    }

    private function _rewriteStreamWithHeader($fileUrl)
    {
        $columns = $this->getDefaultFormatter()->getColumnNames();
        $txtColumns = join(",", $columns) . PHP_EOL;
        file_prepend($txtColumns, $fileUrl);
    }

    /**
     * {@inheritdoc}
     */
    function close()
    {

        parent::close();

        if($this->_streamUri !== false) {
            LogMessage("Writing final CSV error log to {$this->_streamUri}... ");
            $this->_rewriteStreamWithHeader($this->_streamUri);
        }

    }

    function getDefaultFormatter()
    {
        return $this->_formatter;
    }
}