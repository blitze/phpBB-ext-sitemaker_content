# phpBB Sitemaker Content Extension

phpBB Sitemaker Content is an Extension for [phpBB 3.2](https://www.phpbb.com/)

[![Travis branch](https://img.shields.io/travis/blitze/phpBB-ext-sitemaker_content/develop.svg?style=flat)](https://travis-ci.org/blitze/phpBB-ext-sitemaker_content)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/blitze/phpBB-ext-sitemaker_content/develop.svg?style=flat)](https://scrutinizer-ci.com/g/blitze/phpBB-ext-sitemaker_content/?branch=develop)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/blitze/phpBB-ext-sitemaker_content/develop.svg?style=flat)](https://scrutinizer-ci.com/g/blitze/phpBB-ext-sitemaker_content/?branch=develop)
[![Maintainability](https://api.codeclimate.com/v1/badges/a9a8d4b2441ad10c9aad/maintainability)](https://codeclimate.com/github/blitze/phpBB-ext-sitemaker_content/maintainability)  
[![Latest Stable Version](https://poser.pugx.org/blitze/content/v/stable?format=flat)](https://www.phpbb.com/customise/db/extension/sitemaker_content/)
[![Latest Unstable Version](https://poser.pugx.org/blitze/content/v/unstable?format=flat)](https://packagist.org/packages/blitze/content)
[![License](https://poser.pugx.org/blitze/content/license?format=flat)](https://packagist.org/packages/blitze/content)

## Description

Create and manage content types for phpBB Sitemaker Extension

## Features

* Define input fields for each content type in ACP
* Optionally define how each field is displayed
* Manage settings for each field in ACP
* Manage view settings in ACP
* Create custom views in ACP
* Other extensions can provide new or extend or replace existing content views
* Other extensions can provide new or extend or replace existing fields
* Ability to mark fields as required
* Choose who can input a field: poster or moderator
* Force-require approval before publishing
* Use permission system to restrict access to a particular content type
* Uses phpBB forum to store content so they are searchable
* Adds minimum meta tags for social sharing

### Available Blocks
* Recent Content
* Archive
* Swipper (Slideshow)

### Available Content Views
* Blog
* Portal
* Tiles

### Available Content Fields
* Checkbox
* Color
* Datetime
* Hidden
* Image
* Location
* Number
* Radio
* Range
* Select
* Social Share
* Telephone
* Text
* Paragraph
* URL

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