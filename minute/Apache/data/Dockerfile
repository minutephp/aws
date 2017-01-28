ENV APACHE_RUN_USER             www-data
ENV APACHE_RUN_GROUP            www-data
ENV APACHE_LOG_DIR              /var/log/apache2
ENV APACHE_LOCK_DIR             /var/lock/apache2
ENV APACHE_PID_FILE             /var/run/apache2.pid

RUN rm -rf /etc/apache2/sites-available/*
RUN rm -rf /etc/apache2/sites-enabled/*
RUN rm -rf /var/www

ADD apache.conf     /etc/apache2/sites-enabled/default.conf
ADD minute.ini      /etc/php/7.0/apache2/conf.d/minute.ini
ADD minute.ini      /etc/php/7.0/cli/conf.d/minute.ini


