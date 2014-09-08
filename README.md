ACME: Coffee Management Evolved
===============================

A set of scripts to help organise a coffee and doughnut rota.

Features
========

* LDAP authentication
* Periodical automatic payments of arbitrary amounts among users
* Manual payments made by coffee administrator
* Automatic email reminders sent to rota users
* Administrator functionality to add users to the rota, add new users, make custom transactions and set user payment levels
* Ability for multiple coffee administrators to exist

Requirements
============

(The versions below are guesses - just use reasonably up-to-date software and you should be ok)

* PHP >= 5.2
  * PEAR::Calendar (http://pear.php.net/package/Calendar/)
* MySQL >= 5.0
* Some sort of scheduled task manager (e.g. cron)

Bundled Software
================

ACME uses a hacked version of raintpl for templating and a PEAR::Calendar for working out dates.

A modified version of raintpl (http://www.raintpl.com/) is included as part of the software so doesn't need to be installed separately.

PEAR::Calendar is licenced under BSD which is incompatible with GPL. Therefore, just install PEAR::Calendar yourself and this software should be able to use it as long as it's in the include path.

Notes
=====

ACME was very much written to fit the caffeinated requirements at my organisation. It will require a bit of modification to work for another organisation, particularly regarding the emails that are sent by the cron jobs.

The project also includes a bunch of easter eggs, mainly added as jokes. The title of the website, for instance, changes periodically (the titles can be defined in the list in include.php - titles you wish to occur more frequently should be repeated).

Warnings
========

ACME is a bad example of how to write software and organise a project. There are few comments, and I've not used the cool, newer features of PHP like PDO to connect to the database. Users are often not shown meaningful error messages when something goes wrong (that being said, ~40 users have found very few problems with it over the past 18 months of use since I wrote it). It was almost entirely written over a rainy weekend, so use it at your own risk, and don't put this on a server where you host important websites or databases. There's a possibility I've left open an XSS attack vector by mistake.

Installation
============

Now that you've read the above notes and warnings, here's how to get the thing working:

1. Create a database in MySQL which the server can access
2. Import the database schema in database/schema.sql (e.g. with phpMyAdmin)
3. Edit config.php in web/config.php, adding appropriate username, password, server, etc. You need to specify an LDAP host too, otherwise the script can't synchronise users between LDAP and ACME.
4. Upload contents of web folder to your server in a path of your choosing
5. Make the cache folder writeable by the PHP user
6. Upload the contents of the cron folder to your server in a place that is NOT web accessible
7. Modify files in cron folder appropriately (see Scheduled Jobs section)
8. Setup two scheduled jobs to run each of the two scripts in the cron folder at the interval you choose (see Scheduled Jobs section)

Scheduled Jobs
==============

To set up emails and payments, you need to have your server periodically run the scheduled jobs in the cron folder. One script, coffeemail.php, sends emails to users on coffee and doughnut duties as and when required. The other, processpayments.php, subtracts the amount specified for each user in their account from their account, so they then owe money. Running this once a week with a weekly payment means each user's account is billed for their weekly coffee consumption.

You need to make a few changes:

1. Modify line 3 in both files to point to config.php in the web directory
2. Modify the email headers (lines 39 to 41 and 112 to 114) to make the email come from an appropriate reply address (e.g. the coffee administrator's email)
3. (Optional) modify each email (coffee and doughnut)'s contents starting on lines 37 and 110
4. Set up scheduled job to run coffeemail.php frequently, e.g. daily, to send reminder emails
5. Set up scheduled job to run processpayments.php at the interval you wish to bill users, e.g. weekly

Example crontab entry for coffeemail.php:

```# Coffee/doughnut reminder emails
0 3 * * * php /path/to/coffeemail.php >> coffeemail.log```

This runs daily at 3am (server time) and logs everything the script spits out to coffeemail.log.

Example crontab entry for processpayments.php:

```# Coffee/doughnut transactions
0 4 * * 0 php /path/to/processpayments.php >> processpayments.log```

This runs once a week (Sunday) and logs to processpayments.log.

Usage
=====

It's all pretty self-explanatory, I hope. The administrator gets special functionality to add/edit/delete users and set coffee/doughnut rotas and payments. Normal users can just see the coffee and doughnut calendar.

Finishing Notes
===============

I apologise for the long list of instructions. I hope you can put work in once to get this running then never touch it again. It's the sort of software that exists as a tongue-in-cheek, overly geeky way of solving a not-so-much-of-a problem.

Sean Leavey
https://github.com/SeanDS/