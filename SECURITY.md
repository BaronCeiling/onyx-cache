# Security policy

## Reporting

Open a private security advisory through the repository's **Security** tab. Public
issues are the wrong place for anything exploitable.

Include the plugin version, the PHP and WordPress versions, and the shortest
reproduction you have. Expect a first reply within 72 hours; a fix ships as a patch
release with the reporter credited unless they ask otherwise.

## Scope

In scope: the plugin PHP, the admin screens, the REST routes under `/wp-json/onyx-cache/v1/`,
the WP-CLI commands, and anything that writes to disk.

Out of scope: WordPress core and other plugins, host misconfiguration, and anything that
requires an already-compromised administrator account.

## What the plugin does not do

No `eval`, no includes from user input, no writes outside `wp-content/cache/onyx-cache` and
the uploads backup directory, no changes to `.htaccess` or `wp-config.php`.
