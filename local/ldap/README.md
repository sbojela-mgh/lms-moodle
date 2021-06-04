LDAP syncing scripts
=====================

[![Build Status](https://travis-ci.com/LafColITS/moodle-local_ldap.svg?branch=main)](https://travis-ci.com/LafColITS/moodle-local_ldap)

This plugin synchronizes Moodle cohorts against an LDAP directory using either group memberships or attribute values. This is a continuation of Patrick Pollet's [local_ldap](https://github.com/patrickpollet/moodle_local_ldap) plugin, which in turn was inspired by [MDL-25011](https://tracker.moodle.org/browse/MDL-25011) and [MDL-25054](https://tracker.moodle.org/browse/MDL-25054).

Requirements
------------
- Moodle 3.7 (build 2019052000 or later)
- OpenLDAP or Active Directory

Installation
------------
Copy the ldap folder into your /local directory and visit your Admin Notification page to complete the installation. You must have either the CAS or LDAP authentication method enabled.

Configuration
-------------
Depending on your environment the plugin may work with default options. Configuration settings include the group class (`groupOfNames` by default) and whether to automatically import all found LDAP groups as cohorts. By default this setting is disabled.

Usage
-----
Previous versions of this plugin used a CLI script. This is deprecated in favor of two [scheduled tasks](https://docs.moodle.org/31/en/Scheduled_tasks), one for syncing by group and another for syncing by attribute. Both are configured to run hourly and are disabled by default.

Testing
-------
The code is tested against OpenLDAP on Travis CI. If you have a local Active Directory environment you may run the tests against it. See [PHPUnit#LDAP](https://docs.moodle.org/dev/PHPUnit#LDAP) for more information. You will need to set an additional constant, `TEST_AUTH_LDAP_USER_TYPE`, to `ad`.

Author
-----
- Charles Fulton (fultonc@lafayette.edu)
- Patrick Pollet
