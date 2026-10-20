FROM php:8.2-apache

# msmtp-mta provides /usr/sbin/sendmail, which PHP mail() uses to send
# through the Hostinger mailbox (configured at start-up from env variables)
RUN apt-get update \
    && apt-get install -y --no-install-recommends msmtp-mta ca-certificates \
    && rm -rf /var/lib/apt/lists/*

COPY docker/mail-entrypoint.sh /usr/local/bin/mail-entrypoint.sh
RUN chmod 755 /usr/local/bin/mail-entrypoint.sh

# Copy all application files to the Apache web server root directory
COPY . /var/www/html/

# Grant appropriate ownership permissions to the web server user
RUN rm -rf /var/www/html/docker \
    && chown -R www-data:www-data /var/www/html

# Expose HTTP port 80
EXPOSE 80

ENTRYPOINT ["mail-entrypoint.sh"]
CMD ["apache2-foreground"]
