#!/bin/sh

# This is distmaker script for CiviCRM
# Author: michau
# "Protected by an electric fence and copyright control."
# Thanks to Kleptones for moral support when writing this.

# Make sure that you have distmaker.conf file
# in the same directory containing following lines:
#
# DM_SOURCEDIR=/home/user/svn/civicrm           <- sources
# DM_GENFILESDIR=/home/user/generated           <- generated files
# DM_TMPDIR=/tmp                                <- temporary files (will be deleted afterwards)
# DM_TARGETDIR=/tmp/outdir                      <- target dir for tarballs
# DM_PHP=/opt/php5/bin/php                      <- php5 binary
# DM_RSYNC=/usr/bin/rsync                       <- rsync binary
# DM_VERSION=trunk.r1234                        <- what the version number should be
# DM_ZIP=/usr/bin/zip                           <- zip binary
# 
#
# ========================================================
# DO NOT MODIFY BELOW
# ========================================================


# Where are we called from?
P=`dirname $0`
# Current dir
ORIGPWD=`pwd`

# Set no actions by default 
D5PACK=0
J5PACK=0
S5PACK=0

# Display usage
display_usage()
{
	echo
	echo "Usage: "
	echo "  distmaker.sh OPTION"
	echo 
	echo "Options available:"
	echo "  all - generate all available tarballs"
	echo "  d5  - generate Drupal PHP5 module"
	echo "  j5  - generate Joomla PHP5 module"
	echo "  s5  - generate Standalone PHP5 tarball"
	echo
	echo "You also need to have distmaker.conf file in place."
	echo "See distmaker.conf.dist for example contents."
	echo
}


# Check if config is ok.
check_conf()
{
	# Test for distmaker.conf file availability, cannot proceed without it anyway
	if [ ! -f $P/distmaker.conf ] ; then
		echo; echo "ERROR! No distmaker.conf file available!"; echo;
		display_usage
		exit 1
	else
		for l in `cat $P/distmaker.conf`; do export $l; done
		for k in "$DM_SOURCEDIR" "$DM_GENFILESDIR" "$DM_TARGETDIR" "$DM_TMPDIR"; do
			if [ ! -d "$k" ] ; then
				echo; echo "ERROR! " $k "directory not found!"; echo "(if you get empty directory name, it might mean that one of necessary variables is not set)"; echo;
				exit 1
			fi
		done
	fi
}

# Check if PHP4 converstion happened
check_php4()
{
	if [ ! $PHP4GENERATED = 1 ]; then
		echo; echo "ERROR! Cannot package PHP4 version without running conversion!"; echo;
		exit 1
	fi
}

# Let's go.

check_conf

# Figure out what to do
case $1 in
	# DRUPAL PHP5
	d5)
	echo; echo "Generating Drupal PHP5 module"; echo;
	D5PACK=1
	;;

	# JOOMLA PHP5
	j5)
	echo; echo "Generating Joomla PHP5 module"; echo;
	J5PACK=1
	;;

	# STANDALONE PHP5
	s5)
	echo; echo "Generating Standalone PHP5 tarball"; echo;
	S5PACK=1
	;;

	# ALL
	all)
	echo; echo "Generating all we've got."; echo;
	D5PACK=1
	J5PACK=1
	S5PACK=1
	;;

	# USAGE
	*)
	display_usage
	exit 0	
	;;

esac


# Before anything - regenerate DAOs

cd $DM_SOURCEDIR/xml
$DM_PHP GenCode.php schema/Schema.xml $DM_VERSION

cd $ORIGPWD

if [ $D5PACK = 1 ]; then
	echo; echo "Packaging for Drupal, PHP5 version"; echo;
	sh $P/dists/drupal_php5.sh
fi

if [ $J5PACK = 1 ]; then
	echo; echo "Packaging for Joomla, PHP5 version"; echo;
	sh $P/dists/joomla_php5.sh
fi

if [ $S5PACK = 1 ]; then
	echo; echo "Packaging for Standalone, PHP5 version"; echo;
	sh $P/dists/standalone_php5.sh
fi


unset DM_SOURCEDIR DM_GENFILESDIR DM_TARGETDIR DM_TMPDIR DM_PHP DM_RSYNC DM_VERSION DM_ZIP
echo;echo "DISTMAKER Done.";echo;
