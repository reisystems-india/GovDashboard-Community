# [GovDashboard](http://govdashboard.com)

GovDashboard, [REI Systems](http://reisystems.com) (REI) leading solution for visualizing data analytics, is a Software as a Service (SaaS) or an on-premise installation that enables data driven decision-making. Built on the latest open source technology, GovDashboard unlocks and organizes data to generate compelling visuals and analytics. With its quick-launch capability, built-in accelerators, and highly customizable interface, GovDashboard is easy to use and allows you to identify trends, manage performance, make better decisions, and inform your world.

The technology behind GovDashboard is based on REI's groundbreaking work supporting Government CIO initiatives for the Office of Management and Budget (OMB) in developing investment review and performance management dashboard solutions. REI is an award-winning pioneer in this government-wide initiative, helping OMB improve and automate the reporting and analysis of spending and program data since in 2005. For GovDashboard, REI has used the same cutting-edge open source technology and many of the same search and data visualization features, so that you can load your own data, present it to your audience, and use it to **_Answer Today's Questions, Today._**

## Table of contents

* [Installation Guide](#installation-guide)
* [Documentation](#documentation)
* [Contributing](#contributing)
* [Versioning](#versioning)
* [Copyright and license](#copyright-and-license)

## Installation Guide
These instructions will guide you through the process of setting up a
GovDashboard installation onto your webserver.

### Prerequisites

Before continuing with this guide, make sure you have all of the following system requirements:

* Apache, Nginx
* MySQL 5.5.44 or higher
* PHP 5.4 or higher

### Preparing the Code

GovDashboard is a series of modules built on top of the Drupal CMS and utilizes the standard Drupal installation process. 

* Create a configuration file by copying the example, named default.settings.php in the webapp/sites/default
directory. The copied configuration file must be named settings.php
* Give the web server write privileges to the settings.php configuration file.
* So that the files directory can be created automatically, give the web server write privileges to the
sites/default directory.
* You can read more about installing a Drupal installation at their [site](https://www.drupal.org/documentation/install).

### Creating the Database

GovDashboard requires a MySQL database using utf8_unicode_ci collation and utf8 charset.

### Installing the site

Once the code has been prepared and the database been created, you can begin the installation process for the website.

1. You can run the installation script by entering the base URL of your site and adding install.php at the end of it. (http://www.example.com/install.php)
2. GovDashboard includes custom Drupal installation profile called 'GovDash' that will enable all the modules required by the system. This profile needs to be selected and used in the installation process to ensure everything works correctly.
3. You will then be asked to input information for the MySQL that you set up earlier.
4. The 'GovDash' installation profile will then begin to execute and install the necessary modules as well as enable them.
5. Once the installation is finished, you will be asked to enter information about the site as well as the first user.

When this is complete, you have just installed your GovDashboard application!

## Copyright and license

All GovDashboard code is Copyright 2015 by REI Systems, Inc.

GovDashboard is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.

GovDashboard is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program as the file [LICENSE.md](LICENSE.md); if not, please see http://www.gnu.org/licenses/.

GovDashboard is a registered trademark of REI Systems, Inc.

GovDashboard includes works under other copyright notices and distributed according to the terms of the GNU General Public License or a compatible license.