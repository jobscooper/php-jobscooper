#!/bin/bash
override=$1
skipHTMLDownload=$2

# get an output file name
now=$(date +"%Y_%m_%d_%H%M")
path="/Users/bryan/OneDrive/OneDrive-JobSearch/"
pathsrcfiles=$path"bryans_list_source_to_use/"
dest=$path
endfilename="_newjobs.csv"
endlogname="_newjobs_run.log"
enddestname="latest_newjobs_filtered.csv"
endtitlesname="bryans_list_exclude_titles.csv"
endregextitlesname="bryans_list_exclude_titles_regex.csv"
titlesfilename=$pathsrcfiles$endtitlesname
regextitlesfilename=$pathsrcfiles$endregextitlesname
filename=$now$endfilename
file=$path$filename
log=$path$now$endlogname

echo 'Output file will be ' $file 2>&1 1>"$log"
echo 'Final destination will be ' $dest 2>&1 1>"$log"
echo 'Log file is ' $log  2>&1 1>>"$log"

when="unknown"

# BUGBUG
if [ "$skipHTMLDownload" != "skip" ]; then     ## GOOD
	echo 'Downloading HTML from jobs sites'  2>&1 1>>"$log"
	if [ -f '$1/amazon-newjobs-page-1.html' ];
	then
		echo 'New jobs files were pulled within the last 24 hours. Skipping re-download.' 2>&1 1>>"$log"
	else

		# Run the applescript workflow to pull the latest jobs
		# from the jobs sites that we can't automate via PHP

		echo 'Starting download of HTML from jobs sites.' &>"$log"
		osascript downloadJobsSitesHTML.applescript "$path" &>"$log"

		# Wait for the workflow to finish
		sleep 2m &>"$log"
	fi

	echo 'New jobs site download complete.' $log  2>&1 1>>"$log"
fi

case "$(date +%a)" in (Mon|Wed|Fri|Sat)

  when="noteveryday"
  script_flags=" -all " 

esac

case "$(date +%a)" in  Tue|Thu|Sat|Sun)
  when="everyday"
  script_flags=" -indeed -simplyhired "
esac
echo 'Running script case $when using the following flags:  '$script_flags 2>&1 1>>"$log"

if [ "$override" == "all" ]; then     ## GOOD

  when="noteveryday"
  script_flags=" -all " 

fi

echo 'Downloading new jobs... ' 2>&1 1>>"$log"

# Now process that data and export CSVs with the listings
echo "Running php ../../scooper_utils/runJobs.php $script_flags -o '$file' -t '$titlesfilename' -tr '$regextitlesfilename'"  2>&1 1>>"$log"
php ../runJobs.php $script_flags -days 7 -o "$file" -t "$titlesfilename" -tr "$regextitlesfilename" 2>&1 1>>"$log"

# cp "$file" "$dest"   2>&1 1>>"$log"

echo 'Download complete. ' 2>&1 1>>"$log"

echo "Sending email with the latest results file =" $file 2>&1 1>>"$log"
echo "Running osascript send_latest_jobs_via_email.appleScript $file" 2>&1 1>>"$log"
osascript "send_latest_jobs_via_email.appleScript" "$file" 2>&1 1>>"$log"

echo "Done." 2>&1 1>>"$log"

