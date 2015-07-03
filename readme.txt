=== Task Scheduler ===
Contributors:       Michael Uno, miunosoft
Donate link:        http://en.michaeluno.jp/donate
Requires at least:  3.7
Tested up to:       4.2.2
Stable tag:         1.0.1
License:            GPLv2 or later
License URI:        http://www.gnu.org/licenses/gpl-2.0.html
Tags:               access, tool, utility, background, backend, server, admin, task, management, system, event, scheduler, bulk, action, email, delete, post, cron, automation, routine, routines

Provides a task management system.

== Description ==

Do you have specific tasks which need to run at your desired time? Do you use WordPress as a proxy to generate data from external sources? As WordPress has evolved into a phase of application platforms, a more enhanced task management system needed to emerge.

Currently, with WP Cron, if you register, for example, 1000 tasks to run immediately and one of them stalls, it affects all the other actions preventing them from being loaded at the scheduled time. Also, the scheduled tasks won't be triggered if there is no visitor on the site. The goal of this plugin is to resolve such issues and become the perfect solution for WordPress powered back-end application servers to provide full-brown API functionalities.

<h4>What it does</h4>
- (optional) creates periodic background access to the site. 
- triggers tasks registered by the site owner at desired time or interval.

<h4>Built-in Actions</h4>
- <strong>Delete Posts</strong> - Performs bulk deletion of posts based on the post type, post statuses, taxonomy, and taxonomy terms.
- <strong>Send Email</strong> - Sends email to specified email addresses.

<h4>Custom Action Modules</h4>
- <strong>[Auto Post](https://wordpress.org/plugins/auto-post/)</strong> - creates posts automatically.

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

<h4>Create a Custom Action</h4>
You can run your custom action with Task Scheduler and run it with scheduled times, once a day, with a fixed interval. or whatever you set with the plugin.

**1.** Decide your action slug which also serves as a WordPress _filter_ hook.

Say you pick `my_custom_action` as an action name.

**2.** Use the `add_filter()` WordPress core function to hook into the action.

`
/**
 * Called when the Task Scheduler plugin gets loaded.
 */
function doMyCustomAction( $sExitCode, $oRoutine ) {
    
    /**
     * Do you stuff here.
     */
    TaskScheduler_Debug::log( $oRoutine->getMeta() );
    return 1;
    
}
/**
 * Set the 'my_custom_action' custom action slug in the Select Action screen
 * via Dashboard -> Task SCheduler -> Add New Task.
 */
add_filter( 'my_custom_action', 'doMyCustomAction', 10, 2 );
`

Please note that we use `add_filter()` not `add_action()` in order to return an exit code. 

Return `1` if the task completes and `0` when there is a problem. You can pass any value except `null`.

**3.** Go to **Dashboard** -> **Task Scheduler** -> **Add New Task**. Proceed the wizard and when you get the **Select Action** screen after setting up the occurrence, type **my_custom_action**, the one you defined in the above step.
  
The action slug set in the field will be triggered at the scheduled time.

See an example plugin (https://gist.github.com/michaeluno/5819636448947e7ab733).

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
     * Required arguments: 
     * 
     */
    public function doAction( $sExitCode, $oRoutine ) {
        
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

In the doAction() method of the above class, define the behaviour of your action what it does. The second parameter receives a routine object. The object has a public method named `getMeta()` which returns the associated arguments. 

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

See the entire example plugin (https://github.com/michaeluno/task-scheduler-sample-action-module).

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