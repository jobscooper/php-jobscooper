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
import uuid
reload(sys)
sys.setdefaultencoding('utf-8')

cli_usage = """
Usage:
  run_plugins.py --configdir <string> [--outdir <string> --user <string> --stages <string>]
  run_plugins.py --help

Options:
  -h --help  show this help message and exit
  --version  show version and exit
  -v --verbose  print status messages
  --user=<string> a specific user filename to run
  --stages=<string> the stage list to run (separated by commas e.g. "1,2,3") [default: "1"]
  --configdir=<string> directory location for user configuration files
  --outdir=<string> directory to use for output
"""

from docopt import docopt


import os
import sys
import subprocess
import datetime
import codecs



def getPluginFiles():
    """

    Returns:

    """
    plugins = {}
    plugindir = os.path.realpath("../../plugins")
    for root, dirs, files in os.walk(plugindir):
        for d in dirs:
            dirpath = os.path.join(root, d)
            for f in files:
                fname = os.path.basename(f)
                basef = fname.split(".")[0]

                filepath = os.path.join(root, d, f)
                plugins[basef] = filepath
    return plugins

def runPluginForUser(plug, configini, outpath, stages):
        """

        Args:
            plug:
            configini:
            outpath:
            stages:
        """
        RUNARGS = ["php", "/opt/jobs_scooper/runJobs.php", "-days 3", "--use_config_ini " + configini, "-o " + outpath, "-" + plugin]
        if stages:
            RUNARGS.append("--stages {}".format(stages))

        # RUNCMD = " ".join(RUNARGS)

        # cmd = RUNCMD.format(plug, configini, outpath)
        print("\trunning {} plugin".format(plugin))
        print ("\tcalling: php {}".format(" ".join(RUNARGS)))

        # file = "{}_runlog_{}.log".format(plug, datetime.datetime.now().strftime("%m-%d-%Y") + "_")
        # outfile = os.path.join(outpath, f)
        try:
            # f = codecs.open(outfile, encoding='utf-8', mode='w')
            p = subprocess.Popen(args=RUNARGS,  stdout=subprocess.PIPE,
                                 stderr=subprocess.STDOUT,
                                 stdin=subprocess.PIPE)

            resp = p.communicate()[0]
            # dresp = resp.split("\n")
            # print ("Last logged lines: " + "\n".join(dresp[0:5]))
            save_run_log(outpath, plugin, resp)
            # f.close()
        except:
            pass

        # print('Response: ', pp.pprint(dresp))
        # print('Return code:', p.returncode)


def save_run_log(outpath=None, plugin=None, textdata=None, encoding='utf-8'):
    """
        Writes a file to disk with the text passed.  If filepath is not specified, the filename will
        be <testname>_results.txt.
    :return: the path of the file
    """

    file = "{}_run.log".format(plugin)
    outfile = os.path.join(outpath, file)
    try:
        f = codecs.open(outfile, encoding=encoding, mode='w+')
        f.write(textdata)
        f.close()
    except:
        pass

    return outfile

if __name__ == '__main__':
    print " ".join(sys.argv)
    arguments = docopt(cli_usage, version='0.1.1rc')
    print ("Run Plugins called with arguments: " + str(arguments))
    
    import processfile

    userKey = arguments['--user']

    stages = arguments['--stages']
    if not stages:
        stages = "1"

    inidir = None
    outdir = None
    if arguments['--configdir']:
        inidir = arguments['--configdir'].replace("'", "")

    if arguments['--outdir']:
        outdir = arguments['--outdir'].replace("'", "")
    if not outdir:
        outdir = os.environ['JOBSCOOPER_OUTPUT']

    outdir = os.path.join(outdir, "plugin_run_logs", datetime.datetime.now().strftime("%m-%d-%Y"))
    if not os.path.isdir(outdir):
        os.makedirs(outdir)
    
    plugs = getPluginFiles()
    print ("Found {} plugins to run.".format(len(plugs)))
    print ("Parameters:  inidir={}; outdir={}; userKey={}, stages={}".format(inidir, outdir, userKey, stages))
    for fname in os.listdir(inidir):
        f = os.path.join(inidir, fname)
        if os.path.isfile(f) and f.endswith(".ini") and (userKey is None or userKey in f):
            nextcfg = f
            print("Running plugins for config file {}".format(nextcfg))
            for plugin in plugs:
                runPluginForUser(plugin, nextcfg, outdir, stages)


# return (stdout.decode("utf-8"), p.returncode)

    # print root, "consumes",
    # print sum(getsize(join(root, name)) for name in files),
    # print "bytes in", len(files), "non-directory files"
    # if 'CVS' in dirs:
    #     dirs.remove('CVS')  # don't visit CVS directories
