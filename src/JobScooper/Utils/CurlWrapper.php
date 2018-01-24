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

const C__API_RETURN_TYPE_OBJECT__ = 33;
const C__API_RETURN_TYPE_ARRAY__ = 44;


class CurlWrapper
{

    /****************************************************************************************************************/
    /****                                                                                                        ****/
    /****         Helper Functions:  Utility Functions                                                           ****/
    /****                                                                                                        ****/
    /****************************************************************************************************************/

    private $fDebugLogging = false;

    function __construct()
    {
        $this->fDebugLogging = isDebug();
    }

    function setDebug($fDebug = true)
    {
        $this->fDebugLogging = $fDebug;
    }

    private function __handleCallback__($callback, &$val, $fReturnType = C__API_RETURN_TYPE_OBJECT__ )
    {

        if($fReturnType == C__API_RETURN_TYPE_ARRAY__)
        {
            $val =  json_decode(json_encode($val, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE), true);
        }

        if ($callback && is_callable($callback))
        {
            call_user_func_array($callback, array(&$val));
        }

        if($fReturnType == C__API_RETURN_TYPE_ARRAY__)
        {
            $val = json_decode(json_encode($val, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE), false);
        }
    }

    function getObjectsFromAPICall( $baseURL, $objName = "", $fReturnType = C__API_RETURN_TYPE_OBJECT__, $callback = null, $pagenum = 0)
    {
        $retData = null;

        $curl_obj = $this->cURL($baseURL, "", "GET", "application/json", $pagenum);

        $srcdata = json_decode($curl_obj['output']);
        if(isset($srcdata))
        {
            if($objName == "")
            {
                if($callback != null)
                {
                    $this->__handleCallback__($callback, $srcdata, $fReturnType);
                }
                $retData = $srcdata;
            }
            else
            {

                foreach($srcdata->$objName as $key => $value)
                {
                    $this->__handleCallback__($callback, $value, $fReturnType);
                    $retData[$key] = $value;
                }

                //
                // If the data returned has a next_page value, then we have more results available
                // for this query that we need to also go get.  Do that now.
                //
                if(isset($srcdata->next_page))
                {
                    if($this->fDebugLogging == true) { LogMessage('Multipage results detected. Getting results for ' . $srcdata->next_page . '...' . PHP_EOL); }

                    // $patternPage = "/.*page=([0-9]{1,})/";
                    $patternPagePrefix = "/.*page=/";
                    // $pattern = "/(\/api\/v2\/).*/";
                    $pagenum = preg_replace($patternPagePrefix, "", $srcdata->next_page);
                    $retSecondary = $this->getObjectsFromAPICall($baseURL, $objName, null, null, $pagenum);

                    //
                    // Merge the primary and secondary result sets into one result
                    // before return.  This allows for multiple page result sets from Zendesk API
                    //

                    foreach($retSecondary as $moreKey => $moreVal)
                    {
                        $this->__handleCallback__($callback, $moreVal, $fReturnType);
                        $retData[$moreKey] = $moreVal;
                    }
                }
            }
        }


        switch ($fReturnType)
        {
            case  C__API_RETURN_TYPE_ARRAY__:
                $retData = json_decode(json_encode($retData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE), true);
                break;


            case  C__API_RETURN_TYPE_OBJECT__:
            default:
                // do nothing;
                break;
        }


        return $retData;
    }



    function cURL($full_url, $json = null, $action = 'GET', $content_type = null, $pagenum = null, $onbehalf = null, $fileUpload = null, $secsTimeout = null, $cookies = null, $referrer = null)
    {
        if(!isset($secsTimeout))
        {
            $secsTimeout= 30;
        }

        $curl_object = array('input_url' => '', 'actual_site_url' => '', 'error_number' => 0, 'output' => '', 'output_decoded'=>'', 'cookies'=>null, 'headers'=>null);

        if($pagenum > 0)
        {
            $full_url .= "?page=" . $pagenum;
        }
        $header = array();
        if(!is_null($onbehalf)) $header[] = 'X-On-Behalf-Of: ' . $onbehalf;
        if(!is_null($content_type)) $header[] = 'Content-type: ' . $content_type;
        if(!is_null($content_type)) $header[] = 'Accept: ' . $content_type;

        $ch = curl_init();
        if(!is_null($referrer)) curl_setopt($ch, CURLOPT_REFERER, $referrer);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
        curl_setopt($ch, CURLOPT_URL, $full_url);
        curl_setopt($ch, CURLOPT_USERAGENT, \C__STR_USER_AGENT__);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $secsTimeout);
        curl_setopt($ch, CURLOPT_VERBOSE, $this->fDebugLogging);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);

        // curlWrapNew = only?
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        if($cookies)
            curl_setopt($ch, CURLOPT_COOKIE,  $cookies);


        switch($action)
        {
            case "POST":

                if($fileUpload != null)
                {
                    $fileh = fopen($fileUpload, 'r');
                    $size = filesize($fileUpload);
                    $fildata = fread($fileh,$size);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $fildata);
                    curl_setopt($ch, CURLOPT_INFILE, $fileh);
                    curl_setopt($ch, CURLOPT_INFILESIZE, $size);
                }
                else
                {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                }
                break;
            case "GET":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                break;
            case "PUT":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                break;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);


        $output = curl_exec($ch);
        $curl_info = curl_getinfo($ch);

        $header_size = $curl_info['header_size'];
        $header = substr($output, 0, $header_size);
        $headerlines = explode(PHP_EOL, $header );
        $body = substr($output, $header_size);
        foreach ($headerlines as $line) {
            $exploded = explode(':', $line);
            if(count($exploded) > 1)
                $curl_object['headers'][$exploded[0]] = $exploded[1];
        }


        preg_match_all('|Set-Cookie: (.*);|U', $header, $results);
        $cookies = implode(';', $results[1]);

        $curl_object['cookies'] = $cookies;
        $curl_object['input_url'] = $full_url;
        $last_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        $curl_object['actual_site_url'] = $last_url;
        $curl_object['body'] = $body;
        $curl_object = array_merge($curl_object, $curl_info);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        /* If the document has loaded successfully without any redirection or error */
        if ($httpCode < 200 || $httpCode >= 400)
        {
            $strErr = "CURL received an HTTP error #". $httpCode;
            $curl_object['http_error_number'] = $httpCode;
            $curl_object['error_number'] = -1;
            curl_close($ch);
            throw new \ErrorException($strErr, E_RECOVERABLE_ERROR );
        }
        elseif (curl_errno($ch))
        {
            $strErr = 'Error #' . curl_errno($ch) . ': ' . curl_error($ch);
            $curl_object['error_number'] = curl_errno($ch);
            $curl_object['output'] = curl_error($ch);
            curl_close($ch);
            throw new \ErrorException($strErr,curl_errno($ch),E_RECOVERABLE_ERROR );
        }
        else
        {
            $curl_object['output'] = $body;
            curl_close($ch);
        }

        return $curl_object;

    }

}


