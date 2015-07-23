#!/bin/bash

set -e
set -x

DB=$1
TRAVIS_PHP_VERSION=$2
EXTNAME=$3

if [ "$TRAVIS_PHP_VERSION" == "5.5" -a "$DB" == "mysqli" ]
then
    wget https://scrutinizer-ci.com/ocular.phar
    php ocular.phar code-coverage:upload --format=php-clover build/logs/clover.xml
fi