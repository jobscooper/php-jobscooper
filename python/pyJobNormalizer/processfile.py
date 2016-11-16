import nltk
from nltk.corpus import stopwords
import unicodecsv
import re
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
        csv_writer.writerow(data[row])
    csvfile.close()
    return csvFileName

def loadCSV(csvFileName, rowKeyName = None):

    print "Loading " + csvFileName
    csv_fp = open(csvFileName, "rbU")
    dictRecords = {}
    fields = {}

    csv_reader = None
    try:
        with csv_fp:
            csv_reader = unicodecsv.DictReader(csv_fp, delimiter=",", quoting=unicodecsv.QUOTE_MINIMAL, errors='strict')
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

def tokenizeStrings(listStrings):
    retData = {}
    for k in listStrings.keys():
        v = listStrings[k]
        retData[k] = { "original" : v, "tokenized" : []}
        tokens = getScrubbedStringTokens(v)
        retData[k]["tokenized"] = "|".join(tokens)

    return retData

import nltk
import string
# NOTE:  Need to run the download once per machine to get the dictionaries
# nltk.download()

def removeStopWords(listwords):
    retwords = [i for i in listwords if i not in stopwrds]
    return retwords


def getStemmedWords(listwords):
    retwords = [snowstemmer.stem(i) for i in listwords]
    return retwords

import codecs

exclude = set(codecs.encode(string.punctuation, "utf-8"))

import operator
def combine_dicts(a, b):
    z = a.copy()
    for k in a.keys():
        for kb in b[k]:
            z[k][kb] = b[k][kb]
    return z

def getExpandedWords(strWords):
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
    strNoAbbrev = getExpandedWords(inputstring)
    lTokensNoStop = removeStopWords(strNoAbbrev)
    lStemmedTokens = getStemmedWords(lTokensNoStop)

    return lStemmedTokens


def tokenizeFile(inputFile, outputFile, dataKey=None, indexKey=None):
    if indexKey is None:
        indexKey = 0
    if dataKey is None:
        dataKey = 0

    data = loadCSV(inputFile, indexKey)
    fields = data['fieldnames']
    dictData = data['dict']
    dictStrings = {}
    for k, v in dictData.items():
        dictStrings[k] = v[dataKey]
        # print k, v, "\n"
        # print v[dataKey], "\n", "\n"
#    listStrings = [k, v[dataKey] for k, v in dictData.items()]

    outData = tokenizeStrings(dictStrings)
    results = combine_dicts(dictData, outData)
    fields.append(u"tokenized")
    writedicttocsv(outputFile, results, fields)

    return results

#
# def addMatchesToList(source, new_links, itemlist, out_folder, kind):
#     """
#
#     :rtype : object
#     """
#     if new_links is None:
#         new_links = []
#
#     for link in new_links:
#         item = dict(link.attrs.copy())
#         item['text'] = link.text.encode('ascii', 'ignore').lower()
#         item['words'] = removeStopWords(item['text'])
#         l = []
#         for w in item['words']:
#             l.append(w.encode('ascii', 'ignore'))
#         item['words'] = l
#         item['words_stemmed'] = getStemmedWords(item['words'])
#         item['source'] = source.lower()
#
#         itemlist.append(item)
#
#     writelisttocsv(os.path.join(out_folder, (source + "-" + kind +"titles.tsv")), itemlist)
#
#     countWords(itemlist, "words", out_folder, source, kind)
#     countWords(itemlist, "words_stemmed", out_folder, source, kind)
#
#     return itemlist
#