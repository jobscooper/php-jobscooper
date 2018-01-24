@ECHO OFF
IF %~1.==. SET branch=master
IF NOT %~1.==. SET branch=%1

SET jobsname=jobs-%branch%
SET imagetag=selner/js4-%branch%
echo Branch is %branch%.
echo Container name is %jobsname%.
echo Image tag is %imagetag%.
@ECHO ON


docker rm -f %jobsname%
docker rmi %imagetag%

docker build --build-arg BRANCH=%branch% --build-arg CACHEBUST="%DATE%%TIME%" -t %imagetag% .

docker run --volume C:\Users\bryan\Dropbox\var-local-jobs_scooper:/var/local/jobs_scooper --volume c:\dev\nltk_data:/root/nltk_data --hostname %COMPUTERNAME%_docker --name %jobsname% -d %imagetag%

docker logs -f %jobsname%

