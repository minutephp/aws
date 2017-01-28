
ENV DEBIAN_FRONTEND             noninteractive
ENV LANG                        C
ENV TERM                        xterm

RUN apt-get update -y && apt-get upgrade -y && apt-get install -y \
    apache2 \
    supervisor \
    libapache2-modsecurity \
    libapache2-mod-security2 \
    libapache2-mod-xforward \
    libapache2-mod-webauth \
    libapache2-mod-upload-progress \
    libapache2-mod-nss \
    libapache2-mod-mapcache \
    libapache-gallery-perl \
    libapache2-mod-evasive \
    php \
    php-zip \
    php-xml \
    php-xajax \
    php-validate \
    php-sybase \
    php-soap \
    php-oauth \
    php-memcache \
    php-mcrypt \
    php-mbstring \
    php-markdown \
    php-json \
    php-imagick \
    php-image-text \
    php-http \
    php-gnupg \
    php-gettext \
    php-geoip \
    php-fshl \
    php-fpm \
    php-email-validator \
    php-date \
    php-cache \
    php-bcmath \
    php-auth \
    php-tidy \
    php-sqlite3 \
    php-pspell \
    php-pear \
    php-odbc \
    php-mysql \
    php-gmp \
    php-gd \
    php-curl \
    php-common \
    php-cgi \
    libapache2-mod-php \
    libapache2-mod-php \
    memcached \
    php7.0 \
    php7.0-curl \
    php7.0-gd \
    php7.0-gmp \
    php7.0-json \
    php7.0-mysql \
    php7.0-odbc \
    php7.0-opcache \
    php7.0-pspell \
    php7.0-readline \
    php7.0-recode \
    php7.0-sqlite3 \
    php7.0-tidy \
    php7.0-xml \
    php7.0-xmlrpc \
    php7.0-bcmath \
    php7.0-enchant \
    php7.0-fpm \
    php7.0-mbstring \
    php7.0-mcrypt \
    php7.0-soap \
    php7.0-intl \
    php7.0-sybase \
    php7.0-zip \
    php7.0-cgi \
    php7.0-common \
    libapache2-mod-php7.0 \
    composer \
    git \
    curl \
    mysql-client \
    mysql-utilities \
    nodejs-legacy \
    npm \
    mlocate \
    vim \
    wget \
    libfreetype6 \
    libfontconfig \
    bzip2 \
    zlib1g-dev \
    libz-dev \
    libmemcached-dev \
    libcurl4-gnutls-dev \
    libpng12-dev \
    dos2unix \
    lynx

ENV DEPLOYED                    EC2
ENV COMPOSER_HOME               /var/www/tmp
ENV COMPOSER_DISCARD_CHANGES    true
ENV MEMCACHED                   memcache://localhost:11211
ENV TZ                          UTC

RUN npm install -g bower
RUN npm install -g typings

RUN a2enmod rewrite headers expires evasive
RUN mkdir -p /var/lock/apache2 /var/run/apache2 /var/run/sshd /var/log/supervisor
RUN mkdir /var/log/mod_evasive && chown www-data:www-data /var/log/mod_evasive

COPY evasive.conf /etc/apache2/mods-available/evasive.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN service memcached start
RUN echo colorscheme elflord >> /etc/vim/vimrc


