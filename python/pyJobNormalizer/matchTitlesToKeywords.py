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


import sys

reload(sys)
sys.setdefaultencoding('utf-8')

cli_usage = """
Usage:
  matchTitlesToKeywords.py -i <file> -o <file>
  matchTitlesToKeywords.py --version

Options:
  -h --help  show this help message and exit
  --version  show version and exit
  -v --verbose  print status messages
  -o <file>, --output <file> output file with job match results 
  -i <file>, --input <file> input JSON data file with jobs and keywords
"""
JSON_KEY_JOBMATCHES = u'job_matches'
JSON_KEY_POS_KEYWORDS = u'SearchKeywords'
JSON_KEY_NEG_KEYWORDS = u'negative_title_keywords'
JSON_KEY_USER = u'user'

from docopt import docopt
import json
from processfile import tokenizeStrings

import itertools
from operator import itemgetter

class SetEncoder(json.JSONEncoder):
    def default(self, obj):
        if isinstance(obj, set):
            return list(obj)
        return json.JSONEncoder.default(self, obj)


class MatchJobsToKeywordsTask():
    inputfile = None
    keywords = {}
    negative_keywords = {}
    jobs = {}
    user_id = None
    df_job_tokens = None

    def __init__(self, inputfile, outputfile):

        self.inputFile = inputfile
        self.outputfile = outputfile

        print "Processing job title matching with input file {}".format(self.inputFile)

        print "Loading user keywords for matching..."
        self.load_keywords()

        print "Loading job list to match..."
        self.loadJobs()

        print "Matching job list titles vs. user search keywords ..."
        self.mark_positive_matches()

        print "Matching job list titles vs. user negative keyword matches..."
        self.mark_negative_matches()

        self.exportResultsData()
        print "Matching completed."

    def get_output_data(self):
        return {
            JSON_KEY_JOBMATCHES: self.jobs,
            JSON_KEY_POS_KEYWORDS: self.keywords,
            JSON_KEY_NEG_KEYWORDS: self.negative_keywords
        }

    def exportResultsData(self):
        print "Exporting final match results to {}".format(self.outputfile)

        outf = open(self.outputfile, "w")
        outData = self.get_output_data()
        json.dump(outData, outf, indent=4, encoding='utf-8', cls=SetEncoder)
        outf.close()
        return self.outputfile

    def loadJobs(self):
        inf = open(self.inputFile, "rU")
        inputData = json.load(inf)
        if inputData:
            if isinstance(inputData, dict):
                if (JSON_KEY_JOBMATCHES in inputData and isinstance(inputData[JSON_KEY_JOBMATCHES], dict) and len(
                        inputData[JSON_KEY_JOBMATCHES]) > 0):
                    self.jobs = inputData[JSON_KEY_JOBMATCHES]
                    self.jobs = tokenizeStrings(self.jobs, u'Title', u'TitleTokens', u'set')

    def match_keywords(self, data_keywords):
        dictKeywords = {}
        for kwdset in data_keywords:
            toksstring = data_keywords[kwdset]['tokens']
            toks = toksstring.split("|")
            l = [t for t in toks if t != ""]
            dictKeywords[toksstring] = l

        matches = []
        for jobid in self.jobs:
            for toks in dictKeywords:
                if self.jobs[jobid][u'TitleTokens'].issuperset(set(dictKeywords[toks])):
                     matches.append({u'token_match' : toks, u'jobid' : jobid })

        matched_groups = {}
        matched_jobs = sorted(matches, key=itemgetter(u'jobid'))
        for k, g in itertools.groupby(matched_jobs, key=itemgetter(u'jobid')):
            matched_groups[k] = list(g)

        return matched_groups

    def mark_positive_matches(self):
        print "Marking jobs that match {} positive title keywords...".format(len(self.keywords))

        matched_groups = self.match_keywords(self.keywords)
        group_keys = matched_groups.keys()
        #
        # Since we re-matched all the records, we need to update
        # all rows to clear out any old values of match/not match
        # that may be lingering
        #
        all_job_ids = self.jobs.keys()

        #
        #
        #  First let's mark all the non-matches
        #
        not_matched_ids = list(set(all_job_ids) - set(group_keys))
        for jobid in not_matched_ids:
            self.jobs[jobid][u'IsJobMatch'] = False
            self.jobs[jobid][u'MatchedUserKeywords'] = None

        #
        #  Now mark all the job title matches
        #
        for jobid in group_keys:
            self.jobs[jobid][u'IsJobMatch'] = True
            terms = None
            for grp in matched_groups[jobid]:
                if terms:
                    terms += "|"
                if isinstance(grp[u'token_match'], basestring):
                    terms = grp[u'token_match']
                else:
                    terms = " ".glue(list(grp[u'token_match']))
            self.jobs[jobid][u'MatchedUserKeywords'] = terms

        print "Positive Search Keywords: {} / {} job titles matched; {} / {} job titles not matched.".format(len(group_keys), len(all_job_ids), len(not_matched_ids), len(all_job_ids))

    def mark_negative_matches(self):

        print "Marking jobs that match {} negative title keywords...".format(len(self.negative_keywords))
        matched_groups = self.match_keywords(self.negative_keywords)
        group_keys = matched_groups.keys()
        #
        # Since we re-matched all the records, we need to update
        # all rows to clear out any old values of match/not match
        # that may be lingering
        #
        all_job_ids = self.jobs.keys()

        #
        #
        #  First let's mark all the non-matches
        #
        not_matched_ids = list(set(all_job_ids) - set(group_keys))
        for jobid in not_matched_ids:
            self.jobs[jobid][u'MatchedNegativeTitleKeywords'] = None

        #
        #  Now mark all the job title matches
        #
        for jobid in group_keys:
            terms = None
            for grp in matched_groups[jobid]:
                if terms:
                    terms += "|"
                if isinstance(grp[u'token_match'], basestring):
                    terms = grp[u'token_match']
                else:
                    terms = " ".glue(list(grp[u'token_match']))
            self.jobs[jobid][u'MatchedNegativeTitleKeywords'] = terms

        print "Negative Title Keywords:  {} / {} job titles matched; {} / {} job titles not matched.".format(len(group_keys), len(all_job_ids), len(not_matched_ids), len(all_job_ids))

    def load_keywords(self):
        fp = open(self.inputFile, "rU")
        data = json.load(fp, encoding="utf-8")

        if JSON_KEY_USER in data and 'UserId' in data[JSON_KEY_USER]:
            self.user_id = data[JSON_KEY_USER]['UserId']

            if JSON_KEY_POS_KEYWORDS in data[JSON_KEY_USER]:
                for keyword in data[JSON_KEY_USER][JSON_KEY_POS_KEYWORDS]:
                    self.keywords[keyword] = {
                        'key': keyword,
                        'user_keyword_set_key': keyword,
                        'keyword': keyword,
                        'tokens': None
                    }
                outData = tokenizeStrings(self.keywords, 'keyword', 'tokens')

                self.keywords = outData

        if JSON_KEY_NEG_KEYWORDS in data:
            for kwdkey in data[JSON_KEY_NEG_KEYWORDS]:
                self.negative_keywords[kwdkey] = {
                    'key': kwdkey,
                    'keyword': kwdkey,
                    'tokens': None
                }
            outData = tokenizeStrings(self.negative_keywords, 'keyword', 'tokens')
            self.negative_keywords = outData

        fp.close()
        print "Loaded {} positive keywords and {} negative keywords for matching.".format(len(self.keywords), len(self.negative_keywords))


if __name__ == '__main__':
    arguments = docopt(cli_usage, version='0.1.1rc')

    matcher = MatchJobsToKeywordsTask(arguments["--input"].replace("'", ""), arguments["--output"].replace("'", ""))
