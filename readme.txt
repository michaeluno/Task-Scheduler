=== Task Scheduler ===
Contributors: Michael Uno, miunosoft
Donate link: http://en.michaeluno.jp/donate
Tags: access, tool, background, backend, server, admin, task, management, system, event, scheduler, bulk, action, email, delete, post, cron, automation
Requires at least: 3.7
Tested up to: 3.9.1
Stable tag: 1.0.0b03
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Provides a task management system.

== Description ==

Do you have specific tasks which need to run at your desired time? Do you use WordPress as a proxy to fetch feed or generate data from external sources?

As WordPress has evolved into a phase of application platforms, a more enhanced task management system needed to emerge.

Currently, with WP Cron, if you register 1000 tasks to run immediately and one of them stalls, it affects all the other actions preventing them from being loaded at the scheduled time. Also, the scheduled tasks won't be triggered if there is no visitor on the site.

The goal of this plugin is to resolve such issues and become the perfect solution for WordPress powered back-end application servers to provide full-brown API functionalities.

<h4>What it does</h4>
- creates periodic background access to the site.
- triggers tasks registered by the site owner at desired time.

<h4>Built-in Actions</h4>
- <strong>Delete Posts</strong> - Performs bulk deletion of posts based on the post type, post statuses, taxonomy, and taxonomy terms.
- <strong>Send Email</strong> - Sends email to specified email addresses.

<h4>Extensible</h4>
This is designed to be fully extensible and developers can add custom modules including actions and occurrence types.

Some of the possibilities of custom modules include bulk post status change (post expiration), compress files in a certain directory and send it as an email attachment, clean up transients etc. 

If you need a custom module, let us know it!

== Installation ==

= Install = 

1. Upload **`task-scheduler.php`** and other files compressed in the zip folder to the **`/wp-content/plugins/`** directory.,
2. Activate the plugin through the `Plugins` menu in WordPress.

= How to Use =  
1. Define a `Task` via **Dashboard** -> **Task Scheduler** -> **Add New Task**
2. In the task listing table, toggle on and off.

== Other Notes ==

== Frequently Asked Questions ==

= Why Do I need this? =
This is mostly for site admins who need total control over the server behavior. If you use WordPress just to publish articles, you won't need this.

= Is it possible to trigger actions while disabling the server heartbeat? =
Yes. In that case, you need to set up your own Cron job that accesses the site with the `task_scheduler_checking_actions` query string in the request url.

e.g.
`/usr/local/bin/curl --silent http://your-site/?task_scheduler_checking_actions=1`

`/usr/local/bin/wget http://your-site/?task_scheduler_checking_actions=1`

= Found a bug. Where can I report? =
Please use the [GitHub repository](https://github.com/michaeluno/Task-Scheduler) of this plugin.

== Screenshots ==

1. ***Task Listing Table***
2. ***Wizard***
3. ***Settings***


== Changelog ==

= 1.0.0b05 - 2014/08/03 =
- Made it possible to trigger actions without the server heartbeat.

= 1.0.0b04 - 2014/08/02 =
- Fixed an issue that multiple server heartbeat instances could run. 

= 1.0.0b03 - 2014/08/01 =
- Changed it to display module description in the form field.
- Fixed an issue that the `Change` button did not appear when the `Debug` action is selected.
- Optimized the server heartbeat.

= 1.0.0b02 - 2014/07/31 =
- Initial release.