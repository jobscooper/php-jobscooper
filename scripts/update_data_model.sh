#!/usr/bin/env bash

args=$#           # Number of args passed.
ORIGDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

SCRIPTSDIR=`pwd`
cd ..
PROJDIR=`pwd`

lastarg=${!args}
if [ -z ${1} ]; then
    CONFIGDIR="$PROJDIR"
else
    CONFIGDIR=$1
fi
CODEDIR="$PROJDIR/src"
PROPEL="$PROJDIR/vendor/bin/propel"
OUTDIR=`echo $JOBSCOOPER_OUTPUT`
NOW=$(date "+%F-%H-%M-%S")
echo "current dir is `pwd`"

echo "Copying current db to backup ($OUTDIR/job_scooper_db.sq3.backup-$NOW)..."
cp "$OUTDIR/job_scooper_db.sq3" "$OUTDIR/job_scooper_db.sq3.backup-"$NOW
cd $CONFIGDIR
# rm -Rf ./generated-classes/Base
# rm -Rf ./generated-classes/Map
# cp -R ./generated-classes "./generated-classes.backup-"$NOW


echo "Removing past migration and SQL migration scripts"
rm -Rf "$PROJDIR/Config/Data/generated-migrations"
rm -Rf "$PROJDIR/Config/Data/generated-sql"

echo "Building Propel config file starting from $PROJDIR"
CMD="$PROPEL config:convert -vvv --config-dir=$CONFIGDIR"
echo $CMD; $CMD

echo "Building Propel model files..."
CMD="$PROPEL build -vvv --config-dir=$CONFIGDIR"
echo $CMD; $CMD

echo "Building Propel SQL files..."
CMD="$PROPEL sql:build --overwrite -vvv --config-dir=$CONFIGDIR"
echo $CMD; $CMD

echo "Generating SQL migration for database..."
CMD="$PROPEL migration:diff -vvv --config-dir=$CONFIGDIR"
echo $CMD; $CMD

echo "Running SQL migration against database..."
CMD="$PROPEL migration:migrate -vvv --config-dir=$CONFIGDIR"
echo $CMD; $CMD

cd $PROJDIR
composer dump

cd scripts
