#!/usr/bin/bash 

cd jobs/amazon_jobs

# delete any jobs lists HTML files older than a day
# so we know to go get them again 
find amazon-newjobs-page-*.html -mtime +1 -exec rm {} \;


if [ -f 'amazon-newjobs-page-1.html' ];
then
	echo 'New jobs files were pulled within the last 24 hours. Skipping re-download.'
else

	# Run the Fake app workflow to pull the latest jobs
	# from the new Amazon website
	osascript ../../downloadJobsFromAmazonNewSite.applescript "/Users/bryan/Code/data/jobs/amazon_jobs"

	# Wait for the workflow to finish
	sleep 1m

fi

cd .. #back to jobs folder

