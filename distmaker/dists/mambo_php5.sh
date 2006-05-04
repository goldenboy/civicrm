#!/bin/bash

# This script assumes
# that DAOs are generated
# and all the necessary conversions had place!

P=`dirname $0`
CFFILE=$P/../distmaker.conf

if [ ! -f $CFFILE ] ; then
	echo "NO DISTMAKER.CONF FILE!"
	exit 1
else
	for l in `cat $CFFILE`; do export $l; done
fi

RSYNCOPTIONS="-avC --exclude=svn"
RSYNCCOMMAND="rsync $RSYNCOPTIONS"
SRC=$DM_SOURCEDIR
TRG=$DM_TMPDIR/civicrm

# make sure and clean up before
if [ -d $TRG ] ; then
	rm -rf $TRG/*
fi

# copy all the rest of the stuff
for CODE in css i js l10n packages PEAR templates bin mambo CRM api modules; do
  echo $CODE
  [ -d $SRC/$CODE ] && $RSYNCCOMMAND $SRC/$CODE $TRG
done

# delete any setup.sh or setup.php4.sh if present
if [ -d $TRG/bin ] ; then
  rm -f $TRG/bin/setup.sh
  rm -f $TRG/bin/setup.php4.sh
  rm -f $TRG/bin/setup.bat
fi

# copy selected sqls
if [ ! -d $TRG/sql ] ; then
	mkdir $TRG/sql
fi
for F in $SRC/sql/civicrm_*.mysql; do
	cp $F $TRG/sql
done

# delete any setup.sh or setup.php4.sh if present
if [ -d $TRG/bin ] ; then
  rm -f $TRG/bin/setup.sh
  rm -f $TRG/bin/setup.php4.sh
fi

# copy docs
cp $SRC/license.txt $TRG
cp $SRC/affero_gpl.txt $TRG
cp $SRC/gpl.txt $TRG
cp $SRC/README.txt $TRG
cp $SRC/civicrm.config.php $TRG
cp $SRC/civicrm.settings.php.sample $TRG

# final touch
REV=`svnversion -n $SRC`
echo "1.4.$REV Mambo PHP5" > $TRG/civicrm-version.txt


# gen zip file
cd $DM_TMPDIR;

mkdir com_civicrm
mkdir com_civicrm/civicrm

cp -r -p civicrm/* com_civicrm/civicrm

$DM_PHP5PATH/php $DM_SOURCEDIR/distmaker/utils/mamboxml.php

cp -r com_civicrm/civicrm/mambo/* com_civicrm

zip -r -9 $DM_TARGETDIR/civicrm-mambo-php5-SNAPSHOT-rev$REV.zip com_civicrm

# clean up
rm -rf com_civicrm
rm -rf $TRG
