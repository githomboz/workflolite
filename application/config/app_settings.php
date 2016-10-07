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
    'name',
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
    'status',
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
    'meta',
    'settings',
  ),
  'tasks' => array(
    'name', // FOR OVERRIDE
    'instructions', // FOR OVERRIDE
    'visibility', // FOR OVERRIDE
    'estimatedTime', // FOR OVERRIDE
    'sortOrder', // FOR OVERRIDE
    'taskGroup', // FOR OVERRIDE
    'publiclyAccessible', // FOR OVERRIDE
    'optional', // FOR OVERRIDE
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
    'startedDate',
    'completedDate',
    'dependencies', // Tasks that need to be completed before task can be completed
    'isPublicMilestone'
  ),
  'jobTemplates' => array( // A saved template of all the tasks and their settings
    'workflowId',
    'tasksTemplates', // ordered list of taskTemplates
    'meta', // random information about the job
    'organizationId', // Org that this template belongs to
    'accessibility', // Who has access to use this template
    'metaFields',
  ),
  'taskTemplates' => array(
    'name',
    'instructions',
    'visibility', // Whether or not this task shows up on client progress portal
    'overviewVisibility', // Whether or not this task shows up on main jobs page
    'estimatedTime', // In hours, amount of time it should take to complete this task
    'sortOrder', // The sequential arrangement of the tasks. ( In tenths )
    'taskGroup',
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
    'status',
    'processed',
    'organizationId',
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
    'organizationId', // The organization that this contact belongs to
    'name',
    'phone',
    'email',
    'role',
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