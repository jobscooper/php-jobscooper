#!/usr/bin/env bash
cd /opt/jobs_scooper

php run_job_scooperrun_job_scooper.php /var/local/jobs_scooper/configs/evan/job_scooper_config.ini --jobsite all

php run_job_scooper.php /var/local/jobs_scooper/configs/bryan/job_scooper_config.ini --jobsite indeed --debug


