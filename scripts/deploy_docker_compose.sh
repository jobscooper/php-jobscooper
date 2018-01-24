#!/bin/bash

echo off

DOCKER_RUN_CMD=""
if [ -z ${1} ]; then
	echo "no command set."
else
	DOCKER_RUN_CMD="env bash -C '${1}'"
	echo "Docker run command:  "$DOCKER_RUN_CMD
	export DOCKER_RUN_CMD=$DOCKER_RUN_CMD
fi

if [ -z $CODEFRESH_API_KEY ]; then
	echo "Missing required environment variable CODEFRESH_API_KEY."
	goto s_error
fi

cd ..

echo "Docker .env variables are set to: "
cat .env
BRANCH=`git symbolic-ref --short HEAD`
echo "BRANCH = $BRANCH"
echo "CONTAINER_TAG = $CONTAINER_TAG"


echo "***************************************************************"
echo ""
echo "Logging into Codefresh private docker repository..."
echo ""
echo "***************************************************************"
docker login r.cfcr.io -u bryanselner -p $CODEFRESH_API_KEY

echo "***************************************************************"
echo ""
echo "Removing any previous Docker containers"
echo ""
echo "***************************************************************"
docker-compose down --remove-orphans

docker-compose pull

docker-compose config

docker-compose up -d

docker ps -a


