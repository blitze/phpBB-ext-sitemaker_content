#!/bin/bash
#
# This file is part of the phpBB Forum Software package.
#
# @copyright (c) phpBB Limited <https://www.phpbb.com>
# @license GNU General Public License, version 2 (GPL-2.0)
#
# For full copyright and license information, please see
# the docs/CREDITS.txt file.
#
set -e
set -x

BRANCH=$1

# Clone sitemaker
git clone --depth=1 "git://github.com/blitze/phpBB-ext-sitemaker.git" "phpBB3/ext/blitze/sitemaker" --branch=$BRANCH

cd phpBB3/ext/blitze/sitemaker
composer install --no-interaction --prefer-source

cd ../../../../phpBB3
