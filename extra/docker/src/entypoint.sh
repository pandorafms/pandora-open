#!/bin/bash

#  Prepares environment and launchs Pandora Open

# Global variables
PANDORA_CONSOLE=/var/www/html/pandora_console
PANDORA_SERVER_CONF=/etc/pandora/pandora_server.conf
PANDORA_SERVER_BIN=/usr/bin/pandora_server
PANDORA_HA_BIN=/usr/bin/pandora_ha
PANDORA_TABLES_MIN=160
PANDORA_BIN="pandora_server"
PANDORA_EXEC="/usr/bin/pandora_server /etc/pandora/pandora_server.conf"

# Default variables
[ "$TZ" ] || TZ="Europe/Madrid"
[ "$DBHOST" ] || DBHOST=""
[ "$DBNAME" ] || DBNAME=pandora
[ "$DBUSER" ] || DBUSER=pandora
[ "$DBPASS" ] || DBPASS='Pandor4_'
[ "$DBPORT" ] || DBPORT=3306
[ "$SLEEP" ] || SLEEP=5
[ "$RETRIES" ] || RETRIES=1
[ "$PUBLICURL" ] || PUBLICURL=""
[ "$INSTANCE_NAME" ] || INSTANCE_NAME="pandoraopen"

# Functions
# Check database
function db_check {
	# Check DB
	echo -n ">> Checking dbengine connection: "

	for i in `seq $RETRIES`; do 
		r=`echo 'select 1' | mysql -u$DBUSER   -P$DBPORT -h$DBHOST -A`
		if [ $? -ne 0 ]; then
			echo -n "retriying DB conection in $SLEEP seconds: " 
			sleep $SLEEP
		else
			break
		fi
	done

	r=`echo 'select 1' | mysql -u$DBUSER -P$DBPORT -h$DBHOST -A`
	if [ $? -eq 0 ]; then
		echo "OK"
		echo -n ">> Checking database connection: "
		r=`echo 'select 1' | mysql -u$DBUSER   -P$DBPORT -h$DBHOST -A $DBNAME`
		if [ $? -eq 0 ]; then
			echo "OK"
			return 0
		fi
		echo -n ">> Cannot connect to $DBNAME, trying to create: "
		r=`echo "create database $DBNAME" | mysql -u$DBUSER   -P$DBPORT -h$DBHOST`
		if [ $? -eq 0 ]; then
			echo "OK"
			return 0
		fi
		echo "Cannot create database $DBNAME on $DBUSER@$DBHOST:$DBPORT"

		return 1
	fi

	if [ "$DEBUG" == "1" ]; then
		echo "Command: [echo 'select 1' | mysql -u$DBUSER   -P$DBPORT -h$DBHOST -A $DBNAME]"
		echo "Output: [$r]"

		traceroute $DBHOST
		nmap $DBHOST -v -v -p $DBPORT
	fi


	return 1
}

# Load database
function db_load {
	# Load DB
	echo -n ">> Checking database state:"
	r=`mysql -u$DBUSER   -P$DBPORT -h$DBHOST -A $DBNAME -s -e 'show tables'| wc -l`
	if [ "$DEBUG" == "1" ]; then
		echo "Command: [mysql -u$DBUSER   -P$DBPORT -h$DBHOST -A $DBNAME -s -e 'show tables'| wc -l]"
		echo "Output: [$r]"
	fi

	if [ "$r" -ge "$PANDORA_TABLES_MIN" ]; then
		echo 'OK. Already exists, '$r' tables detected'
		return 0
	fi
	echo 'Empty database detected';

	# Needs to be loaded.
	echo -n "- Loading database schema: "
	r=`mysql -u$DBUSER   -P$DBPORT -h$DBHOST $DBNAME < $PANDORA_CONSOLE/pandoradb.sql`
	if [ $? -ne 0 ]; then
		echo "mysql -u$DBUSER   -P$DBPORT -h$DBHOST $DBNAME < $PANDORA_CONSOLE/pandoradb.sql"
		echo "ERROR"
		echo "$r"
		return 1;
	fi
	echo "OK"

	echo -n "- Loading database data: "
	r=`mysql -u$DBUSER   -P$DBPORT -h$DBHOST $DBNAME < $PANDORA_CONSOLE/pandoradb_data.sql`
	if [ $? -ne 0 ]; then
		echo "ERROR"
		echo $r
		return 2;
	fi
	echo "OK"

	# Loaded.
	return 0
}


# Prepare & start Pandora FMS Console
function console_prepare {
	CONSOLE_PATH=$PANDORA_CONSOLE
	echo ">> Preparing console"
	# Delete install and license files.
	mv $CONSOLE_PATH/install.php $CONSOLE_PATH/install.done
	rm -f $CONSOLE_PATH/enterprise/PandoraFMS_Enteprise_Licence.txt
	
	# Configure console.
	cat > $CONSOLE_PATH/include/config.php << EO_CONFIG_F
<?php
\$config["dbtype"] = "mysql";
\$config["dbname"]="$DBNAME";
\$config["dbuser"]="$DBUSER";
\$config["dbpass"]="$DBPASS";
\$config["dbhost"]="$DBHOST";
\$config["homedir"]="/var/www/html/pandora_console";
\$config["homeurl"]="/pandora_console";	
error_reporting(0); 
\$ownDir = dirname(__FILE__) . '/';
include (\$ownDir . "config_process.php");

EO_CONFIG_F

	# enable allow override 
	cat > /etc/httpd/conf.d/pandora.conf << EO_CONFIG_F
<Directory "/var/www/html">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>

EO_CONFIG_F

	cat > /var/www/html/index.html << EOF_INDEX
<meta HTTP-EQUIV="REFRESH" content="0; url=/pandora_console/">
EOF_INDEX
	
	echo "- Fixing permissions"
	chmod 600 $CONSOLE_PATH/include/config.php	
	chown -R apache. $CONSOLE_PATH
	mkdir -p /run/php-fpm/

	# prepare php.ini
	sed -i -e "s/^max_input_time.*/max_input_time = -1/g" /etc/php.ini
	sed -i -e "s/^max_execution_time.*/max_execution_time = 0/g" /etc/php.ini
	sed -i -e "s/^upload_max_filesize.*/upload_max_filesize = 800M/g" /etc/php.ini
	sed -i -e "s/^memory_limit.*/memory_limit = 800M/g" /etc/php.ini
	sed -i -e "s/.*post_max_size =.*/post_max_size = 800M/" /etc/php.ini

 	echo "- Setting Public URL: $PUBLICURL"
 	q=$(mysql -u$DBUSER   $DBNAME -h$DBHOST -sNe "select token from tconfig;" | grep public_url)
	[[ ! "$q" ]] && mysql -u $DBUSER   $DBNAME -P$DBPORT -h$DBHOST -sNe  "INSERT INTO tconfig (token,value) VALUES ('public_url',\"$PUBLICURL\");"
	mysql -u$DBUSER   $DBNAME -h$DBHOST -sNe "UPDATE tconfig SET value=\"$PUBLICURL\" WHERE token=\"public_url\";"

}

function check_mr {
		## geting MR + Package
		CMR=$(mysql -u$DBUSER   $DBNAME -h$DBHOST -sNe "select value from tconfig where token = 'MR'")
		CPK=$(mysql -u$DBUSER   $DBNAME -h$DBHOST -sNe "select value from tconfig where token = 'current_package_enterprise'")

		DPK=$(grep pandora_version $PANDORA_CONSOLE/include/config_process.php | awk '{print $3}' | tr -d "'" | tr -d ";" | cut -d . -f 3)
		DMR=$(ls $PANDORA_CONSOLE/extras/mr/ | sort -n | tail -1 | cut -d . -f 1)
		
		if [[ $DMR -gt $CMR ]]; then
			echo ">> Fixing DB: MR: $CMR Current Package: $CPK Desired MR: $DMR"
			cd  $PANDORA_CONSOLE/extras/mr/
            for i in $(ls | sort -n); do
                    cat $i | mysql -u$DBUSER   $DBNAME -h$DBHOST
                done
        	cd -
			
			echo ">> Updating DB: MR: $CMR Current Package: $CPK Desired MR: $DMR"

            mysql -u $DBUSER   $DBNAME -h$DBHOST -sNe "update tconfig set value = $DMR where token = 'MR';"
            mysql -u $DBUSER   $DBNAME -h$DBHOST -sNe "update tconfig set value = $DPK where token = 'current_package_enterprise';"
        fi
}

function server_prepare {
	sed -i -e "s/^dbhost.*/dbhost $DBHOST/g" $PANDORA_SERVER_CONF
	sed -i -e "s/^dbname.*/dbname $DBNAME/g" $PANDORA_SERVER_CONF
	sed -i -e "s/^dbuser.*/dbuser $DBUSER/g" $PANDORA_SERVER_CONF
	sed -i -e "s|^dbpass.*|dbpass $DBPASS|g" $PANDORA_SERVER_CONF
	sed -i -e "s/^dbport.*/dbport $DBPORT/g" $PANDORA_SERVER_CONF
	sed -i -e "s/^#servername.*/servername $INSTANCE_NAME/g" $PANDORA_SERVER_CONF

	# prepare snmptrapd
	cat > /etc/snmp/snmptrapd.conf << EOF
authCommunity log public
disableAuthorization yes
EOF

}

## MAIN

# Variable check
if [ "$DBUSER" == "" ] || [ "$DBPASS" == "" ] || [ "$DBNAME" == "" ] || [ "$DBHOST" == "" ]; then
	echo "Required environemntal variables DBUSER, DBPASS, DBNAME, DBHOST"
	exit 1
fi

export MYSQL_PWD=$DBPASS

echo "" > /opt/pandora/crontasks || touch /opt/pandora/crontasks

#set localtime
rm -rf /etc/localtime
ln -s /usr/share/zoneinfo/$TZ /etc/localtime

# preparing DB
db_check && db_load
/usr/bin/php $PANDORA_CONSOLE/godmode/um_client/updateMR.php  
check_mr
# preparing console
console_prepare 
# preparing server
server_prepare

echo  ">> Enable discovery cron: "
echo "*/5 * * * * wget -q -O - --no-check-certificate --load-cookies /tmp/cron-session-cookies --save-cookies /tmp/cron-session-cookies --keep-session-cookies http://localhost/pandora_console/enterprise/cron.php >> /var/www/html/pandora_console/log/cron.log" >> /opt/pandora/crontasks

echo  ">> Enable pandora_db cron: "
/usr/bin/perl /usr/share/pandora_server/util/pandora_db.pl /etc/pandora/pandora_server.conf
echo "0 */1 * * * /usr/bin/pandora_db /etc/pandora/pandora_server.conf" >> /opt/pandora/crontasks

echo ">> Loading crontab tasks"
crontab -r
crontab /opt/pandora/crontasks && crontab -l

ip addr | grep -w "inet" | grep -v "127.0.0.1" | grep -v -e "172.1[0-9].0.1" | awk '{print $2}' | awk -F '/' '{print "-> Go to http://"$1"/pandora_console to manage this server"}'

# Check and launch supervisord
echo  ">> Starting process:  Running supervisord"
# Create supervisor.conf

cat > /etc/supervisord.conf << EOF_CON
[unix_http_server]
file=/tmp/supervisor.sock

[supervisord]
nodaemon=true
loglevel = debug

[program:php-fpm]
command=/usr/sbin/php-fpm -F
riredirect_stderr=true

[program:httpd]
command=/usr/sbin/httpd -DFOREGROUND
riredirect_stderr=true

[program:tentacle]
command=/etc/init.d/tentacle_serverd restart

[program:$PANDORA_BIN]
command=$PANDORA_EXEC
riredirect_stderr=true

[program:cron]
command=crond -n

[group:allservices]
programs=httpd,cron,php-fpm,$PANDORA_BIN,tentacle

[rpcinterface:supervisor]
supervisor.rpcinterface_factory = supervisor.rpcinterface:make_main_rpcinterface

[supervisorctl]
serverurl=unix:///tmp/supervisor.sock

EOF_CON

# execute supervisor
touch /var/log/pandora/pandora_server.log ; tail -F /var/log/pandora/* &
su - -c supervisord
