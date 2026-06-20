FROM php:8.2-apache

# Copy all application files to the Apache web server root directory
COPY . /var/www/html/

# Grant appropriate ownership permissions to the web server user
RUN chown -R www-data:www-data /var/www/html

# Expose HTTP port 80
EXPOSE 80
