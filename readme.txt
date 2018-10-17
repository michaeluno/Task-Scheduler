=== Task Scheduler ===
Contributors:       Michael Uno, miunosoft
Donate link:        http://en.michaeluno.jp/donate
Requires at least:  3.7
Tested up to:       4.9.8
Stable tag:         1.4.7
License:            GPLv2 or later
License URI:        http://www.gnu.org/licenses/gpl-2.0.html
Tags:               tool, utility, background, backend, task, system, event, scheduler, email, delete posts, cron, automation, routine

Provides a task management system.

== Description ==

<h4>Handle Massive Number of Actions</h4>
Do you have specific tasks which need to run at your desired time? Do you use WordPress as a proxy to generate data from external sources? As WordPress has evolved into a phase of application platforms, a more enhanced task management system needed to emerge.

Currently, with WP Cron, if you register a large number of actions, for example, 1000 tasks to run immediately and one of them stalls, it affects all the other actions preventing them from being loaded at the scheduled time. Also, the scheduled tasks won't be triggered if there is no visitor on the site. The goal of this plugin is to resolve such issues and become the perfect solution for WordPress powered back-end application servers to provide full-brown API functionalities.

<h4>What it does</h4>
- (optional) creates periodic background access to the site. 
- triggers tasks registered by the site owner at desired time or interval.

<h4>Built-in Actions</h4>
- <strong>Delete Posts</strong> - performs bulk deletion of posts based on the post type, post statuses, taxonomy, and taxonomy terms.
- <strong>Send Email</strong> - sends email to specified email addresses.
- <strong>Clean Transients</strong> - deletes expired transients (caches).
- <strong>Check Web Sites</strong> - accesses specified web pages and checks certain keywords.
- <strong>Run PHP Scripts</strong> - runs PHP scripts of your choosing.

<h4>Custom Action Modules</h4>
- <strong>[Auto Post](https://wordpress.org/plugins/auto-post/)</strong> - creates posts automatically.

<h4>Extensible</h4>
This is designed to be fully extensible and developers can add custom modules including actions and occurrence types.

== Installation ==

= Install = 

1. Upload **`task-scheduler.php`** and other files compressed in the zip folder to the **`/wp-content/plugins/`** directory.,
2. Activate the plugin through the `Plugins` menu in WordPress.

= How to Use =  
1. Define a `Task` via **Dashboard** -> **Task Scheduler** -> **Add New Task**
2. In the task listing table, toggle on and off.

== Other Notes ==

<h4>Create a Custom Action</h4>
You can run your custom action with Task Scheduler and run it at scheduled times, once a day, with a fixed interval, or whatever you set with the plugin.

Place the code that includes the module in your plugin or `functions.php` of the activated theme.

**1.** Decide your action slug which also serves as a WordPress _filter_ hook.

Say, you pick `my_custom_action` as an action name.

**2.** Use the `add_filter()` WordPress core function to hook into the action.

`
/**
 * Called when the Task Scheduler plugin gets loaded.
 */
function doMyCustomAction( $isExitCode, $oRoutine ) {
    
    /**
     * Do you stuff here.
     */
    TaskScheduler_Debug::log( $oRoutine->getMeta() );
    return 1;
    
}
/**
 * Set the 'my_custom_action' custom action slug in the Select Action screen
 * via Dashboard -> Task Scheduler -> Add New Task.
 */
add_filter( 'my_custom_action', 'doMyCustomAction', 10, 2 );
`

Please note that we use `add_filter()` not `add_action()` in order to return an exit code. 

Return `1` if the task completes and `0` when there is a problem. You can pass any value except `null`.

**3.** Go to **Dashboard** -> **Task Scheduler** -> **Add New Task**. Proceed with the wizard and when you get the **Select Action** screen after setting up the occurrence, type **my_custom_action**, the one you defined in the above step.
  
The action slug set in the field will be triggered at the scheduled time.

It will be easier for you to modify an [existent code](https://gist.github.com/michaeluno/5819636448947e7ab733). You can download the zip file and install it on your site.

<h4>Create a Custom Action Module</h4>
If you want your action to be listed in the **Select Action** screen, you need to create an action module. 

To create an action module, you need to define a class by extending a base class that Task Scheduler prepares for you.

**1.** Define your custom action module class by extending the TaskScheduler_Action_Base class. 

`
class TaskScheduler_SampleActionModule extends TaskScheduler_Action_Base {
        
    /**
     * The user constructor.
     * 
     * This method is automatically called at the end of the class constructor.
     */
    public function construct() {
        
        // Debug 
        // TaskScheduler_Debug::log(  get_object_vars( $this ) );
        
    }

    /**
     * Returns the readable label of this action.
     * 
     * This will be called when displaying the action in an pull-down select option, task listing table, or notification email message.
     */
    public function getLabel( $sLabel ) {         
        return __( 'Sample Action Module', 'task-scheduler-sample-action-module' );
    }
    
    /**
     * Returns the description of the module.
     */
    public function getDescription( $sDescription ) {
        return __( 'This is a sample action module.', 'task-scheduler-sample-action-module' );
    }    
    
    /**
     * Defines the behaviour of the task action.
     *  
     */
    public function doAction( $isExitCode, $oRoutine ) {
        
        /**
         * Write your own code here! Delete the below log method. 
         * 
         * Good luck!
         */
        TaskScheduler_Debug::log( $oRoutine->getMeta() );
        
        // Exit code.
        return 1;
        
    }
            
}
`

In the `doAction()` method of the above class, define the behaviour of your action what it does. The second parameter receives a routine object. The object has a public method named `getMeta()` which returns the associated arguments. 

**2.** Use the `task_scheduler_action_after_loading_plugin` action hook to register your action module.

To register your action module, just instantiate the class you defined.

`
function loadTaskSchedulerSampleActionModule() {
    
    // Register a custom action module.
    include( dirname( __FILE__ ) . '/module/TaskScheduler_SampleActionModule.php' );
    new TaskScheduler_SampleActionModule;
    
}
add_action( 'task_scheduler_action_after_loading_plugin', 'loadTaskSchedulerSampleActionModule' );
`

**3.** Go to **Dashboard** -> **Task Scheduler** -> **Add New Task**. Proceed the wizard and when you get the **Select Action** screen, choose your action.

You can set your custom arguments in the **Argument (optional)** field if necessary.

The set values will be stored in the argument element of the array returned by the `getMeta()` public method of the routine object.

It will be easier for you to modify an existent module. Get an example action module which comes as a plugin from [this page](https://github.com/michaeluno/task-scheduler-sample-action-module). Download and activate it on your test site. Then modify the code, especially the `doAction()` method which defines the behavior of the action.

<h4>Terminologies</h4>

- **Task** - a rule which defines what kind of action routine to be performed at a specified time.
- **Routine** - a main action routine created by a task. Depending on the action, it creates an action thread to divide its routine.
- **Thread** - a divided action sub-sequential routine created by a routine. For example, The email action creates threads and sends emails per thread instead of sending them all in one routine to avoid exceeding the PHP's maximum execution time.

== Frequently Asked Questions ==

= Who needs this? =
This is mostly for site admins who need total control over the server behavior. If you use WordPress just to publish articles, you won't need this.

= Is it possible to trigger actions while disabling the server heartbeat? =
Yes. In that case, you need to set up your own Cron job that accesses the site with the `task_scheduler_checking_actions` query string in the request url.

e.g.
`/usr/local/bin/curl --silent http://your-site/?task_scheduler_checking_actions=1`

`/usr/local/bin/wget http://your-site/?task_scheduler_checking_actions=1`

= Is it possible to send an email when a particular task completes? =
Yes. Create a task with the `Exit Code` occurrence type and the `Send Email` action. The `Exit Code` occurrence type lets you choose which task and what exit code should trigger an email to be sent.

= Is it possible to execute a PHP script? = 
The `PHP Script` action module lets you run PHP scripts located on your server. One thing to keep in mind is that the plugin just includes the PHP file using `include()` so it does not technically execute a PHP script. 

= How can I know what exit code is returned from an action? =
The most built-in actions return `1` when they succeed and `0` on failure. You can check what exit code will be returned by enabling the log. 

To enable the log, go to **Dashboard** -> **Task Scheduler** -> **Manage Tasks** and click on the **Edit** link of the task. Set a number in the **Max Count of Log Entries** option. `50` would be sufficient to check exit codes.

After the task runs, click on the **View** link of the task listing table of the task. The log page will open and it should tell what exit code the action returns.

= How can I create a module? =
See the [Other Notes](https://wordpress.org/plugins/task-scheduler/other_notes/) section. It requires a basic PHP coding skill and understanding of object oriented programming. 

There are mainly two types of modules you can make, `action` and `occurrence`. Most of the time, you will want action modules.

Comprehensive instructions for creating modules are still in preparation. If you are interested, open the `include/class/module/action` folder and you'll see some built-in action modules. If you open some of the files, you'll notice that each of them are very short. What it does is basically extend a base module class like `TaskScheduler_Action_Base` and insert code in the methods predefined by the base class.

If you are comfortable reading PHP code, it should not be hard to figure out. Give it a try. If you get a question, don't hesitate to post a question about it.

= Found a bug. Where can I report? =
Please use the [GitHub repository](https://github.com/michaeluno/Task-Scheduler) of this plugin.

= How do I list my module? =
If you create a module plugin that can be shared by others, submit it to wordpress.org. 

== Screenshots ==

1. ***Task Listing Table***
2. ***Wizard***
3. ***Settings***


== Changelog ==

= 1.4.7- 2018/10/17 =
- Fixed a bug that caused a PHP warning of strict standards.

= 1.4.6 - 2018/08/01 =
- Added default and Japanese language files.
- Fixed a bug with the `Daily` occurrence type that spawned routines multiple times on some servers.

= 1.4.5 - 2017/06/11 =
- Fixed a bug with the `Daily` occurrence type that did not set the correct time for cases of 7 days ahead.

= 1.4.4 - 2017/03/11 =
- Fixed a bug in the Delete Posts action module that some posts without taxonomy items could not be deleted.

= 1.4.3 - 2016/12/27 =
- Optimized the performance of server-heartbeat.

= 1.4.2 - 2016/10/03 =
- Added the `Elapsed Time` option for the `Delete Posts` action module.
- Fixed PHP warnings of `Notice: Undefined property: stdClass::$delete_posts class-wp-posts-list-table.php on line 403` in the `Log` page.
- Tweaked the settings UI.

= 1.4.1 - 2016/09/30 =
- Added a filter for post query arguments of the `Delete Posts` action module.
- Fixed PHP warnings of `Declaration of TaskScheduler_Utility::uniteArrays() should be compatible with...`.

= 1.4.0 - 2016/09/21 =
- Added the `Run PHP Script` action module.
- Tweaked the settings UI.

= 1.3.4 - 2016/09/08 =
- Fixed a bug which caused a fatal error `Cannot redeclare class TaskScheduler_Routine_Base` in WordPress 4.6.1.

= 1.3.3 - 2016/09/01 =
- Fixed a bug that the ability to add Log items manually for multi-sites were not disabled in v1.3.2.

= 1.3.2 - 2016/08/23 =
- Fixed a compatibility issue with WordPress 4.6 that prevented routines from being processed.
- Deprecated the ability for the Log functionality to create an item manually.

= 1.3.1 - 2016/07/06 =
- Added an option to delete options upon plugin uninstall.
- Deprecated the option to delete options upon plugin deactivation.

= 1.3.0 - 2016/05/30 =
- Added a built-in action module which checks specified web pages.

= 1.2.0 - 2016/03/19 =
- Added the ability to set multiple email addresses per input field of the `Send Email` action module.
- Added the ability to clone tasks via an action link in the task listing table.

= 1.1.1 - 2016/02/03 =
- Fixed a bug that multiple routine instances get created with the `Daily` occurrence type.

= 1.1.0 - 2015/08/02 =
- Added a built-in action module that cleans expired transients.
- Added an option that enables the ability to remove hung routines.

= 1.0.2 - 2015/07/03 =
- Fixed auto-complete fields that did not work in WordPress 4.0 or above.
- Changed the timing of loading plugin components to support themes to add modules.

= 1.0.1 - 2015/05/12 =
- Fixed a bug in the `Delete Posts` action module that the taxonomy and post status options did not take effect.
- Fixed an incompatibility issue with WordPress 4.2 or above that in the listing table view, the view links lost the count indications.
- Fixed an incompatibility issue with WordPress 4.2 or above that taxonomy terms could not be listed.
- Changed it to accept an empty slug to create a custom module.
- Changed it to accept no wizard class to create a custom module.

= 1.0.0 - 2015/03/26 =
- Added the `daily` occurrence type.
- Updated Admin Page Framework.

= 1.0.0b13 - 2014/09/01 =
- Fixed an issue with sites enabling object caching.
- Fixed a bug of paged navigation links in the task listing table.

= 1.0.0b12 - 2014/08/27 =
- Fixed a bug that v1.0.0b11 had some missing files due to the incorrect character cases.

= 1.0.0b11 - 2014/08/22 =
- Added the ability for the `autocomplete` admin page framework custom field to search users that can be used by modules.

= 1.0.0b10 - 2014/08/18 =
- Deprecated the Hung Routine Handler action module.
- Refined the entire routine system to create each routine instance when a task starts.
- Changed the default routine status of tasks to be `Ready` from `Inactive`.
- Fixed a bug that the wizard form field of the Exit Code occurrence type did not function as of the previous version.
- Fixed a bug that when updating module options, the last run time meta value got lost.

= 1.0.0b09 - 2014/08/13 =
- Changed the method of including PHP files to keep maintainability.
- Tweaked the performance of the plugin admin pages.
- Fixed a bug that a test page created for debugging was remaining.
- Fixed a bug that module options were not displayed in the task editing page.

= 1.0.0b08 - 2014/08/09 =
- Added the `Check Action Now` button in the task listing table page.
- Added the `Number of Posts to Process per Routine` option to the `Delete Posts` action module.
- Tweaked the method of including PHP files to improve performance. 
- Tweaked the plugin admin pages to define forms within the own page loads.
- Tweaked the `Delete Posts` action module to load threads smoothly for sites disabling the server heartbeat.
- Changed the meta box output of modules to display stored module option values from all wizard screens if the module uses multiple wizard screens.

= 1.0.0b07 - 2014/08/06 =
- Added a new meta box in task edition page that includes the `Update` submit button, some time indications, and the switch option of `Enabled` or `Disabled`.
- Tweaked the mechanism of checking routines.
- Tweaked the `Delete Posts` action module not to insert a taxonomy query argument when the taxonomy slug is not selected.
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