<?php

/******* [ General Site Settings ] ********/

$config['company_name'] = 'Better Business Technology, LLC';
$config['site_name'] = 'WorkfloLite.com';
$config['site_url'] = NULL;
$config['footer_message'] = 'Copyright &copy; ' . date('Y') . ' | ' . $config['site_name'] . ' | All Rights Reserved';
$config['pagination_rpp'] = 20; // default Rows Per Page
$config['google_analytics'] = null; // Analytics id // example: UA-78160000-0

/******* [ SEO ] ********/

$config['set_noindex_nofollow'] = true;
$config['seo_site_name'] = null;
$config['seo_description'] = null;
$config['seo_image'] = null;
$config['twitter_publisher_handle'] = null;
$config['twitter_author_handle'] = null;
$config['seo_url'] = null;
$config['fb_app_id'] = null;

/******* [ Security ] ********/

$config['pak'] = 'ymta8zCzl3k'; // Private API Key / SALT
$config['encrypt_salt'] = 'n053njlsrq'; // SALT used to encrypt passwords and other data. (Do not change after using)

/******* [ System ] ********/

$config['site_debug_mode'] = true;
$config['bootstrap_mode'] = false; // enable admin/dev class
$config['use_relative_urls'] = true;
$config['work_offline_mode'] = true;
$config['utilize_mongo_db'] = true;

/******* [ DB Logging ] *******/

$config['enable_db_logging'] = false;

/******* [ API ] ********/

$config['api_benchmarking'] = true;
$config['api_example_uri'] = false;
$config['api_example_query'] = false;
$config['api_show_args'] = false;
$config['api_show_query'] = false;

/******* [ Navigation ] ********/

$config['navigation']['general'] = array(
  array(
    'href' => '/home',
    'title' => 'Home',
  )
);

$config['navigation']['members'] = array(
  array(
    'href' => '/my-account',
    'title' => 'My Account',
  )
);

$config['navigation']['admin'] = array(
  array(
    'href' => '/admin/manage-users',
    'title' => 'Manage Users',
  ),
);

/******* [ Email Templates Info ] ********/

$config['email_senders'] = array(
  'xxx' => array(
    'from' => 'no-reply@mycompany.com',
    'name' => 'My Company Team',
    //'subject' => 'Write in a subject',
    //'message' => 'Write in a message',
    'subject_template' => APPPATH.'views/templates/email_xxx_subject.php',
    'message_template' => APPPATH.'views/templates/email_xxx_message.php',
  ),
);

/******* [ Mongo Schema ] ********/

$config['mongo_schema'] = array(
  'logs' => array(
    'dateAdded',
    'type',
    'tags',
    'content',
    'phpErr',
  ),
  'organizations' => array(
    'name',
    'dateAdded',
    'slug',
    'settings',
  ),
  'users' => array(
    'organizationId',
    'dateAdded',
    'firstName',
    'lastName',
    'username',
    'password',
    'email',
    'phone',
    'status',
    'currentTask', // What task did this user touch most recently
    'settings',
    'enableSMS'
  ),
  'workflowCategories' => array(
    'name',
    'parent',
  ),
  'workflows' => array(
    'dateAdded',
    'organizationId', 
    'name',
    'categoryId',
    'description',
    'roles',
    'group',
    'status',
    'taskTemplates', // ordered list of taskTemplates
    'accessibility', // Who has access to use this template
    'metaFields', // Fields that are needed for job completion
  ),
  'templates' => array(
    'dateAdded',
    'organizationId',
    'name',
    'categoryId',
    'description',
    'roles',
    'group',
    'status',
    'noun', // the object instance type. ie "title file" or "twitter order"
    'taskTemplates', // ordered list of taskTemplates
    'availStatuses', // statuses available to all project tasks
    'accessibility', // Who has access to use this template
    'metaFields', // Fields that are needed for job completion
    'versionData', // updated fields and their values mapped to a version number
    'version' // the current version of this template
  ),
  'jobs' => array(
    'organizationId',
    'workflowId',
    'dateAdded',
    'name',
    'dueDate', // a date set by user to track when workflow should be completed
    'approxEndDate', // approx end date based upon delays in task completion
    'nativeToken',
    'partiesInvolved', // associative array of party members and their roles
    'viewableContacts', // the contacts that are able to view this job
    'sortOrder', // Array with the orders of tasks for the job
    'meta',
    'settings',
  ),
  'projects' => array(
    'organizationId',
    'templateId',
    'templateVersion', // The version of the template being used
    'dateAdded',
    'name',
    'dueDate', // a date set by user to track when workflow should be completed
    'approxEndDate', // approx end date based upon delays in task completion
    'nativeToken',
    'partiesInvolved', // associative array of party members and their roles
    'viewableContacts', // the contacts that are able to view this job
    'sortOrder', // Array with the orders of tasks for the job
    'taskTemplates', // Array of tasks if is custom project
    'taskMeta', // Tasks specific data such as start time, end time, and status
    'availStatuses', // Statuses available for this particular project
    'meta',
    'settings',
  ),
  'tasks' => array(
//    'name', // FOR OVERRIDE
//    'instructions', // FOR OVERRIDE
//    'visibility', // FOR OVERRIDE
//    'estimatedTime', // FOR OVERRIDE
//    'sortOrder', // FOR OVERRIDE
//    'taskGroup', // FOR OVERRIDE
//    'publiclyAccessible', // FOR OVERRIDE
//    'optional', // FOR OVERRIDE
    'organizationId', // Organization that the task belongs to
    'dateAdded',
    'taskTemplateId', // A link to default task options (to manage space...lots of repeated data)
    'jobId',
    'steps', // Checklist of what needs to be done
    'workflowId',
    'activeUsers', // The users that are currently working on a certain task
    'assigneeId', // One or more users assigned to a task
    'triggers', // triggers to be merged with taskTemplate.nativeTriggers ( name/payload/blocking )
    'status', // [notstarted, inprogress, complete, skipped]
    'startDate',
    'completeDate',
    'comments', // Added specifically for JNBPA @todo remove this
    'dependencies', // Tasks that need to be completed before task can be completed
    'isPublicMilestone'
  ),
  'taskTemplates' => array(
    'name',
    'instructions',
    'milestone', // Whether or not this task is a milestone
    'description', // Public explanation of what is done in this task
    'clientView', // Whether or not this task shows up on client progress portal
    'overviewVisibility', // Whether or not this task shows up on main jobs page
    'estimatedTime', // In hours, amount of time it should take to complete this task
    'taskGroup',
    'requestApproval', // Request approval of task before task can be completed
    'nativeTriggers', // Array of options divided into two stages. Before start and after complete
    'organizationId', // Org that this template belongs to
    'publiclyAccessible', // Who has access to use this template
    'optional', // whether or not a task is mandatory or option
  ),
  'triggerQueue' => array(
    'dateAdded',
    'scheduleDateTime',
    'completeDateTime',
    'jobId',
    'taskId',
    'trigger',
    'sequence',
    'blocking', // Whether to continue to next in trigger in sequence if this one hasn't completed
    'triggerServer', // The server that processed the trigger
    'payload',
    'status', // [queued, working, failure, success]
    'processed', // Whether or not this trigger has been processed
    'acknowledged', // Whether or not the job is aware of completion of this trigger
    'organizationId',
    'returnPayload', // Data to be returned to by trigger
    'webhook', // Url to post trigger response to
    'topic', // String used to associate with with task or step that queue item was registered from
  ),
  'triggers' => array(
    'name',
    'class', // The group this trigger belongs to. // Messaging, Documents, Calendar, etc.
    'description',
    'instructions',
    'samplePayload',
    'canQueue',
    'options',
    'organizationId',
    'active',
  ),
  'contacts' => array(
    'dateAdded',
    'organizationId', // The organization that this contact belongs to
    'name',
    'phone',
    'mobile',
    'email',
    'pin',
    'settings',
    'active',
  ),
  'notifications' => array(
    'sourceId',
    'targetId',
    'dateAdded',
    'message',
    'opened',
    'seen',
  ),
  'webhooks' => array(
    'referrer',
    'dateAdded',
    'payload',
    'processed',
    'triggerId', // This refers to the trigger that originally called the webhook
  ),
  'settings' => array(
    'group',
    'key',
    'value',
    'onload',
    'dateAdded',
    'expires',
  ),
  'admins' => array(
    'email',
    'password',
    'fName',
    'lName',
    'access',
    'status',
  ),
);