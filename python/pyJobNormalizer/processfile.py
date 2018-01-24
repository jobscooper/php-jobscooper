#!/bin/python
#  -*- coding: utf-8 -*-
#
###########################################################################
#
#  Copyright 2014-18 Bryan Selner
#
#  Licensed under the Apache License, Version 2.0 (the "License"); you may
#  not use this file except in compliance with the License. You may obtain
#  a copy of the License at
#
#      http://www.apache.org/licenses/LICENSE-2.0
#
#  Unless required by applicable law or agreed to in writing, software
#  distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
#  WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
#  License for the specific language governing permissions and limitations
#  under the License.
###########################################################################

from nltk.corpus import stopwords
import unicodecsv
from nltk.stem.snowball import SnowballStemmer
snowstemmer = SnowballStemmer("english")
stopwrds = stopwords.words('english')



states = {
        'AK': 'Alaska',
        'AL': 'Alabama',
        'AR': 'Arkansas',
        'AS': 'American Samoa',
        'AZ': 'Arizona',
        'CA': 'California',
        'CO': 'Colorado',
        'CT': 'Connecticut',
        'DC': 'District of Columbia',
        'DE': 'Delaware',
        'FL': 'Florida',
        'GA': 'Georgia',
        'GU': 'Guam',
        'HI': 'Hawaii',
        'IA': 'Iowa',
        'ID': 'Idaho',
        'IL': 'Illinois',
        'IN': 'Indiana',
        'KS': 'Kansas',
        'KY': 'Kentucky',
        'LA': 'Louisiana',
        'MA': 'Massachusetts',
        'MD': 'Maryland',
        'ME': 'Maine',
        'MI': 'Michigan',
        'MN': 'Minnesota',
        'MO': 'Missouri',
        'MP': 'Northern Mariana Islands',
        'MS': 'Mississippi',
        'MT': 'Montana',
        'NA': 'National',
        'NC': 'North Carolina',
        'ND': 'North Dakota',
        'NE': 'Nebraska',
        'NH': 'New Hampshire',
        'NJ': 'New Jersey',
        'NM': 'New Mexico',
        'NV': 'Nevada',
        'NY': 'New York',
        'OH': 'Ohio',
        'OK': 'Oklahoma',
        'OR': 'Oregon',
        'PA': 'Pennsylvania',
        'PR': 'Puerto Rico',
        'RI': 'Rhode Island',
        'SC': 'South Carolina',
        'SD': 'South Dakota',
        'TN': 'Tennessee',
        'TX': 'Texas',
        'UT': 'Utah',
        'VA': 'Virginia',
        'VI': 'Virgin Islands',
        'VT': 'Vermont',
        'WA': 'Washington',
        'WI': 'Wisconsin',
        'WV': 'West Virginia',
        'WY': 'Wyoming'
}


def writedicttocsv(csvFileName, data, keys=None):
    """

    Args:
        csvFileName:
        data:
        keys:

    Returns:

    """
    print "Writing " + str(len(data)) + " rows to file " + csvFileName +"..."
    if keys is None:
        item = data.itervalues().next()
        keys = item.keys()

    csvfile = open(csvFileName, "wb")
    csv_writer = unicodecsv.DictWriter(csvfile, fieldnames=keys, dialect=unicodecsv.excel)
    csv_writer.writeheader()
    for row in data:
        for k in data[row].keys():
            if k not in keys:
                del data[row][k]
        try:
            csv_writer.writerow(data[row])
        except Exception:
            pass

    csvfile.close()
    return csvFileName

def loadCSV(csvFileName, rowKeyName = None):
    """

    Args:
        csvFileName:
        rowKeyName:

    Returns:

    """

    print "Loading " + csvFileName
    csv_fp = open(csvFileName, "rbU")
    dictRecords = {}
    fields = {}

    csv_reader = None
    try:
        with csv_fp:
            csv_reader = unicodecsv.DictReader(csv_fp, delimiter=",", quoting=unicodecsv.QUOTE_ALL, errors='strict')
            fields = csv_reader.fieldnames
            for row in csv_reader:
                if rowKeyName is None:
                    rowKeyName = fields[0]

                dictRecords[row[rowKeyName]] = row
    except Exception as err:
        print err
        pass

    print "Loaded " + str(len(dictRecords)) + " rows from " + csvFileName

    return { 'fieldnames' : fields, 'dict' : dictRecords }

import os
filepath = os.path.dirname(os.path.abspath(__file__)) # /a/b/c/d/e

abbrevfile = os.path.join(filepath, "static", "job-title-abbreviations.csv")
expandWords = loadCSV(abbrevfile, "abbreviation")['dict']

def tokenizeStrings(listData, field, fieldTokenized = "tokenized", retType="string"):
    """

    Args:
        listData:
        field:
        fieldTokenized:

    Returns:

    """

    for k in listData.keys():
        if len(k) == 0:
            print "String value for key was empty.  Skipping..."
            continue

        tokens = getScrubbedStringTokens(listData[k][field])

        if retType == "list":
            listData[k][fieldTokenized] = tokens

        elif retType == "set":
                listData[k][fieldTokenized] = set(tokens)
        else:
            # if retType == "string" or retType is None:
            listData[k][fieldTokenized] = "|{}|".format("|".join(tokens))

    return listData

import nltk
import string
# NOTE:  Need to run the download once per machine to get the dictionaries
# nltk.download()

def removeStopWords(listwords):
    """

    Args:
        listwords:

    Returns:

    """
    retwords = [i for i in listwords if i not in stopwrds]
    return retwords


def getStemmedWords(listwords):
    """

    Args:
        listwords:

    Returns:

    """
    retwords = [snowstemmer.stem(i) for i in listwords]
    return retwords

import codecs

exclude = set(codecs.encode(string.punctuation, "utf-8"))

import operator
def combine_dicts(a, b):
    """

    Args:
        a:
        b:

    Returns:

    """
    z = a.copy()
    for k in a.keys():
        for kb in b[k]:
            z[k][kb] = b[k][kb]
    return z

def getExpandedWords(strWords):
    """

    Args:
        strWords:

    Returns:

    """
    if not isinstance(strWords, basestring):
        strWords = str(strWords)
    assert(len(strWords) > 0)
    s = ''.join(ch for ch in strWords if ch not in exclude)

    retWords = []
    words = nltk.word_tokenize(s)
    for i in words:
        loweri = i.strip().lower()
        if loweri in expandWords:
            retWords.append(expandWords[loweri]['expansion'])
        else:
            retWords.append(loweri)

    retWords = nltk.word_tokenize(" ".join(retWords))
    return retWords

def getScrubbedStringTokens(inputstring):
    """

    Args:
        inputstring:

    Returns:

    """
    if not inputstring:
        return ""
    strNoAbbrev = getExpandedWords(inputstring)
    lTokensNoStop = removeStopWords(strNoAbbrev)
    lStemmedTokens = getStemmedWords(lTokensNoStop)

    return lStemmedTokens

def tokenizeFile(inputFile, outputFile, dataKey=None, indexKey=None):
    """

    Args:
        inputFile:
        outputFile:
        dataKey:
        indexKey:

    Returns:

    """
    if indexKey is None:
        indexKey = 0
    if dataKey is None:
        dataKey = 0

    data = loadCSV(inputFile, indexKey)
    fields = data['fieldnames']
    dictData = data['dict']
    dictStrings = {}
    # if (isinstance(dictData, dict) and len(dictData) > 0):
    #     for k, v in dictData.items():
    #         dictStrings[k] = v[dataKey]
        # print k, v, "\n"
        # print v[dataKey], "\n", "\n"
#    listStrings = [k, v[dataKey] for k, v in dictData.items()]
    tokenkey = str(dataKey) + "tokenized"
    outData = tokenizeStrings(dictData, dataKey, tokenkey)
    fields.append(tokenkey)
    writedicttocsv(outputFile, outData, fields)

    return outData
