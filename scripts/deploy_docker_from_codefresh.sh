#!/usr/bin/env bash

if [ -z ${1} ]; then
	BRANCH=`git symbolic-ref --short HEAD`
else
	BRANCH="${1}"
fi

[ -z "$CODEFRESH_API_KEY" ] && echo "Missing required environment variable CODEFRESH_API_KEY." && exit 1;


# "***************************************************************"
# ""
# "Setting variable parameters to use during this script"
# ""
# "***************************************************************"

CONTAINER_NAME=jobs-$BRANCH
DOCKER_IMAGE=selner/js4-$BRANCH
DOCKER_REPO_IMAGE=r.cfcr.io/bryanselner/$DOCKER_IMAGE
echo Branch is $BRANCH.
echo Container name is $CONTAINER_NAME.
echo Docker image source is $DOCKER_REPO_IMAGE.


ECHO "***************************************************************"
ECHO ""
ECHO "Logging into Codefresh private docker repository..."
ECHO ""
ECHO "***************************************************************"
docker login r.cfcr.io -u bryanselner -p $CODEFRESH_API_KEY

ECHO "***************************************************************"
ECHO ""
ECHO "Removing any previous Docker container $CONTAINER_NAME"
ECHO ""
ECHO "***************************************************************"
docker rm -f $CONTAINER_NAME

ECHO "***************************************************************"
ECHO ""
ECHO "Pulling most recent Docker image from $DOCKER_REPO_IMAGE"
ECHO ""
ECHO "***************************************************************"
# docker rmi $imagetag
docker pull $DOCKER_REPO_IMAGE

ECHO "***************************************************************"
ECHO ""
ECHO "Starting docker container $CONTAINER_NAME from image $DOCKER_IMAGE"
ECHO ""
ECHO "***************************************************************"

VARLOCAL=/var/local
if [ "$(uname)" == "Darwin" ]; then
    # Do something under Mac OS X platform
    VARLOCAL=/private/var/local
# elif [ "$(expr substr $(uname -s) 1 5)" == "Linux" ]; then
    # Do something under GNU/Linux platform
# elif [ "$(expr substr $(uname -s) 1 10)" == "MINGW32_NT" ]; then
    # Do something under 32 bits Windows NT platform
# elif [ "$(expr substr $(uname -s) 1 10)" == "MINGW64_NT" ]; then
    # Do something under 64 bits Windows NT platform
fi

DOCKER_RUN="docker run --volume $VARLOCAL/jobs_scooper:/var/local/jobs_scooper --volume /var/run/docker.sock:/var/run/docker.sock  -e \"NLTK_DATA=/private/var/local/jobs_scooper/nltk_data\" -e \"JOBSCOOPER_OUTPUT=/private/var/local/jobs_scooper/output\"  --volume $JOBSCOOPER_PROPEL_INI:/private/var/local/jobs_scooper/configs/propel.ini --name $CONTAINER_NAME $DOCKER_REPO_IMAGE"
echo $DOCKER_RUN
$DOCKER_RUN

docker logs -f "$CONTAINER_NAME"


