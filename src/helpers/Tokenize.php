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



function callTokenizer($inputfile, $outputFile, $keyname, $indexKeyName = null)
{
    LogMessage("Tokenizing title exclusion matches from " . $inputfile . ".");
    if (!$outputFile)
        $outputFile = getOutputDirectory('debug') . "/tempCallTokenizer.csv";
    $PYTHONPATH = realpath(__ROOT__ . "/python/pyJobNormalizer/");
    $cmd = "python " . $PYTHONPATH . "/normalizeStrings.py -i " . $inputfile . " -o " . $outputFile . " -c " . $keyname;
    if ($indexKeyName != null)
        $cmd .= " --index " . $indexKeyName;
    LogMessage("Running command: " . $cmd);

    doExec($cmd);

    LogMessage("Loading tokens for " . $inputfile . ".");
    $file = fopen($outputFile, "r");
    if (is_bool($file)) {
        throw new Exception("Specified input file '" . $outputFile . "' could not be opened.  Aborting.");
    }

    $headers = fgetcsv($file);
    $arrTokenizedList = array();
    while (!feof($file)) {
        $rowData = fgetcsv($file);
        if ($rowData === false) {
            break;
        } else {
            $arrRec = array_combine($headers, $rowData);
            if ($indexKeyName != null)
                $arrTokenizedList[$arrRec[$indexKeyName]] = $arrRec;
            else
                $arrTokenizedList[] = $arrRec;
        }
    }

    fclose($file);

    return $arrTokenizedList;

}

function tokenizeSingleDimensionArray($arrData, $tempFileKey, $dataKeyName = "keywords", $indexKeyName = null)
{
    $inputFile = generateOutputFileName("tmp-" . $tempFileKey . "-token-input.", "csv", true, 'debug');
    $outputFile = generateOutputFileName( "tmp-" . $tempFileKey . "-token-output", "csv", true, 'debug');

    $headers = array($dataKeyName);
    if (array_key_exists($dataKeyName, $arrData)) $headers = array_keys($arrData);

    if (is_file($inputFile)) {
        unlink($inputFile);
    }

    if (is_file($outputFile)) {
        unlink($outputFile);
    }

    $file = fopen($inputFile, "w");
    fputcsv($file, $headers);

    foreach ($arrData as $line) {
        if (is_string($line))
            $line = explode(',', $line);
        fputcsv($file, $line);
    }
    unset($line);

    fclose($file);

    $tmpTokenizedWords = callTokenizer($inputFile, $outputFile, $dataKeyName, $indexKeyName);
    unlink($inputFile);
    unlink($outputFile);

    return $tmpTokenizedWords;
}




