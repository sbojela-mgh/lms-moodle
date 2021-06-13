## moodle-tool_downloaddata

#### Description
This is a plugin for Moodle to download courses and users by role to a CSV or Excel 2007 file. There is also a cli script to use the functionality from the terminal.

#### Installation
This plugin has been tested to work with Moodle 2.7 and newer. There are no guarantess it will work with earlier versions.
General installation procedures are those common for all Moodle plugins: https://docs.moodle.org/30/en/Installing_plugins.
First, you need to choose the branch corresponding to your Moodle version. Then you can choose between cloning the repository, downloading the zip file and extracting it or using the zip file for the plugin install interface accessible at *Administration > Site administration > Plugins > Install plugins*.

If you choose to clone the repository, then you need to clone it into MOODLE_ROOT_DIRECTORY/admin/tool/downloaddata by specifying the branch. For example, if you have Moodle 3.0 installed:

    git clone -b MOODLE_30_STABLE https://github.com/alexandru-elisei/moodle-tool_downloaddata.git MOODLE_ROOT_DIRECTORY/admin/tool/downloaddata

replacing MOODLE_ROOT_DIRECTORY with the actual Moodle installation root directory path. The zip file should be extracted to the same location.

If you decide to use the install plugin interface don't forget to *rename the folder inside the archive to downloaddata*.

Keep in mind that cloning the repository also creates a hidden .git directory.

#### Usage
The plugin creates two entries in Administration: one in *Administration > Site administration > Users > Accounts > Download users by role* for downloading users, and one in *Administration > Site administration > Courses > Download courses* for downloading courses. From there you can access the full functionality of the plugin.

Further information about how to use the web interface can be found in Moodle docs at [Download courses](https://docs.moodle.org/30/en/Download_courses) for downloading courses and at [Download users by role](https://docs.moodle.org/30/en/Download_users_by_role) for downloading users.

For the cli script, navigate to MOODLE_ROOT_DIRECTORY/admin/tool/downloaddata/cli and do the following:

    php downloaddata.php --data=users --fields=username,firstname,lastname --format=xls --roles=all > output.xls

You can see a list of all the available options by doing:

    php uploadusercli.php --help

There's also a configuration file MOODLE_ROOT_DIRECTORY/admin/tool/downloaddata/config.php to customize various aspects of the plugin.

#### Copyright
Copyright (C) Alexandru Elisei 2015 and beyond, All right reserved.

moodle-tool_downloaddata is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 3 of the license, or (at your option) any later version.

This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of the MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
