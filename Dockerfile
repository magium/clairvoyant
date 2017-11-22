FROM magium/clairvoyant-chrome-php-7.0

USER root

RUN apt-get install -y php-xdebug
USER seluser

COPY . /magium/
