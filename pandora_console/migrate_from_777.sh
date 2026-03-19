#!/bin/bash

# Pandora OPEN
# Copyright (c) 2004-2026 Pandora FMS
# https://pandoraopen.io
# Pandora Console Updater (c) 2026 Pandora FMS
# Linux/FreeBSD/NetBSD Version (generic), for SuSe, Debian/Ubuntu,
# RHEL/CentOS, Fedora, FreeBSD and NetBSD only
# other Linux distros could not work properly without modifications
# This code is licensed under GPL 2.0 license.
# **********************************************************************


if [ -e ./include/config.php ]
then
    dbpass=`cat include/config.php | grep dbpass | cut -f 4 -d '"'`
    dbuser=`cat include/config.php | grep dbuser | cut -f 4 -d '"'`
    dbhost=`cat include/config.php | grep dbhost | cut -f 4 -d '"'`
    dbname=`cat include/config.php | grep dbname | cut -f 4 -d '"'`

    cat migrate_from_777.sql | mysql -u "$dbuser" -p"$dbpass" -D "$dbname" -h "$dbhost"

else
    echo "cannot find a valid config.php"
    exit 1
fi
