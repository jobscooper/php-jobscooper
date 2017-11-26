<?php
/**
 * Copyright 2014-17 Bryan Selner
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
namespace JobScooper\Utils;

use DiDom\Document;
use DiDom\Query;
use DiDom\Element;
use DOMElement;

class ExtendedDiDomElement extends Element
{
    function __get($name) {
        switch ($name) {
            case 'text':
                return $this->text();
                break;
            case 'sourceUrl':
                return $this->getDocument()->getSource();
                break;
            default:
                return parent::__get($name);
        }
    }

    function getDocument()
    {
        if ($this->node->ownerDocument === null) {
            return null;
        }

        return new ExtendedDiDomDocument($this->node->ownerDocument);
    }

    /**
     * Get the DOM document with the current element.
     *
     * @param string $encoding The document encoding
     *
     * @return \DiDom\Document
     */
    public function toDocument($encoding = 'UTF-8')
    {
        $document = new ExtendedDiDomDocument(null, false, $encoding);

        $document->appendChild($this->node);

        return $document;
    }

    function find($expression, $type = Query::TYPE_CSS, $wrapElement = true)
    {
        $ret = parent::find($expression, $type, $wrapElement);
        if (is_array($ret)) {
            $retExt = array();
            foreach ($ret as $elem) {
                $retExt[] = new ExtendedDiDomElement($elem->getNode());
            }
            return $retExt;
        } elseif (is_a($ret, "Element", true)) {
            return new ExtendedDiDomElement($ret->getNode());
        }
        throw new \Exception("Invalid return type from DiDom->Find");
    }

}

class ExtendedDiDomDocument extends Document
{
    protected $_sourceUrl;

    function __get($name) {
        switch ($name) {
            case 'text':
                return $this->text();
                break;
            default:
                return parent::getElement()[$name];
        }
    }

    function setSource($strUrl)
    {
        $this->_sourceUrl = $strUrl;
    }

    function getSource()
    {
        return $this->_sourceUrl;
    }

    function findByXpath($xpath)
    {
        return $this->find($xpath, Query::TYPE_XPATH);
    }

    function find($expression, $type = Query::TYPE_CSS, $wrapNode = true, $contextNode = null)
    {
        try {
            $ret = parent::find($expression, $type, $wrapNode, $contextNode);
            if (is_array($ret)) {
                $retExt = array();
                foreach ($ret as $elem) {
                    $retExt[] = new ExtendedDiDomElement($elem->getNode());
                }
                return $retExt;
            } elseif (is_a($ret, "Element", true)) {
                return new ExtendedDiDomElement($ret->getNode());
            }
            throw new \Exception("Invalid return type from ExtendedDiDomDocument->Find");
        }
        catch (\Exception $ex)
        {
            $this->debug_dump_to_file();
            throw $ex;
        }
    }

    function debug_dump_to_file()
    {
        $src = $this->getSource();
        if(empty($src))
            $basefile = "debug_dump_" . uniqid();
        else {
            $parsed_url = parse_url($src);
            $basefile = preg_replace('/[^\w]/', '_', $parsed_url['host'] . $parsed_url['path']);
        }
        $outfile = generateOutputFileName($basefile, "html", true, 'debug');
        file_put_contents($outfile, $this->html());
    }
}


class SimpleHTMLHelper extends ExtendedDiDomDocument
{
    function __construct($data)
    {
        $isFile = false;
        $string = $data;

        if(is_string($data))
        {
            if(strncasecmp($data, "http", strlen("http")) === 0)
            {
                $isFile = true;
                $string = $data;
            }
            elseif(is_file($data) === true)
            {
                $isFile = true;
                $string = $data;
            }
            else
            {
                $string = $data;
                $isFile = false;
            }
        }
        elseif(is_object($data) === true) {
            $string = strval($data);
            $isFile = false;
        }

        parent::__construct($string, $isFile);
        if($isFile)
            $this->setSource($string);
    }

}