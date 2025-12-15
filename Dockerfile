FROM php:8.2-apache

# Copy all files into Apache web root
COPY . /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
