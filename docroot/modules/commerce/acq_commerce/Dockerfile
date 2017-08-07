FROM drupal

RUN apt-get -q update \
  && apt-get -y install unzip \
    git \
    vim

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer    

WORKDIR /var/www/html

RUN composer require drupal/panelizer:4.* \
  drupal/key_value_field \
  drupal/workbench_moderation \
  drupal/inline_entity_form \
  drupal/address \
  acquia/http-hmac-php \
  drupal/simple_oauth \
  drush/drush \
  drupal/devel

RUN chown -R www-data:root /var/www/html/modules
