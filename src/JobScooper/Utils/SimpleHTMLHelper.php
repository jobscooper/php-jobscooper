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
namespace JobScooper\Utils;

use DiDom\Document;
use DiDom\Query;
use DiDom\Element;
use DOMElement;

/**
 * Class ExtendedDiDomElement
 * @package JobScooper\Utils
 */
class ExtendedDiDomElement extends Element
{
	/**
	 * @param string $name
	 *
	 * @return null|string
	 */
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

	/**
	 * @return bool
	 */
	function isVisible()
    {
        $elemVisible = true;
        $style = $this->style;
        if(!empty($style))
        {
            $parts = explode(";", $style);
            foreach($parts as $part)
            {
                $kvPair = explode(":", $part);
                if(!empty($kvPair) && count($kvPair) >1) {
                    $key = strtolower($kvPair[0]);
                    $val = strtolower($kvPair[1]);
                    switch ($key) {
                        case "display":
                            if (substr_count_multi($val, array("collapse", "none")))
                                return false;
                            break;

                        case "visibility":
                            if (substr_count_multi($val, array("collapse", "hidden")))
                                return false;
                            break;
                    }
                }}
        }

        if($elemVisible !== false) {
            if (method_exists($this, "parent") && !empty($this->parent()))
            {
                if(method_exists($this->parent(), "getNode"))
                {
                    $parent = new ExtendedDiDomElement($this->parent()->getNode());
                    $elemVisible = $parent->isVisible();
                }
            }

        }

        return $elemVisible;
    }

	/**
	 * @return \DiDom\Document|\JobScooper\Utils\ExtendedDiDomDocument|null
	 */
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

	/**
	 * @param        $expression
	 * @param string $type
	 * @param bool   $wrapElement
	 *
	 * @return array|\DiDom\Element[]|\DOMElement[]|\JobScooper\Utils\ExtendedDiDomElement
	 * @throws \Exception
	 */
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

/**
 * Class ExtendedDiDomDocument
 * @package JobScooper\Utils
 */
class ExtendedDiDomDocument extends Document
{
	/**
	 * @var
	 */
	protected $_sourceUrl;

	/**
	 * @param $name
	 *
	 * @return string
	 */
	function __get($name) {
        switch ($name) {
            case 'text':
                return $this->text();
                break;
            default:
                return parent::getElement()[$name];
        }
    }

	/**
	 * @param $strUrl
	 */
	function setSource($strUrl)
    {
        $this->_sourceUrl = $strUrl;
    }

	/**
	 * @return mixed
	 */
	function getSource()
    {
        return $this->_sourceUrl;
    }

	/**
	 * @param $xpath
	 *
	 * @return array|\DiDom\Element[]|\DOMElement[]|\JobScooper\Utils\ExtendedDiDomElement
	 * @throws \Exception
	 */
	function findByXpath($xpath)
    {
        return $this->find($xpath, Query::TYPE_XPATH);
    }

	/**
	 * @param string $expression
	 * @param string $type
	 * @param bool   $wrapNode
	 * @param null   $contextNode
	 *
	 * @return array|\DiDom\Element[]|\DOMElement[]|\JobScooper\Utils\ExtendedDiDomElement
	 * @throws \Exception
	 */
	function find($expression, $type = Query::TYPE_CSS, $wrapNode = true, $contextNode = null)
    {
        try {
            $ret = parent::find($expression, $type, $wrapNode, $contextNode);
            if (is_array($ret)) {
                $retExt = array();
                foreach ($ret as $elem) {
                    $foundNode = new ExtendedDiDomElement($elem->getNode());
                    if($foundNode->isVisible())
                    {
                        $retExt[] = $foundNode;
                    }
                }
                return $retExt;
            } elseif (is_a($ret, "Element", true)) {
                $foundNode = new ExtendedDiDomElement($ret->getNode());
                if($foundNode->isVisible())
                {
                    return $foundNode;
                }

            }
            throw new \Exception("Invalid return type from ExtendedDiDomDocument->Find");
        }
        catch (\Exception $ex)
        {
            $this->debug_dump_to_file();
            throw $ex;
        }
    }

	/**
	 * @return string
	 */
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
        return $outfile;
    }
}


/**
 * Class SimpleHTMLHelper
 * @package JobScooper\Utils
 */
class SimpleHTMLHelper extends ExtendedDiDomDocument
{
	/**
	 * SimpleHTMLHelper constructor.
	 *
	 * @param $data
	 */
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