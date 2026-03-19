#!/bin/bash
##############################################################################################################
# Pandora Open online installation script for Debian, Ubuntu and Rocky Linux
##############################################################################################################

# Avoid prompts
export DEBIAN_FRONTEND=noninteractive
export NEEDRESTART_SUSPEND=1

# Constants
PANDORA_CONSOLE=/var/www/html/pandora_console
PANDORA_SERVER_CONF=/etc/pandora/pandora_server.conf
PANDORA_AGENT_CONF=/etc/pandora/pandora_agent.conf
WORKDIR="/tmp/pandoraopen.tmp"
LOGFILE="/tmp/pandora-deploy-$(date +%F).log"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_PATH="$(cd "${SCRIPT_DIR}/.." && pwd)"

# Capture the first command line argument as the package directory
PACKAGE_DIR="${1:-$REPO_PATH}"

# Ensure the specified directory exists
if [ ! -d "$PACKAGE_DIR" ]; then
    echo "ERROR: The specified package directory '$PACKAGE_DIR' does not exist. Exiting." >&2
    exit 1
fi

# Remove the previous log before starting.
rm -f "$LOGFILE" &> /dev/null

# Pandora Open version
CONFIG_FILE="${REPO_PATH}/pandora_server/lib/PandoraOpen/Config.pm"
PANDORA_VERSION=$(grep 'our $VERSION =' "$CONFIG_FILE" | awk -F'"' '{print $2}')
if [ -z "$PANDORA_VERSION" ]; then
    echo "ERROR: Could not extract version from $CONFIG_FILE. Exiting." >&2
    exit 1
fi

# Default variables
[ "$TZ" ] || TZ="Europe/Madrid"
[ "$PHPVER" ] || PHPVER=auto
[ "$DBHOST" ] || DBHOST=127.0.0.1
[ "$DBNAME" ] || DBNAME=pandora
[ "$DBUSER" ] || DBUSER=pandora
[ "$DBPASS" ] || DBPASS='Pandor4_'
[ "$DBPORT" ] || DBPORT=3306
[ "$DBROOTPASS" ] || DBROOTPASS='Pandor4_'
[ "$SKIP_PRECHECK" ] || SKIP_PRECHECK=0
[ "$SKIP_DATABASE_INSTALL" ] || SKIP_DATABASE_INSTALL=0
[ "$SKIP_KERNEL_OPTIMIZATIONS" ] || SKIP_KERNEL_OPTIMIZATIONS=0
[ "$POOL_SIZE" ] || POOL_SIZE=$(grep -i total /proc/meminfo | head -1 | awk '{printf "%.2f \n", $(NF-1)*0.4/1024}' | sed "s/\\..*$/M/g")

MYSQL_ROOT_LOCAL_AUTH_MODE="auto"
MYSQL_ROOT_BOOTSTRAP_ATTEMPTED=0

# Check if possible to get os version
if [ ! -e /etc/os-release ]; then
    echo ' > Unable to determine the OS version for this machine; ensure you are installing on a compatible OS.'
    echo ' > More info: https://.io'
    exit -1
fi

# ANSI color code variables
red="\e[0;91m"
green="\e[0;92m"
cyan="\e[0;36m"
yellow="\e[0;93m"
reset="\e[0m"

# Force LTS to install PHP 8.2.
[ "$PANDORA_LTS" = '1' ] && PHPVER=8.2

########################################################################
# Functions
########################################################################
execute_cmd () {
    local cmd="$1"
    local msg="$2"
    local extra="$3"

    echo -en "${cyan}${msg}... ${reset}"
    eval "$cmd" &>> "$LOGFILE"
    local status=$?
    if [ $status -ne 0 ]; then
        echo -e "${red}Failed${reset}"
        [ -n "$extra" ] && echo "$extra"
        echo "Error installing Pandora Open. For details, check the log: $LOGFILE"
        rm -rf "$WORKDIR" &>> "$LOGFILE"
        exit 1
    fi
    echo -e "${green}OK${reset}"
    return 0
}

check_cmd_status () {
    local status=$?
    local extra="$1"
    if [ $status -ne 0 ]; then
        echo -e "${red}Failed${reset}"
        [ -n "$extra" ] && echo "$extra"
        echo "Error installing Pandora Open. For details, check the log: $LOGFILE"
        # Best-effort cleanup of the workdir
        rm -rf "$WORKDIR" &>> "$LOGFILE"
        exit 1
    fi
    return 0
}

filter_apt_packages () {
    local pkg
    local filtered=""
    for pkg in "$@"; do
        local policy
        local candidate
        policy=$(apt-cache policy "$pkg" 2>> "$LOGFILE")
        candidate=$(printf "%s\n" "$policy" | awk '/Candidate:/ {print $2}')
        if [ -n "$candidate" ] && [ "$candidate" != "(none)" ]; then
            filtered="${filtered} ${pkg}"
        else
            echo "Skipping missing package: $pkg" &>> "$LOGFILE"
        fi
    done
    echo "$filtered"
}

is_local_dbhost () {
    case "$DBHOST" in
        localhost|127.0.0.1) return 0 ;;
        *) return 1 ;;
    esac
}

mysql_escape_sql_string () {
    printf '%s' "$1" | sed "s/\\\\/\\\\\\\\/g; s/'/''/g"
}

mysql_escape_mysql_identifier () {
    printf '%s' "$1" | sed 's/`/``/g'
}

mysql_bootstrap_set_root_password () {
    local tmp_root_pass

    if ! is_local_dbhost; then
        return 1
    fi

    if mysql --version 2>/dev/null | grep -qi mariadb; then
        return 0
    fi

    tmp_root_pass=$(grep "temporary password" /var/log/mysqld.log 2>> "$LOGFILE" | tail -n 1 | awk '{print $NF}')
    if [ -z "$tmp_root_pass" ]; then
        echo "Could not read MySQL temporary root password from /var/log/mysqld.log" &>> "$LOGFILE"
        return 1
    fi

    MYSQL_PWD="$tmp_root_pass" mysql --connect-expired-password -uroot --protocol=socket -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '$DBROOTPASS_SQL';" &>> "$LOGFILE"
    return $?
}

detect_mysql_root_auth_mode () {
    if ! is_local_dbhost; then
        MYSQL_ROOT_LOCAL_AUTH_MODE="password"
        return 0
    fi

    if [ "$MYSQL_ROOT_LOCAL_AUTH_MODE" = "socket" ] || [ "$MYSQL_ROOT_LOCAL_AUTH_MODE" = "password" ]; then
        return 0
    fi

    env -u MYSQL_PWD mysql -uroot --protocol=socket -N -s -e "SELECT 1;" &>> "$LOGFILE"
    if [ $? -eq 0 ]; then
        MYSQL_ROOT_LOCAL_AUTH_MODE="socket"
        return 0
    fi

    MYSQL_PWD="$DBROOTPASS" mysql -uroot --protocol=socket -N -s -e "SELECT 1;" &>> "$LOGFILE"
    if [ $? -eq 0 ]; then
        MYSQL_ROOT_LOCAL_AUTH_MODE="password"
        return 0
    fi

    if ! mysql --version 2>/dev/null | grep -qi mariadb && [ "$MYSQL_ROOT_BOOTSTRAP_ATTEMPTED" -eq 0 ]; then
        MYSQL_ROOT_BOOTSTRAP_ATTEMPTED=1
        mysql_bootstrap_set_root_password
        if [ $? -eq 0 ]; then
            MYSQL_PWD="$DBROOTPASS" mysql -uroot --protocol=socket -N -s -e "SELECT 1;" &>> "$LOGFILE"
            if [ $? -eq 0 ]; then
                MYSQL_ROOT_LOCAL_AUTH_MODE="password"
                return 0
            fi
        fi
    fi

    return 1
}

mysql_root_exec () {
    if ! is_local_dbhost; then
        MYSQL_PWD="$DBROOTPASS" mysql -uroot -h"$DBHOST" -P"$DBPORT" "$@" &>> "$LOGFILE"
        return $?
    fi

    detect_mysql_root_auth_mode
    if [ $? -ne 0 ]; then
        return 1
    fi

    if [ "$MYSQL_ROOT_LOCAL_AUTH_MODE" = "socket" ]; then
        env -u MYSQL_PWD mysql -uroot --protocol=socket "$@" &>> "$LOGFILE"
        return $?
    fi

    MYSQL_PWD="$DBROOTPASS" mysql -uroot --protocol=socket "$@" &>> "$LOGFILE"
    return $?
}

mysql_root_query () {
    if ! is_local_dbhost; then
        MYSQL_PWD="$DBROOTPASS" mysql -uroot -h"$DBHOST" -P"$DBPORT" -N -s -e "$1" 2>> "$LOGFILE"
        return $?
    fi

    detect_mysql_root_auth_mode
    if [ $? -ne 0 ]; then
        return 1
    fi

    if [ "$MYSQL_ROOT_LOCAL_AUTH_MODE" = "socket" ]; then
        env -u MYSQL_PWD mysql -uroot --protocol=socket -N -s -e "$1" 2>> "$LOGFILE"
        return $?
    fi

    MYSQL_PWD="$DBROOTPASS" mysql -uroot --protocol=socket -N -s -e "$1" 2>> "$LOGFILE"
    return $?
}

set_root_password_post_install () {
    if ! is_local_dbhost; then
        return 0
    fi

    if mysql --version 2>/dev/null | grep -qi mariadb; then
        return 0
    fi

    mysql_root_exec -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '$DBROOTPASS_SQL';"
    check_cmd_status "Error setting MySQL root password in post-install step"

    MYSQL_ROOT_LOCAL_AUTH_MODE="password"
    MYSQL_PWD="$DBROOTPASS" mysql -uroot --protocol=socket -e "SELECT 1;" &>> "$LOGFILE"
    check_cmd_status "Error verifying MySQL root password in post-install step"
}

mysql_app_exec () {
    if is_local_dbhost; then
        MYSQL_PWD="$DBPASS" mysql -u"$DBUSER" --protocol=socket "$@" &>> "$LOGFILE"
        return $?
    fi

    MYSQL_PWD="$DBPASS" mysql -u"$DBUSER" -h"$DBHOST" -P"$DBPORT" "$@" &>> "$LOGFILE"
    return $?
}

mysql_app_import () {
    local database="$1"
    local sql_file="$2"

    if is_local_dbhost; then
        MYSQL_PWD="$DBPASS" mysql -u"$DBUSER" --protocol=socket "$database" < "$sql_file" &>> "$LOGFILE"
        return $?
    fi

    MYSQL_PWD="$DBPASS" mysql -u"$DBUSER" -h"$DBHOST" -P"$DBPORT" "$database" < "$sql_file" &>> "$LOGFILE"
    return $?
}

detect_php_fpm_service () {
    local svc
    for svc in "php${PHPVER}-fpm" "php-fpm"; do
        if systemctl list-unit-files | awk '{print $1}' | grep -q "^${svc}\.service$"; then
            echo "$svc"
            return 0
        fi
    done
    svc=$(systemctl list-unit-files | awk '{print $1}' | grep -E '^php[0-9]+\.[0-9]+-fpm\.service$' | head -1 | sed 's/\.service$//')
    if [ -n "$svc" ]; then
        echo "$svc"
        return 0
    fi
    echo "php-fpm"
}

enable_ondrej_php_repo () {
    execute_cmd "$PKG_INSTALL software-properties-common" "Installing software-properties-common"
    execute_cmd "add-apt-repository -y ppa:ondrej/php" "Enabling ondrej/php repository"
    execute_cmd "$PKG_UPDATE" "Updating repos after PPA"
}

enable_ondrej_php_repo_quiet () {
    apt install -y software-properties-common &>> "$LOGFILE"
    if [ $? -ne 0 ]; then
        echo "Failed to install software-properties-common for ondrej/php repository" &>> "$LOGFILE"
        return 1
    fi

    add-apt-repository -y ppa:ondrej/php &>> "$LOGFILE"
    if [ $? -ne 0 ]; then
        echo "Failed to enable ondrej/php repository" &>> "$LOGFILE"
        return 1
    fi

    apt update -y &>> "$LOGFILE"
    if [ $? -ne 0 ]; then
        echo "Failed to update repositories after enabling ondrej/php repository" &>> "$LOGFILE"
        return 1
    fi

    return 0
}

enable_remi_php_repo () {
    local major="${VERSION%%.*}"
    execute_cmd "$PKG_INSTALL https://rpms.remirepo.net/enterprise/remi-release-${major}.rpm" "Enabling Remi repository"
    execute_cmd "dnf module reset php -y" "Resetting PHP module stream"
    execute_cmd "dnf module enable php:remi-${PHPVER} -y" "Enabling Remi PHP ${PHPVER} module stream"
    execute_cmd "$PKG_UPDATE" "Updating repos after Remi"
}

install_php_mcrypt_debian () {
    local pkg="${PHP_PKG_PREFIX}mcrypt"
    local candidate

    if php -m 2>/dev/null | grep -qi "^mcrypt$"; then
        echo "mcrypt already enabled" &>> "$LOGFILE"
        return 0
    fi

    candidate=$(apt-cache policy "$pkg" 2>> "$LOGFILE" | awk '/Candidate:/ {print $2}')
    if [ -z "$candidate" ] || [ "$candidate" = "(none)" ]; then
        echo "Skipping optional package: $pkg (not available)" &>> "$LOGFILE"
        return 0
    fi

    apt install -y "$pkg"
    return $?
}

resolve_debian_phpver () {
    local requested="$1"
    local v
    local cand
    local selected=""

    # Explicit version requested (not auto): use as-is.
    if [ -n "$requested" ] && [ "$requested" != "auto" ]; then
        echo "$requested"
        return 0
    fi

    # Pick highest available PHP 8.x from default repos first.
    for v in 8.4 8.3 8.2; do
        cand=$(apt-cache policy "php${v}" 2>> "$LOGFILE" | awk '/Candidate:/ {print $2}')
        if [ -n "$cand" ] && [ "$cand" != "(none)" ]; then
            selected="$v"
            break
        fi
    done

    if [ -n "$selected" ]; then
        echo "$selected"
        return 0
    fi

    # Nothing available in current repos, try ondrej/php.
    if ! enable_ondrej_php_repo_quiet; then
        echo ""
        return 1
    fi

    for v in 8.4 8.3 8.2; do
        cand=$(apt-cache policy "php${v}" 2>> "$LOGFILE" | awk '/Candidate:/ {print $2}')
        if [ -n "$cand" ] && [ "$cand" != "(none)" ]; then
            selected="$v"
            break
        fi
    done

    if [ -n "$selected" ]; then
        echo "$selected"
        return 0
    fi

    echo ""
    return 1
}

resolve_debian_phpver_installable () {
    local requested="$1"
    local v
    local cand

    # If explicit version was requested, honor it but verify installability.
    if [ -n "$requested" ] && [ "$requested" != "auto" ]; then
        cand=$(apt-cache policy "php${requested}" 2>> "$LOGFILE" | awk '/Candidate:/ {print $2}')
        if [ -n "$cand" ] && [ "$cand" != "(none)" ]; then
            echo "$requested"
            return 0
        fi

        if ! enable_ondrej_php_repo_quiet; then
            echo ""
            return 1
        fi

        cand=$(apt-cache policy "php${requested}" 2>> "$LOGFILE" | awk '/Candidate:/ {print $2}')
        if [ -n "$cand" ] && [ "$cand" != "(none)" ]; then
            echo "$requested"
            return 0
        fi

        echo ""
        return 1
    fi

    # Auto mode: try highest preferred versions with a real candidate.
    for v in 8.4 8.3 8.2; do
        cand=$(apt-cache policy "php${v}" 2>> "$LOGFILE" | awk '/Candidate:/ {print $2}')
        if [ -n "$cand" ] && [ "$cand" != "(none)" ]; then
            echo "$v"
            return 0
        fi
    done

    # If nothing found, enable ondrej/php and retry.
    if ! enable_ondrej_php_repo_quiet; then
        echo ""
        return 1
    fi
    for v in 8.4 8.3 8.2; do
        cand=$(apt-cache policy "php${v}" 2>> "$LOGFILE" | awk '/Candidate:/ {print $2}')
        if [ -n "$cand" ] && [ "$cand" != "(none)" ]; then
            echo "$v"
            return 0
        fi
    done

    echo ""
    return 1
}

resolve_rocky_phpver () {
    local requested="$1"
    local selected=""
    local stream
    local stream_ver
    local major="${VERSION%%.*}"

    # Explicit version requested (not auto): use as-is.
    if [ -n "$requested" ] && [ "$requested" != "auto" ]; then
        echo "$requested"
        return 0
    fi

    # Ensure Remi repo is available so remi-* php module streams are visible.
    # IMPORTANT: keep this function stdout-clean (only echo selected version).
    if ! rpm -q remi-release &>> "$LOGFILE"; then
        $PKG_INSTALL "https://rpms.remirepo.net/enterprise/remi-release-${major}.rpm" &>> "$LOGFILE"
        if [ $? -ne 0 ]; then
            echo ""
            return 1
        fi
    fi

    # Refresh module metadata quietly.
    dnf module reset php -y &>> "$LOGFILE"
    if [ $? -ne 0 ]; then
        echo ""
        return 1
    fi

    $PKG_UPDATE &>> "$LOGFILE"
    if [ $? -ne 0 ]; then
        echo ""
        return 1
    fi

    # Pick highest available remi-8.x stream.
    while IFS= read -r stream; do
        stream_ver="${stream#remi-}"
        if [[ "$stream_ver" =~ ^8\.[2-9][0-9]*$ ]]; then
            if [ -z "$selected" ] || [ "$(printf '%s\n%s\n' "$selected" "$stream_ver" | sort -V | tail -1)" = "$stream_ver" ]; then
                selected="$stream_ver"
            fi
        fi
    done < <(dnf -q module list php --all 2>> "$LOGFILE" | awk '{print $2}' | grep -E '^remi-8\.[2-9][0-9]*$' | sort -u)

    if [ -n "$selected" ]; then
        echo "$selected"
        return 0
    fi

    echo ""
    return 1
}

check_pre_pandora () {

    echo -en "${cyan}Checking environment ... ${reset}"
    [ -d "$PANDORA_CONSOLE" ] && local fail=true
    [ -f /usr/bin/pandora_server ] && local fail=true
    mysql_app_exec "$DBNAME" -e "SELECT 1;" && local fail=true

    [ ! $fail ]
    check_cmd_status 'Error: an existing Pandora Open installation was found on this node. (Server, Console or Database found). Please remove it to perform a clean install.'
}

check_root_permissions () {
    echo -en "${cyan}Checking root account... ${reset}"
    if [ "$(whoami)" != "root" ]; then
        echo -e "${red}Failed${reset}"
        echo "Please use the root account or sudo to install Pandora Open."
        echo "Error installing Pandora Open. For details, check the log: $LOGFILE"
        exit 1

    else
        echo -e "${green}OK${reset}"
    fi
}

is_mysql_secure_password() {
    local password=$1

    # Require a non-empty password
    if [ -z "$password" ]; then
        echo "Password cannot be empty."
        return 1
    fi

    # Check password length (at least 8 characters)
    if [[ ${#password} -lt 8 ]]; then
        echo "Password length should be at least 8 characters."
        return 1
    fi

    # Check for at least one uppercase, one lowercase, one digit and one special character.
    if ! [[ $password =~ [A-Z] ]]; then
        echo "Password should contain at least one uppercase letter."
        return 1
    fi
    if ! [[ $password =~ [a-z] ]]; then
        echo "Password should contain at least one lowercase letter."
        return 1
    fi
    if ! [[ $password =~ [0-9] ]]; then
        echo "Password should contain at least one digit."
        return 1
    fi
    if ! [[ $password =~ [[:punct:]] ]]; then
        echo "Password should contain at least one special character."
        return 1
    fi

    # Disallow common weak patterns (case-insensitive)
    local common_patterns=("password" "123456" "qwerty" "admin" "pandora")
    for pattern in "${common_patterns[@]}"; do
        if [[ ${password,,} == *"$pattern"* ]]; then
            echo "Password should not contain common patterns like '$pattern'."
            return 1
        fi
    done

    # Disallow passwords that are identical to username-like tokens (basic check)
    if [[ ${password,,} == "root" || ${password,,} == "admin" ]]; then
        echo "Password is too similar to common usernames."
        return 1
    fi

    # All checks passed
    return 0
}

installing_docker () {
    echo "Starting Docker installation" &>> "$LOGFILE"
    if [ "$OS_FAMILY" = "debian" ]; then
        mkdir -m 0755 -p /etc/apt/keyrings &>> "$LOGFILE"
        curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --yes --dearmor -o /etc/apt/keyrings/docker.gpg &>> "$LOGFILE"
        echo \
            "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
            $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list &>> "$LOGFILE"
        apt update -y &>> "$LOGFILE"
        apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin &>> "$LOGFILE"
    else
        dnf install -y dnf-plugins-core &>> "$LOGFILE"
        dnf config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo &>> "$LOGFILE"
        dnf install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin &>> "$LOGFILE"
    fi
    systemctl disable docker --now &>> "$LOGFILE"
    systemctl disable docker.socket --now &>> "$LOGFILE"
    echo "Finished installing Docker" &>> "$LOGFILE"
}

install_google_chrome () {
    local arch
    local chrome_url
    local chrome_pkg
    local chrome_key
    local chrome_bin

    arch=$(uname -m)
    case "$arch" in
        x86_64|amd64)
            ;;
        *)
            echo "Google Chrome installation is only supported on x86_64/amd64. Detected architecture: $arch" &>> "$LOGFILE"
            return 1
            ;;
    esac

    chrome_bin=$(command -v google-chrome-stable 2>/dev/null || command -v google-chrome 2>/dev/null)

    if [ -z "$chrome_bin" ]; then
        if [ "$OS_FAMILY" = "debian" ]; then
            chrome_url="https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb"
            chrome_pkg="$WORKDIR/google-chrome-stable_current_amd64.deb"

            curl -fsSL "$chrome_url" -o "$chrome_pkg" &>> "$LOGFILE" || {
                echo "Failed to download Google Chrome package from $chrome_url" &>> "$LOGFILE"
                return 1
            }

            apt install -y "$chrome_pkg" &>> "$LOGFILE" || {
                echo "Failed to install Google Chrome package $chrome_pkg" &>> "$LOGFILE"
                return 1
            }
        else
            chrome_url="https://dl.google.com/linux/direct/google-chrome-stable_current_x86_64.rpm"
            chrome_pkg="$WORKDIR/google-chrome-stable_current_x86_64.rpm"
            chrome_key="$WORKDIR/google-linux-signing-key.pub"

            curl -fsSL "https://dl.google.com/linux/linux_signing_key.pub" -o "$chrome_key" &>> "$LOGFILE" || {
                echo "Failed to download Google Linux signing key" &>> "$LOGFILE"
                return 1
            }

            rpm --import "$chrome_key" &>> "$LOGFILE" || {
                echo "Failed to import Google Linux signing key from $chrome_key" &>> "$LOGFILE"
                return 1
            }

            curl -fsSL "$chrome_url" -o "$chrome_pkg" &>> "$LOGFILE" || {
                echo "Failed to download Google Chrome package from $chrome_url" &>> "$LOGFILE"
                return 1
            }

            dnf install -y "$chrome_pkg" &>> "$LOGFILE" || {
                echo "Failed to install Google Chrome package $chrome_pkg" &>> "$LOGFILE"
                return 1
            }
        fi

        chrome_bin=$(command -v google-chrome-stable 2>/dev/null || command -v google-chrome 2>/dev/null)
    fi

    if [ -z "$chrome_bin" ]; then
        echo "Google Chrome binary was not found after installation" &>> "$LOGFILE"
        return 1
    fi

    ln -sfn "$chrome_bin" /usr/bin/chromium-browser &>> "$LOGFILE" || {
        echo "Failed to create /usr/bin/chromium-browser symlink to $chrome_bin" &>> "$LOGFILE"
        return 1
    }

    "$chrome_bin" --version &>> "$LOGFILE" || {
        echo "Google Chrome was installed but the version check failed" &>> "$LOGFILE"
        return 1
    }

    return 0
}

detect_mysql_service () {
    local svc
    for svc in mysqld mysql mariadb; do
        if systemctl list-unit-files | awk '{print $1}' | grep -q "^${svc}\.service$"; then
            echo "$svc"
            return 0
        fi
    done
    echo "mysql"
}

apache_configtest () {
    if command -v apache2ctl &> /dev/null; then
        apache2ctl configtest &>> "$LOGFILE"
        return $?
    fi
    if command -v apachectl &> /dev/null; then
        apachectl configtest &>> "$LOGFILE"
        return $?
    fi
    if command -v httpd &> /dev/null; then
        httpd -t &>> "$LOGFILE"
        return $?
    fi
    return 1
}

ensure_rocky_httpd_ssl_files () {
    local ssl_conf="/etc/httpd/conf.d/ssl.conf"
    local crt="/etc/pki/tls/certs/localhost.crt"
    local key="/etc/pki/tls/private/localhost.key"
    local cn

    [ "$OS_FAMILY" = "rocky" ] || return 0

    if [ -f "$ssl_conf" ]; then
        crt=$(awk '/^[[:space:]]*SSLCertificateFile[[:space:]]+/ {print $2; exit}' "$ssl_conf")
        key=$(awk '/^[[:space:]]*SSLCertificateKeyFile[[:space:]]+/ {print $2; exit}' "$ssl_conf")

        crt="${crt%\"}"
        crt="${crt#\"}"
        key="${key%\"}"
        key="${key#\"}"

        [ -n "$crt" ] || crt="/etc/pki/tls/certs/localhost.crt"
        [ -n "$key" ] || key="/etc/pki/tls/private/localhost.key"
    fi

    if [ -s "$crt" ] && [ -s "$key" ]; then
        return 0
    fi

    if ! command -v openssl &> /dev/null; then
        $PKG_INSTALL openssl &>> "$LOGFILE"
        if [ $? -ne 0 ]; then
            echo "Failed to install openssl for Apache SSL certificate generation" &>> "$LOGFILE"
            return 1
        fi
    fi

    mkdir -p "$(dirname "$crt")" "$(dirname "$key")" &>> "$LOGFILE"
    if [ $? -ne 0 ]; then
        echo "Failed to create Apache SSL certificate directories" &>> "$LOGFILE"
        return 1
    fi

    if [ -x /usr/libexec/httpd-ssl-gencerts ]; then
        /usr/libexec/httpd-ssl-gencerts &>> "$LOGFILE"
        if [ $? -ne 0 ]; then
            echo "httpd-ssl-gencerts failed; falling back to openssl self-signed certificate generation" &>> "$LOGFILE"
        fi
    fi

    if [ ! -s "$crt" ] || [ ! -s "$key" ]; then
        cn=$(hostname -f 2>/dev/null)
        [ -n "$cn" ] || cn=$(hostname 2>/dev/null)
        [ -n "$cn" ] || cn="localhost"

        openssl req -x509 -nodes -newkey rsa:2048 -sha256 -days 3650 \
            -keyout "$key" \
            -out "$crt" \
            -subj "/CN=$cn" &>> "$LOGFILE"
        if [ $? -ne 0 ]; then
            echo "Failed to generate self-signed Apache SSL certificate" &>> "$LOGFILE"
            return 1
        fi
    fi

    chmod 600 "$key" &>> "$LOGFILE"
    if [ $? -ne 0 ]; then
        echo "Failed to set permissions on Apache SSL private key" &>> "$LOGFILE"
        return 1
    fi

    chmod 644 "$crt" &>> "$LOGFILE"
    if [ $? -ne 0 ]; then
        echo "Failed to set permissions on Apache SSL certificate" &>> "$LOGFILE"
        return 1
    fi

    if command -v restorecon &> /dev/null; then
        restorecon -Rv "$(dirname "$crt")" "$(dirname "$key")" &>> "$LOGFILE" || true
    fi

    [ -s "$crt" ] && [ -s "$key" ]
}

########################################################################
# Main
########################################################################
echo "Starting Pandora Open deployment (multi-distro)"

# Check tools
if ! grep --version &>> $LOGFILE ; then echo 'Error: grep is not available on the system; it is required for installation.'; exit -1 ;fi 
if ! sed --version &>> $LOGFILE ; then echo 'Error: sed is not available on the system; it is required for installation.'; exit -1 ;fi 
if ! curl --version &>> $LOGFILE ; then echo 'Error: curl is not available on the system; it is required for installation.'; exit -1 ;fi 
if ! ping -V &>> $LOGFILE ; then echo 'Error: ping is not available on the system; it is required for installation.'; exit -1 ;fi 

# Detect Operating System
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$ID
    VERSION=$VERSION_ID
else
    echo "Cannot determine OS. Exiting."
    exit 1
fi

# Set package manager commands
if [[ "$OS" == "ubuntu" || "$OS" == "debian" ]]; then
    OS_FAMILY="debian"
    PKG_MANAGER="apt"
    PKG_UPDATE="apt update -y"
    PKG_INSTALL="apt install -y"
    APACHE_PACKAGE="apache2"
    APACHE_SERVICE="apache2"
    APACHE_USER="www-data"
    MYSQL_PACKAGE="mariadb-server"
    MYSQL_CNF="/etc/mysql/my.cnf"
    PHPVER_RESOLVED=$(resolve_debian_phpver_installable "$PHPVER")
    if [ -z "$PHPVER_RESOLVED" ]; then
        echo "Error: Could not find an installable PHP 8.4/8.3/8.2 version in APT repositories." >&2
        exit 1
    fi
    PHPVER="$PHPVER_RESOLVED"
    PHP_PKG_PREFIX="php${PHPVER}-"
    PHP_CORE_PACKAGE="php${PHPVER}"
    PHP_FPM_SERVICE="php${PHPVER}-fpm"
    PHP_PACKAGES="$PHP_CORE_PACKAGE ${PHP_PKG_PREFIX}fpm ${PHP_PKG_PREFIX}cli ${PHP_PKG_PREFIX}common"
elif [[ "$OS" == "rocky" ]]; then
    OS_FAMILY="rocky"
    PKG_MANAGER="dnf"
    PKG_UPDATE="dnf makecache -y"
    PKG_INSTALL="dnf install -y"
    APACHE_PACKAGE="httpd"
    APACHE_SERVICE="httpd"
    APACHE_USER="apache"
    MYSQL_PACKAGE="mysql-server"
    MYSQL_CNF="/etc/my.cnf"
    PHPVER_RESOLVED=$(resolve_rocky_phpver "$PHPVER")
    if [ -z "$PHPVER_RESOLVED" ]; then
        echo "Error: Could not find an installable PHP >= 8.2 Remi stream on Rocky." >&2
        exit 1
    fi
    PHPVER="$PHPVER_RESOLVED"
    PHP_PACKAGES="php php-fpm php-cli php-common"
else
    echo "Unsupported OS. Exiting."
    exit 1
fi

# Get the OS name
os_name=$(grep ^PRETTY_NAME= /etc/os-release | cut -d '=' -f2 | tr -d '"')
echo -e "${cyan}OS detected: ${os_name}...${reset} ${green}OK${reset}"

# Initialize log file
execute_cmd "echo 'Starting deployment' > $LOGFILE" "All installer activity is logged on $LOGFILE"

# Check root permissions
check_root_permissions

# Check if Pandora Open is already installed
[ "$SKIP_PRECHECK" == 1 ] || check_pre_pandora

# Announce install mode
if [ "$PANDORA_LTS" = '1' ]; then
    echo -e "${green}LTS mode enabled${reset}"
else
    echo -e "${cyan}Standard mode enabled${reset}"
fi

# Install awk, sed, and grep if not present
execute_cmd "$PKG_INSTALL gawk sed grep" 'Installing needed tools'

# Check for systemd
execute_cmd "systemctl --version" "Checking SystemD" 'This system does not use systemd.'

# Check memory is at least 2 GB
execute_cmd  "[ $(grep MemTotal /proc/meminfo | awk '{print $2}') -ge 1700000 ]" 'Checking memory (required: 2 GB)'

# Check disk has at least 10 GB free
execute_cmd "[ $(df -BM / | tail -1 | awk '{print $4}' | tr -d M) -gt 10000 ]" 'Checking Disk (required: 10 GB free min)'

# Set the timezone
rm -rf /etc/localtime &>> "$LOGFILE"
execute_cmd "timedatectl set-timezone $TZ" "Setting Timezone $TZ"

# Check for required tools
execute_cmd "awk --version" 'Checking needed tools: awk'
execute_cmd "grep --version" 'Checking needed tools: grep'
execute_cmd "sed --version" 'Checking needed tools: sed'
execute_cmd "$PKG_MANAGER --version" "Checking needed tools: $PKG_MANAGER"

# Check MySQL passwords
echo -en "${cyan}Checking DBROOTPASS password matches policy... ${reset}"
is_mysql_secure_password "$DBROOTPASS" &>> "$LOGFILE"
check_cmd_status 'This password does not meet the minimum MySQL policy requirements. More info: https://dev.mysql.com/doc/refman/8.0/en/validate-password.html'
echo -e "${green}OK${reset}"

echo -en "${cyan}Checking DBPASS password matches policy... ${reset}"
is_mysql_secure_password "$DBPASS" &>> "$LOGFILE"
check_cmd_status 'This password does not meet the minimum MySQL policy requirements. More info: https://dev.mysql.com/doc/refman/8.0/en/validate-password.html'
echo -e "${green}OK${reset}"

DBNAME_SQL=$(mysql_escape_sql_string "$DBNAME")
DBNAME_IDENT=$(mysql_escape_mysql_identifier "$DBNAME")
DBUSER_SQL=$(mysql_escape_sql_string "$DBUSER")
DBPASS_SQL=$(mysql_escape_sql_string "$DBPASS")
DBROOTPASS_SQL=$(mysql_escape_sql_string "$DBROOTPASS")

# Create the work directory
rm -rf "$WORKDIR" &>> "$LOGFILE"
mkdir -p "$WORKDIR" &>> "$LOGFILE"
execute_cmd "cd '$WORKDIR'" "Moving to workdir: $WORKDIR" "Error moving to workdir: $WORKDIR"

# Install utils
execute_cmd "$PKG_UPDATE" "Updating repos"
if [ "$OS_FAMILY" = "debian" ]; then
    execute_cmd "$PKG_INSTALL curl wget gnupg lsb-release" "Installing utils"
else
    execute_cmd "$PKG_INSTALL dnf-plugins-core" "Installing dnf-plugins-core"
    if [[ "$VERSION" == 8* ]]; then
        execute_cmd "dnf config-manager --set-enabled powertools" "Enabling PowerTools repository"
    elif [[ "$VERSION" == 9* ]]; then
        execute_cmd "dnf config-manager --set-enabled crb" "Enabling CRB repository"
    fi
    execute_cmd "$PKG_INSTALL epel-release" "Enabling EPEL repository"
    execute_cmd "$PKG_UPDATE" "Updating repos after EPEL/CRB"
    execute_cmd "$PKG_INSTALL curl wget gnupg2" "Installing utils"
fi

execute_cmd "install_google_chrome" "Installing Google Chrome"

# Install PHP and Apache
if [ "$OS_FAMILY" != "debian" ]; then
    enable_remi_php_repo
fi
execute_cmd "$PKG_INSTALL $PHP_PACKAGES $APACHE_PACKAGE" "Installing PHP and Apache"
if [ "$OS_FAMILY" = "debian" ]; then
    execute_cmd "install_php_mcrypt_debian" "Installing mcrypt extension"
    if [ -x "/usr/bin/php$PHPVER" ]; then
        update-alternatives --set php "/usr/bin/php$PHPVER" &>> "$LOGFILE"
    fi
fi
PHP_ACTUAL=$(php -r 'echo PHP_VERSION;' 2>/dev/null)
if ! php -r 'exit((version_compare(PHP_VERSION, "8.2.0", ">=") && version_compare(PHP_VERSION, "9.0.0", "<")) ? 0 : 1);'; then
    echo "ERROR: PHP >= 8.2 and < 9.0 required, found ${PHP_ACTUAL:-unknown}. Exiting." >&2
    exit 1
fi

# Enable and start Apache
systemctl enable $APACHE_SERVICE --now

# Console dependencies
if [ "$OS_FAMILY" = "debian" ]; then
    console_dependencies=" \
    ldap-utils \
    postfix \
    wget \
    graphviz  \
    xfonts-75dpi \
    xfonts-100dpi \
    xfonts-ayu \
    xfonts-intl-arabic \
    xfonts-intl-asian \
    xfonts-intl-phonetic \
    xfonts-intl-japanese-big \
    xfonts-intl-european \
    xfonts-intl-chinese \
    xfonts-intl-japanese \
    xfonts-intl-chinese-big \
    libzstd1 \
    gir1.2-atk-1.0 \
    libavahi-common-data \
    cairo-perf-utils \
    libfribidi-bin \
    ${PHP_PKG_PREFIX}gd  \
    ${PHP_PKG_PREFIX}curl \
    ${PHP_PKG_PREFIX}mysql \
    ${PHP_PKG_PREFIX}ldap \
    ${PHP_PKG_PREFIX}fileinfo \
    ${PHP_PKG_PREFIX}gettext \
    ${PHP_PKG_PREFIX}snmp  \
    ${PHP_PKG_PREFIX}mbstring \
    ${PHP_PKG_PREFIX}zip  \
    ${PHP_PKG_PREFIX}xmlrpc \
    ${PHP_PKG_PREFIX}xml \
    ${PHP_PKG_PREFIX}yaml \
    libnet-telnet-perl \
    whois \
    cron"
else
    console_dependencies=" \
    openldap-clients \
    postfix \
    wget \
    graphviz \
    xorg-x11-fonts-75dpi \
    xorg-x11-fonts-100dpi \
    xorg-x11-fonts-ISO8859-1-75dpi \
    libzstd \
    atk \
    avahi-libs \
    cairo \
    fribidi \
    php-gd \
    php-curl \
    php-mysqlnd \
    php-ldap \
    php-fileinfo \
    php-gettext \
    php-snmp \
    php-mbstring \
    php-zip \
    php-xml \
    php-pecl-yaml \
    whois \
    cronie"
fi
if [ "$OS_FAMILY" = "debian" ]; then
    console_dependencies=$(filter_apt_packages $console_dependencies)
fi
execute_cmd "$PKG_INSTALL $console_dependencies" "Installing Pandora Open Console dependencies"

# Server dependencies
if [ "$OS_FAMILY" = "debian" ]; then
    server_dependencies=" \
    perl  \
    nmap  \
    fping \
    sudo \
    net-tools \
    nfdump \
    expect \
    openssh-client \
    postfix \
    unzip \
    coreutils \
    libio-compress-perl \
    libmoosex-role-timer-perl \
    libdbd-mysql-perl \
    libcrypt-mysql-perl \
    libhttp-request-ascgi-perl \
    liblwp-useragent-chicaching-perl \
    liblwp-protocol-https-perl \
    snmp \
    libnetaddr-ip-perl \
    libio-socket-ssl-perl \
    libio-socket-socks-perl \
    libio-socket-ip-perl \
    libio-socket-inet6-perl \
    libnet-telnet-perl \
    libjson-perl \
    libencode-perl \
    cron \
    libgeo-ip-perl \
    arping \
    snmp-mibs-downloader \
    snmptrapd \
    libnsl2 \
    make \
    openjdk-8-jdk "
else
    server_dependencies=" \
    perl \
    nmap \
    fping \
    sudo \
    net-tools \
    nfdump \
    expect \
    openssh-clients \
    postfix \
    unzip \
    coreutils \
    perl-IO-Compress \
    perl-DBD-MySQL \
    perl-JSON \
    perl-Encode \
    net-snmp \
    perl-NetAddr-IP \
    perl-IO-Socket-SSL \
    perl-IO-Socket-IP \
    perl-Net-Telnet \
    cronie \
    net-snmp-utils \
    net-snmp \
    net-snmp-libs \
    libnsl2 \
    make \
    java-1.8.0-openjdk "
fi
execute_cmd "$PKG_INSTALL $server_dependencies" "Installing Pandora Open Server dependencies"

echo -en "${cyan}Installing Docker for debug... ${reset}"
installing_docker &>> "$LOGFILE"
check_cmd_status "Error installing Docker for debug"
echo -e "${green}OK${reset}"

# Create symlink for fping
rm -f /usr/sbin/fping &>> "$LOGFILE"
ln -s /usr/bin/fping /usr/sbin/fping &>> "$LOGFILE"


# IPAM dependencies
if [ "$OS_FAMILY" = "debian" ]; then
    ipam_dependencies=" \
    libnetaddr-ip-perl \
    coreutils \
    libdbd-mysql-perl \
    libxml-simple-perl \
    libgeo-ip-perl \
    libio-socket-inet6-perl \
    libxml-twig-perl \
    libnetaddr-ip-perl"
else
    ipam_dependencies=" \
    perl-NetAddr-IP \
    perl-Sys-Syslog \
    perl-DBI \
    perl-XML-Simple \
    perl-IO-Socket-INET6 \
    perl-XML-Twig"
fi
execute_cmd "$PKG_INSTALL $ipam_dependencies" "Installing some PERL dependencies"

# Disable apparmor and ufw if present
if systemctl list-unit-files | grep -q "^ufw.service"; then
    systemctl stop ufw.service &>> "$LOGFILE"
    systemctl disable ufw &>> "$LOGFILE"
fi
if systemctl list-unit-files | grep -q "^apparmor.service"; then
    systemctl stop apparmor &>> "$LOGFILE"
    systemctl disable apparmor &>> "$LOGFILE"
fi
if [ "$OS_FAMILY" = "rocky" ]; then
    if command -v setenforce &>/dev/null; then
        setenforce 0 &>> "$LOGFILE"
    fi
    if [ -f /etc/selinux/config ]; then
        sed -i 's/^SELINUX=.*/SELINUX=disabled/' /etc/selinux/config &>> "$LOGFILE"
    fi
    if systemctl list-unit-files | grep -q "^firewalld.service"; then
        firewall-cmd --add-service=http --add-service=https &>> "$LOGFILE"
        firewall-cmd --runtime-to-permanent &>> "$LOGFILE"
    fi
fi

if [ "$SKIP_DATABASE_INSTALL" -eq '0' ]; then
    # Install database server
    execute_cmd "$PKG_INSTALL $MYSQL_PACKAGE" "Installing database server"
    MYSQL_SERVICE=$(detect_mysql_service)
    systemctl enable "$MYSQL_SERVICE" --now &>> "$LOGFILE"

    # Configure database
    execute_cmd "systemctl start $MYSQL_SERVICE" "Starting database engine"

    if mysql --version 2>/dev/null | grep -qi mariadb; then
        # MariaDB: keep native root auth behavior; no temp-password bootstrap required.
        echo "MariaDB detected: skipping MySQL temporary-password bootstrap" &>> "$LOGFILE"
    else
        mysql_root_exec -e "SELECT 1;"
        check_cmd_status "Error verifying local MySQL root authentication"

        mysql_root_exec -e "INSTALL COMPONENT 'file://component_validate_password';"
        install_validate_status=$?
        if [ $install_validate_status -ne 0 ]; then
            echo "Skipping validate_password component (not available or already installed)" &>> "$LOGFILE"
        fi
    fi

    mysql_root_exec -e "DROP DATABASE IF EXISTS \`$DBNAME_IDENT\`; DROP USER IF EXISTS '$DBUSER_SQL'@'%';"
    check_cmd_status "Error resetting Pandora Open database"
    db_exists=$(mysql_root_query "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME='$DBNAME_SQL';")
    if [ -n "$db_exists" ]; then
        echo "Database $DBNAME still exists after drop." >> "$LOGFILE"
        echo -e "${red}Failed${reset}"
        echo "Error resetting Pandora Open database; $DBNAME still exists." >&2
        exit 1
    fi
    mysql_root_exec -e "CREATE DATABASE \`$DBNAME_IDENT\`"
    check_cmd_status "Error creating database $DBNAME. Is this a clean node? If you have a previous installation, please contact support."

    mysql_root_exec -e "CREATE USER IF NOT EXISTS '$DBUSER_SQL'@'%' IDENTIFIED BY '$DBPASS_SQL';"
    check_cmd_status "Error creating database user $DBUSER"

    if mysql --version 2>/dev/null | grep -qi mariadb; then
        mysql_root_exec -e "ALTER USER '$DBUSER_SQL'@'%' IDENTIFIED BY '$DBPASS_SQL';"
        check_cmd_status "Error setting password for database user $DBUSER"
    else
        mysql_root_exec -e "ALTER USER '$DBUSER_SQL'@'%' IDENTIFIED WITH mysql_native_password BY '$DBPASS_SQL'"
        check_cmd_status "Error setting mysql_native_password for database user $DBUSER"
    fi

    mysql_root_exec -e "GRANT ALL PRIVILEGES ON \`$DBNAME_IDENT\`.* TO '$DBUSER_SQL'@'%'"
    check_cmd_status "Error granting privileges to database user $DBUSER"

    # Rocky + MySQL 8: enforce mysql_native_password for Pandora Perl DBI compatibility
    # This avoids fallback to caching_sha2_password ("Authentication requires secure connection")
    if [ "$OS_FAMILY" = "rocky" ] && ! mysql --version 2>/dev/null | grep -qi mariadb; then
        mysql_root_exec -e "CREATE USER IF NOT EXISTS '$DBUSER_SQL'@'localhost' IDENTIFIED WITH mysql_native_password BY '$DBPASS_SQL';"
        check_cmd_status "Error creating localhost database user $DBUSER"

        mysql_root_exec -e "ALTER USER '$DBUSER_SQL'@'%' IDENTIFIED WITH mysql_native_password BY '$DBPASS_SQL';"
        check_cmd_status "Error enforcing mysql_native_password for database user $DBUSER@%"

        mysql_root_exec -e "ALTER USER '$DBUSER_SQL'@'localhost' IDENTIFIED WITH mysql_native_password BY '$DBPASS_SQL';"
        check_cmd_status "Error enforcing mysql_native_password for database user $DBUSER@localhost"

        mysql_root_exec -e "GRANT ALL PRIVILEGES ON \`$DBNAME_IDENT\`.* TO '$DBUSER_SQL'@'%'"
        check_cmd_status "Error granting privileges to database user $DBUSER@%"

        mysql_root_exec -e "GRANT ALL PRIVILEGES ON \`$DBNAME_IDENT\`.* TO '$DBUSER_SQL'@'localhost'"
        check_cmd_status "Error granting privileges to database user $DBUSER@localhost"

        plugin_remote=$(mysql_root_query "SELECT plugin FROM mysql.user WHERE user='$DBUSER_SQL' AND host='%' LIMIT 1;")
        plugin_local=$(mysql_root_query "SELECT plugin FROM mysql.user WHERE user='$DBUSER_SQL' AND host='localhost' LIMIT 1;")
        if [ "$plugin_remote" != "mysql_native_password" ] || [ "$plugin_local" != "mysql_native_password" ]; then
            echo "Unexpected auth plugin for $DBUSER. remote=$plugin_remote local=$plugin_local" &>> "$LOGFILE"
            echo -e "${red}Failed${reset}"
            echo "Error configuring MySQL auth plugin for Pandora user on Rocky."
            exit 1
        fi
    fi

    # Generate my.cnf
    cat > "$MYSQL_CNF" << EOF_DB
[mysqld]
datadir=/var/lib/mysql
user=mysql
character-set-server=utf8mb4
skip-character-set-client-handshake
# Disabling symbolic-links is recommended to prevent assorted security risks
symbolic-links=0
# MySQL optimizations for Pandora Open
# Please check the documentation in http://.io for better results

max_allowed_packet = 64M
innodb_buffer_pool_size = $POOL_SIZE
innodb_lock_wait_timeout = 90
innodb_file_per_table
innodb_flush_log_at_trx_commit = 0
innodb_flush_method = O_DIRECT
innodb_log_file_size = 64M
innodb_log_buffer_size = 16M
innodb_io_capacity = 300
thread_cache_size = 8
thread_stack    = 256K
max_connections = 100

key_buffer_size=4M
read_buffer_size=128K
read_rnd_buffer_size=128K
sort_buffer_size=128K
join_buffer_size=4M

skip-log-bin

sql_mode=""

log-error=/var/log/mysql/error.log
[mysqld_safe]
log-error=/var/log/mysqld.log
pid-file=/var/run/mysqld/mysqld.pid

EOF_DB

    execute_cmd "systemctl restart $MYSQL_SERVICE" "Configuring and restarting database engine"
else
    echo "Skipping local database installation and configuration (SKIP_DATABASE_INSTALL=1)" &>> "$LOGFILE"
fi

# Define packages with the specified or default directory
PANDORA_SERVER_PACKAGE="${PACKAGE_DIR}/pandoraopen-server-$PANDORA_VERSION.tar.gz"
PANDORA_CONSOLE_PACKAGE="${PACKAGE_DIR}/pandoraopen-console-$PANDORA_VERSION.tar.gz"
PANDORA_AGENT_PACKAGE="${PACKAGE_DIR}/pandoraopen-agent-$PANDORA_VERSION.tar.gz"

# Change to the work directory
cd $WORKDIR &>> "$LOGFILE"

# Install Pandora Open Console
echo -en "${cyan}Installing Pandora Open Console...${reset}"
tar xvzf "$PANDORA_CONSOLE_PACKAGE" &>> "$LOGFILE" && cp -Ra pandora_console /var/www/html/ &>> "$LOGFILE"
check_cmd_status "Error installing Pandora Open Console"
echo -e "${green}OK${reset}"
rm -f $PANDORA_CONSOLE/*.spec &>> "$LOGFILE"

# Install Pandora Open Server
echo -en "${cyan}Installing Pandora Open Server...${reset}"
useradd pandora  &>> "$LOGFILE"
tar xvfz "$PANDORA_SERVER_PACKAGE" &>> $LOGFILE && cd pandora_server && ./pandora_server_installer --install &>> $LOGFILE && cd $WORKDIR &>> $LOGFILE
check_cmd_status "Error installing Pandora Open Server"
echo -e "${green}OK${reset}"

# Install Pandora Open Agent
echo -en "${cyan}Installing Pandora Open Agent...${reset}"
tar xvzf "$PANDORA_AGENT_PACKAGE" &>> "$LOGFILE" && cd unix && \
bash ./pandora_agent_installer --install &>> $LOGFILE && \
cp -a tentacle_client /usr/local/bin/ &>> $LOGFILE && cd $WORKDIR
check_cmd_status "Error installing Pandora Open Agent"
echo -e "${green}OK${reset}"


# Configure services
if [ "$OS_FAMILY" = "debian" ]; then
    # Configure Apache and SSL
    if [ -f /etc/apache2/conf-enabled/timeout.conf ]; then
        sed -i '/^ProxyTimeout /d' /etc/apache2/conf-enabled/timeout.conf
    fi
    cat > /etc/apache2/conf-available/ssl-params.conf << EOF_PARAM
SSLCipherSuite EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH
    
    SSLProtocol All -SSLv2 -SSLv3 -TLSv1 -TLSv1.1
    
    SSLHonorCipherOrder On
    
    
    Header always set X-Frame-Options DENY
    
    Header always set X-Content-Type-Options nosniff
    
    # Requires Apache >= 2.4
    
    SSLCompression off
    
    SSLUseStapling on
    
    SSLStaplingCache "shmcb:logs/stapling-cache(150000)"
    
    
    # Requires Apache >= 2.4.11
    
    SSLSessionTickets Off
EOF_PARAM

    a2enmod ssl &>> "$LOGFILE"
    a2enmod headers &>> "$LOGFILE"
    a2enmod rewrite &>> "$LOGFILE" 
    a2enmod proxy_fcgi &>> "$LOGFILE"
    a2enmod setenvif &>> "$LOGFILE"
    a2enmod proxy &>> "$LOGFILE"
    a2enmod proxy_http &>> "$LOGFILE"
    if [ -f "/etc/apache2/conf-available/php${PHPVER}-fpm.conf" ]; then
        a2enconf "php${PHPVER}-fpm" &>> "$LOGFILE"
    else
        PHP_FPM_CONF=$(ls /etc/apache2/conf-available/php*-fpm.conf 2>/dev/null | head -1)
        if [ -n "$PHP_FPM_CONF" ]; then
            a2enconf "$(basename "$PHP_FPM_CONF" .conf)" &>> "$LOGFILE"
        fi
    fi
    a2enconf ssl-params &>> "$LOGFILE"
    a2ensite default-ssl &>> "$LOGFILE"
else
    execute_cmd "$PKG_INSTALL mod_ssl" "Installing mod_ssl"

    echo -en "${cyan}Ensuring Apache SSL certificate files exist... ${reset}"
    ensure_rocky_httpd_ssl_files
    check_cmd_status "Apache SSL certificate generation failed"
    echo -e "${green}OK${reset}"

    cat > /etc/httpd/conf.d/ssl-params.conf << EOF_PARAM
SSLCipherSuite EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH
SSLProtocol All -SSLv2 -SSLv3 -TLSv1 -TLSv1.1
SSLHonorCipherOrder On
Header always set X-Frame-Options DENY
Header always set X-Content-Type-Options nosniff
SSLCompression off
SSLUseStapling on
SSLStaplingCache "shmcb:/var/run/httpd/ssl_stapling_cache(150000)"
SSLSessionTickets Off
EOF_PARAM
fi

echo -en "${cyan}Validating Apache configuration... ${reset}"
apache_configtest
check_cmd_status "Apache configuration test failed"
echo -e "${green}OK${reset}"

execute_cmd "systemctl restart $APACHE_SERVICE" "Enable SSL module and restart Apache"

if [ "$SKIP_DATABASE_INSTALL" -eq '0' ]; then
    execute_cmd "systemctl enable $MYSQL_SERVICE --now" "Enabling Database service"
fi
execute_cmd "systemctl enable $APACHE_SERVICE --now" "Enabling Apache service"
if [ "$OS_FAMILY" = "debian" ]; then
    PHP_FPM_SERVICE=$(detect_php_fpm_service)
    execute_cmd "systemctl enable $PHP_FPM_SERVICE --now" "Enabling $PHP_FPM_SERVICE service"
else
    execute_cmd "systemctl enable php-fpm --now" "Enabling php-fpm service"
fi


if [ "$SKIP_DATABASE_INSTALL" -eq '0' ]; then
    # Populate the database
    echo -en "${cyan}Loading pandoradb.sql to $DBNAME database...${reset}"
    mysql_root_exec -e "DROP DATABASE IF EXISTS \`$DBNAME_IDENT\`; CREATE DATABASE \`$DBNAME_IDENT\`;"
    check_cmd_status "Error resetting database before schema import"
    mysql_root_exec -e "GRANT ALL PRIVILEGES ON \`$DBNAME_IDENT\`.* TO '$DBUSER_SQL'@'%'"
    check_cmd_status "Error re-granting privileges for database user $DBUSER"
    mysql_app_import "$DBNAME" "$PANDORA_CONSOLE/pandoradb.sql"
    check_cmd_status 'Error loading database schema'
    echo -e "${green}OK${reset}"

    echo -en "${cyan}Loading pandoradb_data.sql to $DBNAME database...${reset}"
    mysql_app_import "$DBNAME" "$PANDORA_CONSOLE/pandoradb_data.sql"
    check_cmd_status 'Error loading database schema data'
    echo -e "${green}OK${reset}"
else
    echo "Skipping local database schema deployment (SKIP_DATABASE_INSTALL=1)" &>> "$LOGFILE"
fi

# Configure the console
cat > $PANDORA_CONSOLE/include/config.php << EO_CONFIG_F
<?php
\$config["dbtype"] = "mysql";
\$config["dbname"]="$DBNAME";
\$config["dbuser"]="$DBUSER";
\$config["dbpass"]="$DBPASS";
\$config["dbhost"]="$DBHOST";
\$config["homedir"]="$PANDORA_CONSOLE";
\$config["homeurl"]="/pandora_console";
error_reporting(0);
\$ownDir = dirname(__FILE__) . '/';
include (\$ownDir . "config_process.php");
EO_CONFIG_F

# Enable AllowOverride
if [ "$OS_FAMILY" = "debian" ]; then
    cat > /etc/apache2/conf-enabled/pandora_security.conf << EO_CONFIG_F
ServerTokens Prod
<Directory "/var/www/html">
    Options FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
EO_CONFIG_F
else
    cat > /etc/httpd/conf.d/pandora_security.conf << EO_CONFIG_F
ServerTokens Prod
<Directory "/var/www/html">
    Options FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
EO_CONFIG_F
fi

# Fix console permissions
chmod 600 $PANDORA_CONSOLE/include/config.php &>> "$LOGFILE"
chown -R ${APACHE_USER}:${APACHE_USER} $PANDORA_CONSOLE &>> "$LOGFILE"
mv $PANDORA_CONSOLE/install.php $PANDORA_CONSOLE/install.done &>> "$LOGFILE"

# Configure PHP
if [ "$OS_FAMILY" = "debian" ]; then
    if [ -f "/etc/php/$PHPVER/fpm/php.ini" ]; then
    ln -sf /etc/php/$PHPVER/fpm/php.ini /etc/
    else
        PHP_FPM_INI=$(ls /etc/php/*/fpm/php.ini 2>/dev/null | head -1)
        if [ -n "$PHP_FPM_INI" ]; then
            ln -s "$PHP_FPM_INI" /etc/
        fi
    fi
    PHP_INI="/etc/php.ini"
else
    PHP_INI="/etc/php.ini"
fi
sed --follow-symlinks -i -e "s/^max_input_time.*/max_input_time = -1/g" "$PHP_INI"
sed --follow-symlinks -i -e "s/^max_execution_time.*/max_execution_time = 0/g" "$PHP_INI"
sed --follow-symlinks -i -e "s/^upload_max_filesize.*/upload_max_filesize = 800M/g" "$PHP_INI"
sed --follow-symlinks -i -e "s/^memory_limit.*/memory_limit = 800M/g" "$PHP_INI"
sed --follow-symlinks -i -e "s/.*post_max_size =.*/post_max_size = 800M/" "$PHP_INI"
sed --follow-symlinks -i -e "s/^disable_functions/;disable_functions/" "$PHP_INI"

# Configure timeouts
if [ "$OS_FAMILY" = "debian" ]; then
    echo 'TimeOut 900' > /etc/apache2/conf-enabled/timeout.conf
    a2enmod proxy &>> "$LOGFILE"
    a2enmod proxy_http &>> "$LOGFILE"
    if apache2ctl -M 2>/dev/null | grep -q 'proxy_module'; then
        echo 'ProxyTimeout 300' >> /etc/apache2/conf-enabled/timeout.conf
    else
        sed -i '/^ProxyTimeout /d' /etc/apache2/conf-enabled/timeout.conf
        echo "Proxy modules not loaded; skipping ProxyTimeout" &>> "$LOGFILE"
    fi
else
    echo 'TimeOut 900' > /etc/httpd/conf.d/timeout.conf
    if httpd -M 2>/dev/null | grep -q 'proxy_module'; then
        echo 'ProxyTimeout 300' >> /etc/httpd/conf.d/timeout.conf
    fi
fi

cat > /var/www/html/index.html << EOF_INDEX
<meta HTTP-EQUIV="REFRESH" content="0; url=/pandora_console/">
EOF_INDEX

execute_cmd "systemctl restart $APACHE_SERVICE" "Restarting Apache after configuration"
if [ "$OS_FAMILY" = "debian" ]; then
    PHP_FPM_SERVICE=$(detect_php_fpm_service)
    execute_cmd "systemctl restart $PHP_FPM_SERVICE" "Restarting $PHP_FPM_SERVICE after configuration"
else
    execute_cmd "systemctl restart php-fpm" "Restarting php-fpm after configuration"
fi

# Configure snmptrapd
cat > /etc/snmp/snmptrapd.conf << EOF
authCommunity log public
disableAuthorization yes
EOF

# Configure the Server
sed -i -e "s/^dbhost.*/dbhost $DBHOST/g" $PANDORA_SERVER_CONF
sed -i -e "s/^dbname.*/dbname $DBNAME/g" $PANDORA_SERVER_CONF
sed -i -e "s/^dbuser.*/dbuser $DBUSER/g" $PANDORA_SERVER_CONF
sed -i -e "s|^dbpass.*|dbpass $DBPASS|g" $PANDORA_SERVER_CONF
sed -i -e "s/^dbport.*/dbport $DBPORT/g" $PANDORA_SERVER_CONF

# Add group to pandora server conf
grep -q "group ${APACHE_USER}" $PANDORA_SERVER_CONF || \
cat >> $PANDORA_SERVER_CONF << EOF_G

# Add group ${APACHE_USER} to assign remote-config permissions correctly.
group ${APACHE_USER}
EOF_G

# Enable agent remote config
AGENT_CONF_PATH="$PANDORA_AGENT_CONF"
if [ ! -f "$AGENT_CONF_PATH" ]; then
    AGENT_CONF_PATH=$(ls /etc/pandora/pandora_agent*.conf 2>/dev/null | head -1)
fi
if [ -n "$AGENT_CONF_PATH" ] && [ -f "$AGENT_CONF_PATH" ]; then
    sed -i "s/^remote_config.*$/remote_config 1/g" "$AGENT_CONF_PATH"
else
    echo "Pandora agent config not found; skipping remote_config enable" &>> "$LOGFILE"
fi

# Kernel optimizations
if [ "$SKIP_KERNEL_OPTIMIZATIONS" -eq '0' ] ; then
cat >> /etc/sysctl.conf <<EO_KO
# Pandora Open Optimization

# default=5
net.ipv4.tcp_syn_retries = 3

# default=5
net.ipv4.tcp_synack_retries = 3

# default=1024
net.ipv4.tcp_max_syn_backlog = 65536

# default=124928
net.core.wmem_max = 8388608

# default=131071
net.core.rmem_max = 8388608

# default = 128
net.core.somaxconn = 1024

# default = 20480
net.core.optmem_max = 81920

EO_KO

   echo -en "${cyan}Applying kernel optimizations... ${reset}"
    sysctl --system &>> $LOGFILE
    if [ $? -ne 0 ]; then
        echo -e "${red}Failed${reset}"
        echo -e "${yellow}Your kernel could not be optimized. You may be running this script in a virtualized environment without access to kernel settings.${reset}"
        echo -e "${yellow}This system can be used for testing, but it is not recommended for production.${reset}"
    else
        echo -e "${green}OK${reset}"
    fi
fi

# Fix pandora_server.{log,error} permissions to allow the Console to read them.
chown pandora:${APACHE_USER} /var/log/pandora
chmod g+s /var/log/pandora

cat > /etc/logrotate.d/pandora_server <<EO_LR
/var/log/pandora/pandora_server.log
/var/log/pandora/web_socket.log
/var/log/pandora/pandora_server.error {
        su root ${APACHE_USER}
        weekly
        missingok
        size 300000
        rotate 3
        maxage 90
        compress
        notifempty
        copytruncate
        create 660 pandora ${APACHE_USER}
}

/var/log/pandora/pandora_snmptrap.log {
        su root ${APACHE_USER}
        weekly
        missingok
        size 500000
        rotate 1
        maxage 30
        notifempty
        copytruncate
        create 660 pandora ${APACHE_USER}
}

EO_LR

cat > /etc/logrotate.d/pandora_agent <<EO_LRA
/var/log/pandora/pandora_agent.log {
        su root ${APACHE_USER}
        weekly
        missingok
        size 300000
        rotate 3
        maxage 90
        compress
        notifempty
        copytruncate
}

EO_LRA

chmod 0644 /etc/logrotate.d/pandora_server
chmod 0644 /etc/logrotate.d/pandora_agent

# Enable Pandora service
execute_cmd "/etc/init.d/pandora_server start" "Starting Pandora Open Server"
systemctl enable pandora_server &>> "$LOGFILE"

# Start Tentacle server
execute_cmd "service tentacle_serverd start" "Starting Tentacle Server"
systemctl enable tentacle_serverd &>> "$LOGFILE"

# Enable Console cron
execute_cmd "echo \"* * * * * root wget -q -O - --no-check-certificate --load-cookies /tmp/cron-session-cookies --save-cookies /tmp/cron-session-cookies --keep-session-cookies http://127.0.0.1/pandora_console/cron.php >> $PANDORA_CONSOLE/log/cron.log\" >> /etc/crontab" "Enabling Pandora Open Console cron"

# Enable pandoradb cron
execute_cmd "echo 'Enabling pandoradb cron' >> $PANDORA_CONSOLE/log/cron.log" "Enabling Pandora Open pandoradb cron"
echo "@hourly         root    bash -c /etc/cron.hourly/pandora_db" >> /etc/crontab

# Configure the Agent
if [ -n "$AGENT_CONF_PATH" ] && [ -f "$AGENT_CONF_PATH" ]; then
    sed -i "s/^remote_config.*$/remote_config 1/g" "$AGENT_CONF_PATH" &>> "$LOGFILE"
else
    echo "Pandora agent config not found; skipping remote_config enable" &>> "$LOGFILE"
fi
if [ -x /etc/init.d/pandora_agent_daemon ]; then
    execute_cmd "/etc/init.d/pandora_agent_daemon restart" "Starting Pandora Open Agent"
    systemctl enable pandora_agent_daemon &>> "$LOGFILE"
else
    echo "Pandora agent init script not found; skipping agent start" &>> "$LOGFILE"
fi


# Fix PhantomJS path
sed --follow-symlinks -i -e "s/^openssl_conf = openssl_init/#openssl_conf = openssl_init/g" /etc/ssl/openssl.cnf &>> "$LOGFILE"

# Enable postfix
systemctl enable postfix --now &>> "$LOGFILE"

# Disable snmptrapd
systemctl disable --now snmptrapd &>> "$LOGFILE"
systemctl disable --now snmptrapd.socket &>> "$LOGFILE"

# Add legacy provider to OpenSSL
sed -i '/default = default_sect/a legacy = legacy_sect' /etc/ssl/openssl.cnf
sed -i 's/# activate = 1/activate = 1/' /etc/ssl/openssl.cnf
sed -i '/activate = 1/a [legacy_sect]\nactivate = 1' /etc/ssl/openssl.cnf

if [ "$SKIP_DATABASE_INSTALL" -eq '0' ]; then
    set_root_password_post_install
fi

# SSH banner
[ "$(curl -s ifconfig.me)" ] && ipplublic=$(curl -s ifconfig.me)

cat > /etc/issue.net << EOF_banner

Welcome to the Pandora Open appliance on ${os_name}
------------------------------------------
Go to http://$ipplublic/pandora_console to log in to the web console.
$(ip addr | grep -w "inet" | grep -v "127.0.0.1" | grep -v "172.17.0.1" | awk '{print $2}' | awk -F '/' '{print "Go to http://"$1"/pandora_console to log in to the web console."}')

You can find more information at http://.io.

EOF_banner

rm -f /etc/issue
ln -s /etc/issue.net /etc/issue

grep -qxF 'Banner /etc/issue.net' /etc/ssh/sshd_config || echo 'Banner /etc/issue.net' >> /etc/ssh/sshd_config
systemctl reload sshd &>> "$LOGFILE" || systemctl reload ssh &>> "$LOGFILE" || true

# Remove temporary files
execute_cmd "echo done" "Pandora Open installed"
cd "$HOME"
execute_cmd "rm -rf $WORKDIR" "Removing temporary files"

# Print final message
GREEN='\033[01;32m'
NONE='\033[0m'
printf " -> Go to Public ${green}http://"$ipplublic"/pandora_console${reset} to manage this server"
ip addr | grep -w "inet" | grep -v "127.0.0.1" | grep -v -e "172.1[0-9].0.1" | awk '{print $2}' | awk -v g=$GREEN -v n=$NONE -F '/' '{printf "\n -> Go to Local "g"http://"$1"/pandora_console"n" to manage this server \n -> Use these credentials to log in Pandora Console "g"[ User: admin / Password: pandora ]"n" \n"}'
