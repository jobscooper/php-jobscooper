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

/**
 * When the geocoder server returns something that we cannot process.
 * @author Bryan Selner <dev@bryanselner.com>
 */
class InvalidServerResponse extends \RuntimeException implements \Geocoder\Exception\ExceptionInterface
{
}

class GoogleGeocoderHttpAdapter extends CurlWrapper  implements \Geocoder\HttpAdapter\HttpAdapterInterface
{

    function getName()
    {
        return "curl";
    }

    function getContent($url)  // used in Google Maps Geocoder only
    {
        $curl_output = $this->cURL($url, $json = null, $action = 'GET', $content_type = null, $pagenum = null, $onbehalf = null, $fileUpload = null, $secsTimeout = null, $cookies = null, $referrer = C__SRC_LOCATION);
        if(array_key_exists('body', $curl_output))
        {
            $json = json_decode($curl_output['body']);
            if($json->status != 'OK') {
                $errMsg = $json->status . " - " . $json->error_message;
                switch ($json->status) {
                    case "REQUEST_DENIED":
                        throw new \Geocoder\Exception\InvalidCredentialsException($errMsg);
                        break;

                    case "OVER_QUERY_LIMIT":
                        throw new \Geocoder\Exception\QuotaExceededException($errMsg);
                        break;

                    case "ZERO_RESULTS":
                        LogWarning("No results were found for the query " . $url);
                        return $curl_output['body'];
                        break;

                    default:
                        throw new InvalidServerResponse($errMsg);
                        break;
                }
            }
            return $curl_output['body'];
        }

        return $curl_output['output'];

    }

}
