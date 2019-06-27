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

EXTNAME=$1

# Dependent phpBB extensions end up inside of the extension so let's move them to the phpBB/ext folder
cp -fR phpBB/ext/$EXTNAME/ext phpBB
rm -fR phpBB/ext/$EXTNAME/ext
