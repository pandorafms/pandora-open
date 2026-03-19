![logo Pandora-Open](https://pandoraopen.io/wp-content/uploads/2025/12/Pandora-Open-mini.png)

Pandora Open is a __monitoring solution__ that provides unparalleled flexibility for IT to address both immediate and unforeseen operational issues, including infrastructure and IT processes.

# Pandora FMS full stack

## Usage
```
docker run --name %container_name% \
-p %local_httpd_port%:80 \
-p %local_tentacle_port%:41121 \
-e DBHOST=%Mysql_Server_IP% \
-e DBNAME=%database_name% \
-e DBUSER=%Mysql_user% \
-e DBPASS=%Mysql_pass% \
-e DBPORT=%Mysql_port% \
-e INSTANCE_NAME=%server name% \
-ti ghcr.io/pandorafms/pandoraopen:el9
```
Example:
```
docker run --name Pandora-Open \
-p 8081:80 \
-p 41121:41121 \
-e DBHOST=10.1.1.2 \
-e DBNAME='pandora' \
-e DBUSER='pandora' \
-e DBPASS='Pandor4_' \
-e DBPORT=3306 \
-e INSTANCE_NAME=pandoraopen \
-ti ghcr.io/pandorafms/pandoraopen:el9
```

### Database for Pandora Open container
The recomended way to install Panadora Open its in a MySQL/mariadb database, here is the configuration to deploy a mariadb container to use with Pandora Open.

Example:
```
ddocker run --rm \
  --name mariadb-pandoraopen \
  -p 3306:3306 \
  -e MARIADB_ROOT_PASSWORD='Pandor4_' \
  -e MARIADB_DATABASE='pandora' \
  -e MARIADB_USER='pandora' \
  -e MARIADB_PASSWORD='Pandor4_' \
  mariadb:11.8 --sql-mode=""
```

This creates a MariaDB container and a database called pandora with grants to the pandora user (optional) and the credentials for root user. 

In this example we expose the 3306 for database connection. 

Using this configuration (getting the container ip from DB container ip) you can execute the next container Pandora pointing to it:

```
docker run --name Pandora_new \
-p 8081:80 \
-p 41121:41121 \
-e DBHOST=<percona container ip> \
-e DBNAME='pandora' \
-e DBUSER='pandora' \
-e DBPASS='Pandor4_' \
-e DBPORT=3306 \
-e INSTANCE_NAME=pandoraopen \
-ti ghcr.io/pandorafms/pandoraopen:el9
```

## Docker Compose Stack

if you want to run an easy to deploy stack you may use the docker-compose.yml file

```
services:
  pandora-open-db:
    image: mariadb:11.8
    platform: linux/amd64
    container_name: mariadb-pandora
    restart: unless-stopped
    ports:
      - "3306:3306"
    environment:
      MARIADB_DATABASE: pandora
      MARIADB_USER: pandora
      MARIADB_PASSWORD: Pandor4_
      MARIADB_ROOT_PASSWORD: Pandor4_
    command: --sql-mode=""
    volumes:
      - mariadb_data:/var/lib/mysql
    networks:
     - pandora
    healthcheck:
      test: ["CMD", "healthcheck.sh", "--connect", "--innodb_initialized"]
      interval: 10s
      timeout: 5s
      retries: 5

  pandora-open:
    image: ghcr.io/pandorafms/pandoraopen:el9
    platform: linux/amd64
    restart: unless-stopped
    depends_on:
      pandora-open-db:
        condition: service_healthy
    environment:
      TZ: Europe/Madrid
      DBHOST: pandora-open-db
      DBNAME: pandora
      DBUSER: pandora
      DBPASS: Pandor4_
      DBPORT: 3306
      INSTANCE_NAME: PandoraOpen
      PUBLICURL: ""
      SLEEP: 5
      RETRIES: 10
    networks:
     - pandora
    ports:
      - "8081:80"
      - "41121:41121"
      - "162:162/udp"
      - "9995:9995/udp"
    healthcheck:
      test: ["CMD-SHELL", "curl -sf http://localhost/ > /dev/null || exit 1"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 40s

volumes:
  mariadb_data:

networks:
  pandora:

```
Run it with command: 
```
docker-compose -f <docker-compose-file> up
```

## Important Parameters:

* __TZ__: Time Zone Identifier in IANA format.
* __INSTANCE_NAME__: Pandora Server name
* __DBHOST__: DB host IP  to MySQL/MariaDB engine
* __DBNAME__: The name of the database. If the user have enough permissions to create databases, the container will create it automatically if didn't exist
* __DBUSER__: The user to connect MySQL engine.
* __DBPASS__: User password to connect MySQL engine.
* __DBPORT__: The port to connect MySQL engine. by default 3306
* __PUBLICURL__: Define a public URL. Useful when Pandora is used behind a reverse proxy
* __RETRIES__: How many times Pandora Open container will try to connect to MySQL/MariaDB engine before fail.
* __SLEEP__: Time to wait between retries

Note1: the SLEEP and RETRIES variables will be used to wait for database container to fully start, if you are in a slower system maybe you will need to increase these variables values, in this example will wait 5 seconds for the database container to be up and retries 3 times.

## Remove PandoraOpen

To delete the containes and its content run the command: `docker-compose -f <docker-compose-file> down -v`
This will cleanup all information and delete the volume for the database
