#!/usr/bin/env bash

echo "*****************************************************************

    Database Update Started

*****************************************************************"

args=$#           # Number of args passed.
ORIGDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
SCRIPTSDIR=`pwd`
cd ..
PROJDIR=`pwd`

lastarg=${!args}
if [ -z ${1} ]; then
    CONFIGDIR="$PROJDIR/Config"
else
    CONFIGDIR=$1
fi

CODEDIR="$PROJDIR/src"
PROPEL="$PROJDIR/vendor/bin/propel"
OUTDIR=`echo $JOBSCOOPER_OUTPUT`
NOW=$(date "+%F-%H-%M-%S")
echo "current dir is `pwd`"

echo "Moving current db to backup ($OUTDIR/job_scooper_db.sq3)..."
mv "$OUTDIR/job_scooper_db.sq3" "$OUTDIR/job_scooper_db.sq3.backup-"$NOW

rm -Rf $CODEDIR/src/DataAccess/Base
rm -Rf $CODEDIR/src/DataAccess/Map
cp -R $CODEDIR/src/DataAccess "$CODEDIR/generated-classes.backup-"$NOW

cd $PROJDIR


echo "*****************************************************************

    Generating Propel runtime configuration from $CONFIGDIR

*****************************************************************"

echo "Building Propel config file starting from $PROJDIR"
CMD="$PROPEL config:convert -vvv --config-dir=$CONFIGDIR"
echo $CMD; $CMD

echo "Building Propel model files..."
CMD="$PROPEL build -vvv --config-dir=$CONFIGDIR"
echo $CMD; $CMD

echo "Building Propel SQL files..."
CMD="$PROPEL sql:build --overwrite -vvv --config-dir=$CONFIGDIR"
echo $CMD; $CMD

echo "Inserting SQL tables into database..."
CMD="$PROPEL sql:insert -vvv --config-dir=$CONFIGDIR"
echo $CMD; $CMD

#cp -f "$OUTDIR/job_scooper_db.sq3" "$CURDIR/examples/job_scooper_db.sq3"
#
cd $PROJDIR
echo "current dir is " `pwd`

composer dump

cd $SCRIPTSDIR


