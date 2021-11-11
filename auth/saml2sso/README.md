# SAML2 SSO

## Moodle authentication using exists SimpleSAMLphp Service Provider

You'll need the following pre-requirement:

* A working SimpleSAMLphp Service Provider (SP) installation (https://simplesamlphp.org) _working means that the metadata from SP must be registered in Identity Provider (IdP). Can be found in /config/authsources.php_
* The absolute path for the SimpleSAMLphp installation on server (autodetected if the Apache enviroment variable is set)
* The authsource name from SP in which your users will authenticate against

You are strongly encouraged to use a [SimpleSAMLphp session storage](https://simplesamlphp.org/docs/stable/simplesamlphp-maintenance#section_2) other than the default phpsession.

There are other SAML plugins for Moodle and the panorama could be confusing.
Below are the main differences between this plugin, named internally as **auth_saml2sso**, and the others:

* [official Shibboleth plugin](https://docs.moodle.org/35/en/Shibboleth) - Requires a system-level configuration, uses a long-running process, easily protects resource at Apache level, cannot exploit PHP skill, hard to configure for servers hosting multiple Moodle if requirements of each site are different.
* [SAML Authentication (auth_saml)](https://moodle.org/plugins/auth_saml) - There is a new release of this plugin since mid of 2019. Handles enrollment based on attribute passed by the IdP.
* [SAML2 Single sign on (auth_saml2)](https://moodle.org/plugins/auth_saml2) - It's a complete solution for those that don't have a working SSP installation, but because it instantiate its own SP, for every single  Moodle virtual host that you activate, you must exchange the metadata with the IdP, replicate SSP config, update libraries, etc... It could be a good solution for a single Moodle site without specific requirement in authentication process, but it seems unsuitable for complex environments.
* [OneLogin SAML SSO (onelogin_saml)](https://github.com/onelogin/moodle-saml) - Based on OneLogin libraries, features similar to auth_saml2

The key for this plugin is that you can use your exists Service Provider (SP) without needed to exchange the metadata with the Identity Provider (IdP) for every new Moodle instances. _(for instances in the same host name)_

## The following options can be set in config:

* SimpleSAMLphp installation path
* SP source name - Generally default-sp in SimpleSAMLphp
* [Single Sign Off](#Single-Sign-Off) (Yes/No) - Should we sign off users from Moodle and IdP?
* Logout URL to redirect users after logout
* Username mapping - Which attribute from IdP should be used for username
* Username checking - Where to check if the username exists
* Auto create users - Allow create new users
* [Limit concurrent logins](#Limit-concurrent-logins) to 1 if configured as global setting
* Dual login (Yes/No) - Can login with manual accounts like admin
* User synchronization source (see below)
* Allow users to edit or not the profile
* Have an empty caption text for the button - To be able to use an image with text included instead.

To override the authentication and login directly in Moodle (ex.: using admin account), add the `saml=off` parameter in the URL (ex.: https://my.moodle/login/index.php?saml=off)

## Upgrade to 3.10

You cannot upgrade directly from Moodle < 3.4, because incompatible functions.
In the case, you must perform an intermedial upgrade step to a Moodle >= 3.4 and < 3.9 with a plugin version < 3.10.

The feature to break the full name from IdP into firstname and lastname has been removed because inconsistencies in results.
If you can replace it with a SimpleSAMLphp authproc filter: see below.

## Limit concurrent logins
According Moodle documentation, SSO-auth modules don't apply "limit concurrent logins" restriction.

https://tracker.moodle.org/browse/MDL-62753?jql=text%20~%20%22session%20kill%22

https://moodle.org/mod/forum/discuss.php?d=387784

Probably this is due to the mismatch between the Moodle session and the local SSO session.

Since SimpleSAMLphp API can interact with the local SSO session, this plugin supports the concurrent logins limit if it is set to 1.
This is a common scenario for exams, while limits > 1 have not clear purposes.

## Single Sign Off
SAML Single Sign Off (or better *SLO - Single LogOut*) is a tricky topic, Scott Cantor wrote a wide overview of the problem in the [Shibboleth wiki](https://shibboleth.atlassian.net/wiki/spaces/SHIB2/pages/2583494696/IdPEnableSLO).
Regarding this Moodle plugin:
* if Single Sign Off is disabled, logout from Moodle will leave SimpleSAMLphp local session untouched thus if the user click on SSO Login again she/he will log in Moodle without any password request. To avoid this confusing user experience, you could either set ForceAuthn to true in SP config or [limit concurrent logins](Limit-concurrent-logins) in Moodle
* if Single Sign Off is enabled, exiting from Moodle first destroyes the Moodle session, then starts with the IdP a SLO procedure that *should* kill any SP sessions in any application you logged-in; unfortunately, killing a SP session often **doesn't** destroy the application session protected by that SP, as you can see below
* either Single Sign Off is enabled or not, if a third application starts a SLO process, the local SP session will be destroyed according SP metadata but the Moodle session will remain alive because there is no entry point in Moodle auth plugin interface to check if a session is still valid nor can SimpleSAMLphp invoke a callback in the Moodle codebase
The only reliable SLO procedure remains to close the browser. 

## Split the full name from IdP
One of the distinctive feature of the first release of SAML2 SSO plugin was the ability to
break the full name from IdP into the first name and the last name.
It was related to the old version of Moodle auth base plugin and this feature will be removed in the next release.

Nowadays, adminting there are some IdPs still serving the full name and not 
the first and last name (such as givenName and sn in LDAP idiom) you can use a
SimpleSAMLphp *authproc*.

For example, in order to replicate the old behaviour if you receive
the full name in the attribute urn:oid:2.5.4.3 (e.g. from a Shibboleth IdP) you
can add to the authsource or to the IdP metadata:

```
'authproc' => array(
    array(
        'class' => 'core:PHP',
        'code' => '
// First name attribute
$attributes["givenName"][] = strstr($attributes["urn:oid:2.5.4.3"][0], " ", true)
        ? strtoupper(trim(strstr($attributes["urn:oid:2.5.4.3"][0], " ", true)))
        : strtoupper(trim($attributes["urn:oid:2.5.4.3"][0]));
// Last name attribute
$attributes["sn"][] = strstr($attributes["urn:oid:2.5.4.3"][0], " ")
        ? strtoupper(trim(strstr($attributes["urn:oid:2.5.4.3"][0], " ")))
        : strtoupper(trim($attributes["urn:oid:2.5.4.3"][0]));
    ',
),
```

Or, to emulate it in a compact way:

```
'authproc' => array(
    15 => array(
        'class' => 'core:AttributeAlter',
        'subject' => 'urn:oid:2.5.4.3',
        'pattern' => '/^([^ ]+)/',
        'target' => 'givenName',
        '%replace',
    ),
    16 => array(
        'class' => 'core:AttributeAlter',
        'subject' => 'urn:oid:2.5.4.3',
        'pattern' => '/([^\\s]+)$/',
        'target' => 'sn',
        '%replace',
    ),
```

And then mapping the attribute `givenName` and `sn` as usual.

See the [SimpleSAMLphp documentation](https://simplesamlphp.org/docs/stable/simplesamlphp-authproc) on Authproc.


## User synchronization

SAML-based authentication services couldn't provide a user list suitable for users synchronization. But, in scenarios with a single IdP within the same organization (no discovery nor federation) is common that the IdP uses LDAP or a SQL DB as authentication backend.

You can configure the LDAP or DB Moodle auth plugin in order to access to that backend, leaving the plugin itself disabled, and configure SAML2 SSO auth to obtain user list from it.

## How to migrate users from another plugin to SAML2SSO

A special page handles user migration from plugin based on external backend to
SAML2SSO. Users handled by the internal Moodle authentication cannot migrate
because no "authority" can guarantee match between Moodle username and SSO one. 
 
As described in the introduction, some plugins is not compatible with recent
Moodle releases. The migration feature take in account even users belongin to missing plugins.

## Using other authsources

SimpleSAMLphp is more than a SAML library: it is also an adapter layer
that can handle several auth sources in a uniform way.

Then SAML2SSO plugin can seamless authenticate against
[Facebook](https://simplesamlphp.org/docs/stable/authfacebook:authfacebook),
LinkedIn, Twitter, advanced LDAP and multi-LDAP sources, .htpasswd files, etc.

See [SimpleSAMLphp manual](https://simplesamlphp.org/docs/stable/) for more
documentation.
