# docker build --no-cache --pull --build-arg GIT_REPO="GIT_REPO" -t ghcr.io/pandorafms/pandoraopen:el9 -f Dockerfile .
# docker push ghcr.io/pandorafms/pandoraopen:el9
# docker tag ghcr.io/pandorafms/pandoraopen:el9 ghcr.io/pandorafms/pandoraopen:latest
# docker push ghcr.io/pandorafms/pandoraopen:latest
FROM rockylinux/rockylinux:9

ARG PHPVER=8.2
ARG GIT_REPO="https://github.com/pandorafms/pandora-open.git"
ARG BRANCH=main
ENV PHPVER=$PHPVER
ENV IMAGE_NAME='pandorafms/pandoraopen-base:el9'
ENV PANDORA_CONSOLE=/var/www/html/pandora_console

WORKDIR /opt/pandora

RUN echo fastestmirror=1 >> /etc/dnf/dnf.conf

# Configuring extra repos
RUN dnf install -y tar \
    dnf-utils \
    https://dl.fedoraproject.org/pub/epel/epel-release-latest-9.noarch.rpm \
    https://dl.fedoraproject.org/pub/epel/epel-next-release-latest-9.noarch.rpm \
    https://rpms.remirepo.net/enterprise/remi-release-9.rpm \
    https://repo.percona.com/yum/percona-release-latest.noarch.rpm

RUN dnf config-manager --enable crb

RUN dnf module reset -y php && dnf module install -y php:remi-$PHPVER

# installing tools
RUN dnf install -y \
    vim \
    wget \
    bzip2 \
    git \
    git-lfs \
    passwd \
    cronie \
    htop \
    sudo \
    make

#installng console dependencies
RUN dnf install -y \
    php \
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
    whois

# install server deploy dependencies
RUN dnf install -y \
    perl \
    nmap \
    fping \
    net-tools \
    nfdump \
    expect \
    openssh-clients \
    postfix \
    unzip \
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
    chromium

# install ipam deploy dependencies
RUN dnf install -y \
    perl-NetAddr-IP \
    perl-Sys-Syslog \
    perl-DBI \
    perl-XML-Simple \
    perl-IO-Socket-INET6 \
    perl-XML-Twig

# Install utils
RUN dnf install -y supervisor mysql crontabs procps

# Cleanup
RUN dnf clean all

# Download repository
RUN git clone $GIT_REPO
RUN cd pandoraopen && git checkout $BRANCH
RUN cd pandoraopen && git pull

# Create packages
RUN cd pandoraopen && make

# Install console
RUN tar xvzf pandoraopen/build/pandoraopen-console-*.tar.gz && cp -Ra pandora_console /var/www/html/ && rm -rf pandora_console
RUN rm -f $PANDORA_CONSOLE/*.spec 

# Install Agent
# RUN tar xvzf pandoraopen/build/pandoraopen-agent-*.tar.gz && cd unix && ./pandora_agent_installer --install \
#     cp -a tentacle_client /usr/local/bin/ && cd -

# Install Server
RUN useradd pandora
RUN tar xvzf pandoraopen/build/pandoraopen-server-*.tar.gz && cd pandora_server && ./pandora_server_installer --install  && cd
RUN rm -rf pandora_server

EXPOSE 80 443 41121 162/udp 9995/udp

CMD ["sh", "/opt/pandora/pandoraopen/extra/Docker/src/entypoint.sh"]
