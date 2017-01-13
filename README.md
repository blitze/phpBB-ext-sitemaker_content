# phpBB Sitemaker Content Extension

phpBB Sitemaker Content is an Extension for [phpBB 3.2](https://www.phpbb.com/)

[![Travis branch](https://img.shields.io/travis/blitze/phpBB-ext-sitemaker_content/develop.svg?style=flat)](https://travis-ci.org/blitze/phpBB-ext-sitemaker_content) [![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/blitze/phpBB-ext-sitemaker_content/develop.svg?style=flat)](https://scrutinizer-ci.com/g/blitze/phpBB-ext-sitemaker_content/?branch=develop) [![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/blitze/phpBB-ext-sitemaker_content/develop.svg?style=flat)](https://scrutinizer-ci.com/g/blitze/phpBB-ext-sitemaker_content/?branch=develop)

## Description

Create and manage content types for phpBB Sitemaker Extension

## Features

* Define input fields for each content type in ACP
* Optionally define how each field is displayed
* Use permission system to restrict access to a particular content type
* Uses phpBB forum to store content so they are searchable

## Installation

Clone into phpBB/ext/blitze/content:

    git clone https://github.com/blitze/phpBB-ext-sitemaker_content.git phpBB/ext/blitze/content

Go to "ACP" > "Customise" > "Extensions" and enable the "phpBB Sitemaker Content" extension.

## Collaborate

* Create a issue in the [tracker](https://github.com/blitze/phpBB-ext-sitemaker_content/issues)
* Note the restrictions for [branch names](https://wiki.phpbb.com/Git#Branch_Names) and [commit messages](https://wiki.phpbb.com/Git#Commit_Messages) are similar to phpBB3
* Submit a [pull-request](https://github.com/blitze/phpBB-ext-sitemaker_content/pulls)

## Testing

We use Travis-CI as a continuous integration server and phpunit for our unit testing. See more information on the [phpBB development wiki](https://wiki.phpbb.com/Unit_Tests).

## License

[GPLv2](license.txt)