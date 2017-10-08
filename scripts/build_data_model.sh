#!/usr/bin/env bash
cd ../
PROPEL=`pwd`"/vendor/bin/propel"
CODEDIR=`pwd`"/src"
CONFIGDIR=`pwd`"/config"
OUTDIR=`echo ${JOBSCOOPER_OUTPUT}`
NOW=$(date "+%F-%H-%M-%S")

echo "Copying current db to backup ($OUTDIR/job_scooper_db.sq3.backup-$NOW)..."
cp "$OUTDIR/job_scooper_db.sq3" "$OUTDIR/job_scooper_db.sq3.backup-"$NOW
cd $CONFIGDIR
rm -Rf ./generated-classes/lib
rm -Rf ./generated-classes/Map
cp -R ./generated-classes "./generated-classes.backup-"$NOW




$PROPEL config:convert -vvv
$PROPEL build -vvv
$PROPEL sql:build --overwrite -vvv
$PROPEL migration:diff -vvv
$PROPEL migration:migrate -vvv

mv -f "$OUTDIR/job_scooper_db.sq3" "$OUTDIR/job_scooper_db-migrated.sq3"

$PROPEL sql:build --overwrite -vvv
$PROPEL sql:insert -vvv
cp -f "$OUTDIR/job_scooper_db.sq3" "$CODEDIR/examples/job_scooper_db.sq3"
mv -f "$OUTDIR/job_scooper_db-migrated.sq3" "$OUTDIR/job_scooper_db.sq3"

cd ..
composer dump
cd scripts