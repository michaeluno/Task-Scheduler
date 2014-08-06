=== Task Scheduler ===
Contributors: Michael Uno, miunosoft
Donate link: http://en.michaeluno.jp/donate
Tags: access, tool, background, backend, server, admin, task, management, system, event, scheduler, bulk, action, email, delete, post, cron, automation
Requires at least: 3.7
Tested up to: 3.9.1
Stable tag: 1.0.0b06
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Provides a task management system.

== Description ==

Do you have specific tasks which need to run at your desired time? Do you use WordPress as a proxy to fetch feed or generate data from external sources?

As WordPress has evolved into a phase of application platforms, a more enhanced task management system needed to emerge.

Currently, with WP Cron, if you register 1000 tasks to run immediately and one of them stalls, it affects all the other actions preventing them from being loaded at the scheduled time. Also, the scheduled tasks won't be triggered if there is no visitor on the site.

The goal of this plugin is to resolve such issues and become the perfect solution for WordPress powered back-end application servers to provide full-brown API functionalities.

<h4>What it does</h4>
- creates periodic background access to the site (optional). 
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

= Is it possible to send an email when a particular task completes? =
Yes. Create a task with the `Exit Code` occurrence type and the `Send Email` action. The `Exit Code` occurrence type lets you choose which task and what exit code should trigger an email to be sent.

= How can I know an action returns what exit code? =
The most built-in actions return `1` when they succeed and `0` on frailer. You can check what exit code will be returned by enabling the log. 

To enable the log, go to **Dashboard** -> **Task Scheduler** -> **Manage Tasks** and click on the **Edit** link of the task. Set a number in the **Max Count of Log Entries** option. `50` would be sufficient to check exit codes.

After the task runs, click on the **View** link of the task listing table of the task. The log page will open and it should tell what exit code the action returns.

= How can I create a module? =
The tutorials are in preparation. It requires a basic PHP skill and an understanding of object oriented programming. 

There are mainly two types of modules you can make, `action` and `occurrence`. Most of the time, you will want action modules.

If you are interested, open the `include/class/module/action` folder and you'll see some built-in action modules. If you open some of the files, you'll notice that each of them are very short. What it does is basically extend a base module class like `TaskScheduler_Action_Base` and insert code in the methods predefined by the base class.

If you are comfortable reading PHP code, it should not be hard to figure out. Give it a try. If you get a question, don't hesitate to post a question about it.

= Found a bug. Where can I report? =
Please use the [GitHub repository](https://github.com/michaeluno/Task-Scheduler) of this plugin.

== Screenshots ==

1. ***Task Listing Table***
2. ***Wizard***
3. ***Settings***


== Changelog ==

= 1.0.0b07 - 2014/08/06 =
- Added a new meta box in task edition page that includes the `Update` submit button, some time indications, and the switch option of `Enabled` or `Disabled`.
- Tweaked the mechanism of checking routines.
- Tweaked the Delete Posts action module not to insert a taxonomy query argument when the taxonomy slug is not selected.
- Fixed a bug that repeatable fields could not be properly updated in wizards.
- Fixed a bug that editing a disable task made the task not accessible from the task listing table.
- Fixed a bug that the same task could be triggered when simultaneous page loads that checks the scheduled actions are made at the exact the same time.
- Fixed a bug that the same task could be wedged in the queue of spawning tasks while another page load is spawning tasks.

= 1.0.0b06 - 2014/08/05 =
- Fixed a bug that the server heartbeat got resumed upon plugin activation even when it is disabled.
- Added a description in the setting page that appears when the server heartbeat is disabled.

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