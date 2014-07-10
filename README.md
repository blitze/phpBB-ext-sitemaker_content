# phpBB Primetime Content Extension

phpBB Primetime Content is an Extension for [phpBB 3.1](https://www.phpbb.com/)

[![Build Status](https://travis-ci.org/blitze/primetime_content.svg?branch=develop)](https://travis-ci.org/blitze/primetime_content)

## Description

Create and manage content types for phpBB Primetime

## Features

* Define input fields for each content type in ACP
* Optionally define how each field is displayed
* Use permission system to restrict access to a particular content type
* Uses phpBB forum to store content so they are searchable

## Installation

Clone into phpBB/ext/primetime/primetime:

    git clone https://github.com/blitze/primetime_content.git phpBB/ext/primetime/content

Go to "ACP" > "Customise" > "Extensions" and enable the "phpBB Primetime" extension.

## Collaborate

* Create a issue in the [tracker](https://github.com/blitze/primetime_content/issues)
* Note the restrictions for [branch names](https://wiki.phpbb.com/Git#Branch_Names) and [commit messages](https://wiki.phpbb.com/Git#Commit_Messages) are similar to phpBB3
* Submit a [pull-request](https://github.com/blitze/primetime_content/pulls)

## Testing

We use Travis-CI as a continuous integration server and phpunit for our unit testing. See more information on the [phpBB development wiki](https://wiki.phpbb.com/Unit_Tests).

## License

[GPLv2](license.txt)