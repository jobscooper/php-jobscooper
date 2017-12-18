FROM python:2.7

ENV DEBIAN_FRONTEND=noninteractive

#######################################################
##
## Install and update the core package install toolsets
##
#######################################################

RUN apt-get update

RUN apt-get install -y \
    curl \
    wget \
    zip \
    ca-certificates

RUN apt-get install -y \
    apt-transport-https \
    apt-utils \
    sqlite3 \
    mysql-client \
    vim \
    sendmail \
    tzdata \
    openntpd


#######################################################
##
## Install pip
##
#######################################################
RUN which python
RUN echo PATH=$PATH

########################################################
##
## Install PHP5.6 Packages
##
#######################################################
RUN apt-get update && apt-get install -y \
    php5-cli \
    php5-dev \
    php-pear \
    php5-curl \
    php5-gd \
    php5-intl \
    php5-mcrypt \
    php5-xsl \
    php5-mysql \
    php5-sqlite \
    php5-http


#######################################################
##
## Set the timezone on the image
##
#######################################################
ENV TZ=America/Los_Angeles
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

#######################################################
##
## Install Docker exe so we can stop/start Selenium
##
#######################################################
RUN mkdir /etc/apk/
RUN echo "http://dl-3.alpinelinux.org/alpine/latest-stable/community/x86_64/" >> /etc/apk/repositories

RUN apt-get install -y \
    docker

RUN echo "chown -R dev:dev /var/run/docker.sock" >> ~/.bash_profile

#######################################################
##
## Install Composer
##
#######################################################

# Install Composer and make it available in the PATH
RUN curl https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

## Display version information.
RUN composer --version

########################################################
###
### Download the full nltk data set needed for python
###
########################################################
RUN pip install --no-cache-dir -v nltk
RUN python -m nltk.downloader -d /root/nltk_data all

#RUN curl -fsSLO https://get.docker.com/builds/Linux/x86_64/docker-17.03.1-ce.tgz33 && \
#tar --strip-components=1 -xvzf docker-17.03.1-ce.tgz -C /usr/local/bin
#

#######################################################
##
## Download & build the math extensions for SQLite
## that we need for geospatial queries
##
#######################################################
##
### Uncomment these lines if you are using SQLite
##
# RUN mkdir /opt/sqlite
# RUN mkdir /opt/sqlite/extensions
# RUN echo "Downloading and compiling SQLite3 math extensions..."
# RUN wget https://www.sqlite.org/contrib/download/extension-functions.c?get=25 -O /opt/sqlite/extensions/extension-functions.c
# RUN gcc -fPIC -lm -shared /opt/sqlite/extensions/extension-functions.c -o /opt/sqlite/extensions/libsqlitefunctions.so
# ADD ./Config/etc/30-pdo_sqlite_ext.ini /etc/php5/cli/conf.d/30-pdo_sqlite_ext.ini

########################################################
###
### Set up data volume for job output that will be
### mapped to the local hard drive of the actual PC
###
########################################################
VOLUME "/var/local/jobs_scooper"

########################################################
###
### Create the main source code directory structure on
### the image
###
########################################################
RUN mkdir /opt/jobs_scooper

########################################################
###
### Add the PHP composer configuration file into image
### and install the dependencies
###
########################################################
WORKDIR /opt/jobs_scooper
ADD ./composer.json /opt/jobs_scooper/
RUN composer install --no-interaction -vv

########################################################
###
### Install python dependencies
###
########################################################
ADD ./python/pyJobNormalizer/requirements.txt /opt/jobs_scooper/python/pyJobNormalizer/requirements.txt
RUN pip install --no-cache-dir -v -r /opt/jobs_scooper/python/pyJobNormalizer/requirements.txt


########################################################
###
### Add the full, remaining source code from the repo
### to the image
###
########################################################
RUN echo "Adding all source files from `pwd` to /opt/jobs_scooper"
ADD ./ /opt/jobs_scooper/

RUN echo "Verifying correct source installed..."
RUN ls -al /opt/jobs_scooper

########################################################
###
### Run the user's start_jobscooper.sh script in the 
### local shared volume for results and config data
###
########################################################
WORKDIR /var/local/jobs_scooper
CMD bash -C '/var/local/jobs_scooper/start_jobscooper.sh';'bash'