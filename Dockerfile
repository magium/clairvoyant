# This Dockerfile is used to test the MagiumEnvironmentFactory class
# It also often breaks other things too, so it's actually kind of useful
FROM magium/clairvoyant-chrome-php-7.0

USER root
RUN apt-get install -y php-xdebug

USER seluser

COPY . /magium/
