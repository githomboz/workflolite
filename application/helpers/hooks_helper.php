<?php
/**
 * The plugin API is located in this file, which allows for creating actions
 * and filters and hooking functions, and methods. The functions or methods will
 * then be run when the action or filter is called.
 *
 * The API callback examples reference functions, but can be methods of classes.
 * To hook methods, you'll need to pass an array one of two ways.
 *
 * Any of the syntaxes explained in the PHP documentation for the
 * {@link http://us2.php.net/manual/en/language.pseudo-types.php#language.types.callback 'callback'}
 * type are valid.
 *
 * Also see the {@link https://codex.wordpress.org/Plugin_API Plugin API} for
 * more information and examples on how to use a lot of these functions.
 *
 * @package WordPress
 * @subpackage Plugin
 * @since 1.5.0
 */

// Initialize the filter globals.
//global $CI->Hooks->filter, $CI->Hooks->actions, $CI->Hooks->merged_filters, $CI->Hooks->current_filter;

$CI =& get_instance();

if(!isset($CI->Hooks)) $CI->Hooks = new stdClass();

if(!isset($CI->Hooks->filter)) $CI->Hooks->filter = array(); // A multidimensional array of all hooks and the callbacks hooked to them.
if(!isset($CI->Hooks->actions)) $CI->Hooks->actions = array(); // Increments the amount of times action was triggered.
if(!isset($CI->Hooks->merged_filters)) $CI->Hooks->merged_filters = array(); // Tracks the tags that need to be merged for later. If the hook is added, it doesn't need to run through that process.
if(!isset($CI->Hooks->current_filter)) $CI->Hooks->current_filter = array(); // Stores the list of current filters with the current one last.

/**
 * Hook a function or method to a specific filter action.
 *
 * WordPress offers filter hooks to allow plugins to modify
 * various types of internal data at runtime.
 *
 * A plugin can modify data by binding a callback to a filter hook. When the filter
 * is later applied, each bound callback is run in order of priority, and given
 * the opportunity to modify a value by returning a new value.
 *
 * The following example shows how a callback function is bound to a filter hook.
 *
 * Note that `$example` is passed to the callback, (maybe) modified, then returned:
 *
 *     function example_callback( $example ) {
 *         // Maybe modify $example in some way.
 *     	   return $example;
 *     }
 *     add_filter( 'example_filter', 'example_callback' );
 *
 * Since WordPress 1.5.1, bound callbacks can take as many arguments as are
 * passed as parameters in the corresponding apply_filters() call. The `$accepted_args`
 * parameter allows for calling functions only when the number of args match.
 *
 * *Note:* the function will return true whether or not the callback is valid.
 * It is up to you to take care. This is done for optimization purposes,
 * so everything is as quick as possible.
 *
 * @since 0.71
 *
 * @global array $CI->Hooks->filter      A multidimensional array of all hooks and the callbacks hooked to them.
 * @global array $CI->Hooks->merged_filters Tracks the tags that need to be merged for later. If the hook is added,
 *                               it doesn't need to run through that process.
 *
 * @param string   $tag             The name of the filter to hook the $function_to_add callback to.
 * @param callback $function_to_add The callback to be run when the filter is applied.
 * @param int      $priority        Optional. Used to specify the order in which the functions
 *                                  associated with a particular action are executed. Default 10.
 *                                  Lower numbers correspond with earlier execution,
 *                                  and functions with the same priority are executed
 *                                  in the order in which they were added to the action.
 * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
 * @return true
 */
function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
  $CI =& get_instance();

  $idx = _filter_build_unique_id($tag, $function_to_add, $priority);
  $CI->Hooks->filter[$tag][$priority][$idx] = array('function' => $function_to_add, 'accepted_args' => $accepted_args);
  unset( $CI->Hooks->merged_filters[ $tag ] );
  return true;
}

/**
 * Check if any filter has been registered for a hook.
 *
 * @since 2.5.0
 *
 * @global array $CI->Hooks->filter Stores all of the filters.
 *
 * @param string        $tag               The name of the filter hook.
 * @param callback|bool $function_to_check Optional. The callback to check for. Default false.
 * @return false|int If $function_to_check is omitted, returns boolean for whether the hook has
 *                   anything registered. When checking a specific function, the priority of that
 *                   hook is returned, or false if the function is not attached. When using the
 *                   $function_to_check argument, this function may return a non-boolean value
 *                   that evaluates to false (e.g.) 0, so use the === operator for testing the
 *                   return value.
 */
function has_filter($tag, $function_to_check = false) {
  $CI =& get_instance();

  $has = ! empty( $CI->Hooks->filter[ $tag ] );

  // Make sure at least one priority has a filter callback
  if ( $has ) {
    $exists = false;
    foreach ( $CI->Hooks->filter[ $tag ] as $callbacks ) {
      if ( ! empty( $callbacks ) ) {
        $exists = true;
        break;
      }
    }

    if ( ! $exists ) {
      $has = false;
    }
  }

  if ( false === $function_to_check || false === $has )
    return $has;

  if ( !$idx = _filter_build_unique_id($tag, $function_to_check, false) )
    return false;

  foreach ( (array) array_keys($CI->Hooks->filter[$tag]) as $priority ) {
    if ( isset($CI->Hooks->filter[$tag][$priority][$idx]) )
      return $priority;
  }

  return false;
}

/**
 * Call the functions added to a filter hook.
 *
 * The callback functions attached to filter hook $tag are invoked by calling
 * this function. This function can be used to create a new filter hook by
 * simply calling this function with the name of the new hook specified using
 * the $tag parameter.
 *
 * The function allows for additional arguments to be added and passed to hooks.
 *
 *     // Our filter callback function
 *     function example_callback( $string, $arg1, $arg2 ) {
 *         // (maybe) modify $string
 *         return $string;
 *     }
 *     add_filter( 'example_filter', 'example_callback', 10, 3 );
 *
 *     /*
 *      * Apply the filters by calling the 'example_callback' function we
 *      * "hooked" to 'example_filter' using the add_filter() function above.
 *      * - 'example_filter' is the filter hook $tag
 *      * - 'filter me' is the value being filtered
 *      * - $arg1 and $arg2 are the additional arguments passed to the callback.
 *     $value = apply_filters( 'example_filter', 'filter me', $arg1, $arg2 );
 *
 * @since 0.71
 *
 * @global array $CI->Hooks->filter         Stores all of the filters.
 * @global array $CI->Hooks->merged_filters    Merges the filter hooks using this function.
 * @global array $CI->Hooks->current_filter Stores the list of current filters with the current one last.
 *
 * @param string $tag   The name of the filter hook.
 * @param mixed  $value The value on which the filters hooked to `$tag` are applied on.
 * @param mixed  $var   Additional variables passed to the functions hooked to `$tag`.
 * @return mixed The filtered value after all hooked functions are applied to it.
 */
function apply_filters( $tag, $value ) {
  $CI =& get_instance();

  $args = array();

  // Do 'all' actions first.
  if ( isset($CI->Hooks->filter['all']) ) {
    $CI->Hooks->current_filter[] = $tag;
    $args = func_get_args();
    _call_all_hook($args);
  }

  if ( !isset($CI->Hooks->filter[$tag]) ) {
    if ( isset($CI->Hooks->filter['all']) )
      array_pop($CI->Hooks->current_filter);
    return $value;
  }

  if ( !isset($CI->Hooks->filter['all']) )
    $CI->Hooks->current_filter[] = $tag;

  // Sort.
  if ( !isset( $CI->Hooks->merged_filters[ $tag ] ) ) {
    ksort($CI->Hooks->filter[$tag]);
    $CI->Hooks->merged_filters[ $tag ] = true;
  }

  reset( $CI->Hooks->filter[ $tag ] );

  if ( empty($args) )
    $args = func_get_args();

  do {
    foreach( (array) current($CI->Hooks->filter[$tag]) as $the_ )
      if ( !is_null($the_['function']) ){
        $args[1] = $value;
        $value = call_user_func_array($the_['function'], array_slice($args, 1, (int) $the_['accepted_args']));
      }

  } while ( next($CI->Hooks->filter[$tag]) !== false );

  array_pop( $CI->Hooks->current_filter );

  return $value;
}

/**
 * Execute functions hooked on a specific filter hook, specifying arguments in an array.
 *
 * @see 3.0.0
 *
 * @see apply_filters() This function is identical, but the arguments passed to the
 * functions hooked to `$tag` are supplied using an array.
 *
 * @global array $CI->Hooks->filter         Stores all of the filters
 * @global array $CI->Hooks->merged_filters    Merges the filter hooks using this function.
 * @global array $CI->Hooks->current_filter Stores the list of current filters with the current one last
 *
 * @param string $tag  The name of the filter hook.
 * @param array  $args The arguments supplied to the functions hooked to $tag.
 * @return mixed The filtered value after all hooked functions are applied to it.
 */
function apply_filters_ref_array($tag, $args) {
  $CI =& get_instance();

  // Do 'all' actions first
  if ( isset($CI->Hooks->filter['all']) ) {
    $CI->Hooks->current_filter[] = $tag;
    $all_args = func_get_args();
    _call_all_hook($all_args);
  }

  if ( !isset($CI->Hooks->filter[$tag]) ) {
    if ( isset($CI->Hooks->filter['all']) )
      array_pop($CI->Hooks->current_filter);
    return $args[0];
  }

  if ( !isset($CI->Hooks->filter['all']) )
    $CI->Hooks->current_filter[] = $tag;

  // Sort
  if ( !isset( $CI->Hooks->merged_filters[ $tag ] ) ) {
    ksort($CI->Hooks->filter[$tag]);
    $CI->Hooks->merged_filters[ $tag ] = true;
  }

  reset( $CI->Hooks->filter[ $tag ] );

  do {
    foreach( (array) current($CI->Hooks->filter[$tag]) as $the_ )
      if ( !is_null($the_['function']) )
        $args[0] = call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));

  } while ( next($CI->Hooks->filter[$tag]) !== false );

  array_pop( $CI->Hooks->current_filter );

  return $args[0];
}

/**
 * Removes a function from a specified filter hook.
 *
 * This function removes a function attached to a specified filter hook. This
 * method can be used to remove default functions attached to a specific filter
 * hook and possibly replace them with a substitute.
 *
 * To remove a hook, the $function_to_remove and $priority arguments must match
 * when the hook was added. This goes for both filters and actions. No warning
 * will be given on removal failure.
 *
 * @since 1.2.0
 *
 * @global array $CI->Hooks->filter         Stores all of the filters
 * @global array $CI->Hooks->merged_filters    Merges the filter hooks using this function.
 *
 * @param string   $tag                The filter hook to which the function to be removed is hooked.
 * @param callback $function_to_remove The name of the function which should be removed.
 * @param int      $priority           Optional. The priority of the function. Default 10.
 * @return bool    Whether the function existed before it was removed.
 */
function remove_filter( $tag, $function_to_remove, $priority = 10 ) {
  $CI =& get_instance();
  $function_to_remove = _filter_build_unique_id( $tag, $function_to_remove, $priority );

  $r = isset( $CI->Hooks->filter[ $tag ][ $priority ][ $function_to_remove ] );

  if ( true === $r ) {
    unset( $CI->Hooks->filter[ $tag ][ $priority ][ $function_to_remove ] );
    if ( empty( $CI->Hooks->filter[ $tag ][ $priority ] ) ) {
      unset( $CI->Hooks->filter[ $tag ][ $priority ] );
    }
    if ( empty( $CI->Hooks->filter[ $tag ] ) ) {
      $CI->Hooks->filter[ $tag ] = array();
    }
    unset( $CI->Hooks->merged_filters[ $tag ] );
  }

  return $r;
}

/**
 * Remove all of the hooks from a filter.
 *
 * @since 2.7.0
 *
 * @global array $CI->Hooks->filter         Stores all of the filters
 * @global array $CI->Hooks->merged_filters    Merges the filter hooks using this function.
 *
 * @param string   $tag      The filter to remove hooks from.
 * @param int|bool $priority Optional. The priority number to remove. Default false.
 * @return true True when finished.
 */
function remove_all_filters( $tag, $priority = false ) {
  $CI =& get_instance();

  if ( isset( $CI->Hooks->filter[ $tag ]) ) {
    if ( false === $priority ) {
      $CI->Hooks->filter[ $tag ] = array();
    } elseif ( isset( $CI->Hooks->filter[ $tag ][ $priority ] ) ) {
      $CI->Hooks->filter[ $tag ][ $priority ] = array();
    }
  }

  unset( $CI->Hooks->merged_filters[ $tag ] );

  return true;
}

/**
 * Retrieve the name of the current filter or action.
 *
 * @since 2.5.0
 *
 * @global array $CI->Hooks->current_filter Stores the list of current filters with the current one last
 *
 * @return string Hook name of the current filter or action.
 */
function current_filter() {
  $CI =& get_instance();
  return end( $CI->Hooks->current_filter );
}

/**
 * Retrieve the name of the current action.
 *
 * @since 3.9.0
 *
 * @return string Hook name of the current action.
 */
function current_action() {
  return current_filter();
}

/**
 * Retrieve the name of a filter currently being processed.
 *
 * The function current_filter() only returns the most recent filter or action
 * being executed. did_action() returns true once the action is initially
 * processed.
 *
 * This function allows detection for any filter currently being
 * executed (despite not being the most recent filter to fire, in the case of
 * hooks called from hook callbacks) to be verified.
 *
 * @since 3.9.0
 *
 * @see current_filter()
 * @see did_action()
 * @global array $CI->Hooks->current_filter Current filter.
 *
 * @param null|string $filter Optional. Filter to check. Defaults to null, which
 *                            checks if any filter is currently being run.
 * @return bool Whether the filter is currently in the stack.
 */
function doing_filter( $filter = null ) {
  $CI =& get_instance();

  if ( null === $filter ) {
    return ! empty( $CI->Hooks->current_filter );
  }

  return in_array( $filter, $CI->Hooks->current_filter );
}

/**
 * Retrieve the name of an action currently being processed.
 *
 * @since 3.9.0
 *
 * @param string|null $action Optional. Action to check. Defaults to null, which checks
 *                            if any action is currently being run.
 * @return bool Whether the action is currently in the stack.
 */
function doing_action( $action = null ) {
  return doing_filter( $action );
}

/**
 * Hooks a function on to a specific action.
 *
 * Actions are the hooks that the WordPress core launches at specific points
 * during execution, or when specific events occur. Plugins can specify that
 * one or more of its PHP functions are executed at these points, using the
 * Action API.
 *
 * @since 1.2.0
 *
 * @param string   $tag             The name of the action to which the $function_to_add is hooked.
 * @param callback $function_to_add The name of the function you wish to be called.
 * @param int      $priority        Optional. Used to specify the order in which the functions
 *                                  associated with a particular action are executed. Default 10.
 *                                  Lower numbers correspond with earlier execution,
 *                                  and functions with the same priority are executed
 *                                  in the order in which they were added to the action.
 * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
 * @return true Will always return true.
 */
function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
  return add_filter($tag, $function_to_add, $priority, $accepted_args);
}

/**
 * Execute functions hooked on a specific action hook.
 *
 * This function invokes all functions attached to action hook `$tag`. It is
 * possible to create new action hooks by simply calling this function,
 * specifying the name of the new hook using the `$tag` parameter.
 *
 * You can pass extra arguments to the hooks, much like you can with
 * {@see apply_filters()}.
 *
 * @since 1.2.0
 *
 * @global array $CI->Hooks->filter         Stores all of the filters
 * @global array $CI->Hooks->actions        Increments the amount of times action was triggered.
 * @global array $CI->Hooks->merged_filters    Merges the filter hooks using this function.
 * @global array $CI->Hooks->current_filter Stores the list of current filters with the current one last
 *
 * @param string $tag The name of the action to be executed.
 * @param mixed  $arg Optional. Additional arguments which are passed on to the
 *                    functions hooked to the action. Default empty.
 */
function do_action($tag, $arg = '') {
  $CI =& get_instance();

  if ( ! isset($CI->Hooks->actions[$tag]) )
    $CI->Hooks->actions[$tag] = 1;
  else
    ++$CI->Hooks->actions[$tag];

  // Do 'all' actions first
  if ( isset($CI->Hooks->filter['all']) ) {
    $CI->Hooks->current_filter[] = $tag;
    $all_args = func_get_args();
    _call_all_hook($all_args);
  }

  if ( !isset($CI->Hooks->filter[$tag]) ) {
    if ( isset($CI->Hooks->filter['all']) )
      array_pop($CI->Hooks->current_filter);
    return;
  }

  if ( !isset($CI->Hooks->filter['all']) )
    $CI->Hooks->current_filter[] = $tag;

  $args = array();
  if ( is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0]) ) // array(&$this)
    $args[] =& $arg[0];
  else
    $args[] = $arg;
  for ( $a = 2, $num = func_num_args(); $a < $num; $a++ )
    $args[] = func_get_arg($a);

  // Sort
  if ( !isset( $CI->Hooks->merged_filters[ $tag ] ) ) {
    ksort($CI->Hooks->filter[$tag]);
    $CI->Hooks->merged_filters[ $tag ] = true;
  }

  reset( $CI->Hooks->filter[ $tag ] );

  do {
    foreach ( (array) current($CI->Hooks->filter[$tag]) as $the_ )
      if ( !is_null($the_['function']) )
        call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));

  } while ( next($CI->Hooks->filter[$tag]) !== false );

  array_pop($CI->Hooks->current_filter);
}

/**
 * Retrieve the number of times an action is fired.
 *
 * @since 2.1.0
 *
 * @global array $CI->Hooks->actions Increments the amount of times action was triggered.
 *
 * @param string $tag The name of the action hook.
 * @return int The number of times action hook $tag is fired.
 */
function did_action($tag) {
  $CI =& get_instance();

  if ( ! isset( $CI->Hooks->actions[ $tag ] ) )
    return 0;

  return $CI->Hooks->actions[$tag];
}

/**
 * Execute functions hooked on a specific action hook, specifying arguments in an array.
 *
 * @since 2.1.0
 *
 * @see do_action() This function is identical, but the arguments passed to the
 *                  functions hooked to $tag< are supplied using an array.
 * @global array $CI->Hooks->filter         Stores all of the filters
 * @global array $CI->Hooks->actions        Increments the amount of times action was triggered.
 * @global array $CI->Hooks->merged_filters    Merges the filter hooks using this function.
 * @global array $CI->Hooks->current_filter Stores the list of current filters with the current one last
 *
 * @param string $tag  The name of the action to be executed.
 * @param array  $args The arguments supplied to the functions hooked to `$tag`.
 */
function do_action_ref_array($tag, $args) {
  $CI =& get_instance();

  if ( ! isset($CI->Hooks->actions[$tag]) )
    $CI->Hooks->actions[$tag] = 1;
  else
    ++$CI->Hooks->actions[$tag];

  // Do 'all' actions first
  if ( isset($CI->Hooks->filter['all']) ) {
    $CI->Hooks->current_filter[] = $tag;
    $all_args = func_get_args();
    _call_all_hook($all_args);
  }

  if ( !isset($CI->Hooks->filter[$tag]) ) {
    if ( isset($CI->Hooks->filter['all']) )
      array_pop($CI->Hooks->current_filter);
    return;
  }

  if ( !isset($CI->Hooks->filter['all']) )
    $CI->Hooks->current_filter[] = $tag;

  // Sort
  if ( !isset( $CI->Hooks->merged_filters[ $tag ] ) ) {
    ksort($CI->Hooks->filter[$tag]);
    $CI->Hooks->merged_filters[ $tag ] = true;
  }

  reset( $CI->Hooks->filter[ $tag ] );

  do {
    foreach( (array) current($CI->Hooks->filter[$tag]) as $the_ )
      if ( !is_null($the_['function']) )
        call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));

  } while ( next($CI->Hooks->filter[$tag]) !== false );

  array_pop($CI->Hooks->current_filter);
}

/**
 * Check if any action has been registered for a hook.
 *
 * @since 2.5.0
 *
 * @see has_filter() has_action() is an alias of has_filter().
 *
 * @param string        $tag               The name of the action hook.
 * @param callback|bool $function_to_check Optional. The callback to check for. Default false.
 * @return bool|int If $function_to_check is omitted, returns boolean for whether the hook has
 *                  anything registered. When checking a specific function, the priority of that
 *                  hook is returned, or false if the function is not attached. When using the
 *                  $function_to_check argument, this function may return a non-boolean value
 *                  that evaluates to false (e.g.) 0, so use the === operator for testing the
 *                  return value.
 */
function has_action($tag, $function_to_check = false) {
  return has_filter($tag, $function_to_check);
}

/**
 * Removes a function from a specified action hook.
 *
 * This function removes a function attached to a specified action hook. This
 * method can be used to remove default functions attached to a specific filter
 * hook and possibly replace them with a substitute.
 *
 * @since 1.2.0
 *
 * @param string   $tag                The action hook to which the function to be removed is hooked.
 * @param callback $function_to_remove The name of the function which should be removed.
 * @param int      $priority           Optional. The priority of the function. Default 10.
 * @return bool Whether the function is removed.
 */
function remove_action( $tag, $function_to_remove, $priority = 10 ) {
  return remove_filter( $tag, $function_to_remove, $priority );
}

/**
 * Remove all of the hooks from an action.
 *
 * @since 2.7.0
 *
 * @param string   $tag      The action to remove hooks from.
 * @param int|bool $priority The priority number to remove them from. Default false.
 * @return true True when finished.
 */
function remove_all_actions($tag, $priority = false) {
  return remove_all_filters($tag, $priority);
}


/**
 * Call the 'all' hook, which will process the functions hooked into it.
 *
 * The 'all' hook passes all of the arguments or parameters that were used for
 * the hook, which this function was called for.
 *
 * This function is used internally for apply_filters(), do_action(), and
 * do_action_ref_array() and is not meant to be used from outside those
 * functions. This function does not check for the existence of the all hook, so
 * it will fail unless the all hook exists prior to this function call.
 *
 * @since 2.5.0
 * @access private
 *
 * @global array $CI->Hooks->filter  Stores all of the filters
 *
 * @param array $args The collected parameters from the hook that was called.
 */
function _call_all_hook($args) {
  $CI =& get_instance();

  reset( $CI->Hooks->filter['all'] );
  do {
    foreach( (array) current($CI->Hooks->filter['all']) as $the_ )
      if ( !is_null($the_['function']) )
        call_user_func_array($the_['function'], $args);

  } while ( next($CI->Hooks->filter['all']) !== false );
}

/**
 * Build Unique ID for storage and retrieval.
 *
 * The old way to serialize the callback caused issues and this function is the
 * solution. It works by checking for objects and creating a new property in
 * the class to keep track of the object and new objects of the same class that
 * need to be added.
 *
 * It also allows for the removal of actions and filters for objects after they
 * change class properties. It is possible to include the property $filter_id
 * in your class and set it to "null" or a number to bypass the workaround.
 * However this will prevent you from adding new classes and any new classes
 * will overwrite the previous hook by the same class.
 *
 * Functions and static method callbacks are just returned as strings and
 * shouldn't have any speed penalty.
 *
 * @link https://core.trac.wordpress.org/ticket/3875
 *
 * @since 2.2.3
 * @access private
 *
 * @global array $CI->Hooks->filter Storage for all of the filters and actions.
 * @staticvar int $filter_id_count
 *
 * @param string   $tag      Used in counting how many hooks were applied
 * @param callback $function Used for creating unique id
 * @param int|bool $priority Used in counting how many hooks were applied. If === false
 *                           and $function is an object reference, we return the unique
 *                           id only if it already has one, false otherwise.
 * @return string|false Unique ID for usage as array key or false if $priority === false
 *                      and $function is an object reference, and it does not already have
 *                      a unique id.
 */
function _filter_build_unique_id($tag, $function, $priority) {
  $CI =& get_instance();
  static $filter_id_count = 0;

  if ( is_string($function) )
    return $function;

  if ( is_object($function) ) {
    // Closures are currently implemented as objects
    $function = array( $function, '' );
  } else {
    $function = (array) $function;
  }

  if (is_object($function[0]) ) {
    // Object Class Calling
    if ( function_exists('spl_object_hash') ) {
      return spl_object_hash($function[0]) . $function[1];
    } else {
      $obj_idx = get_class($function[0]).$function[1];
      if ( !isset($function[0]->filter_id) ) {
        if ( false === $priority )
          return false;
        $obj_idx .= isset($CI->Hooks->filter[$tag][$priority]) ? count((array)$CI->Hooks->filter[$tag][$priority]) : $filter_id_count;
        $function[0]->filter_id = $filter_id_count;
        ++$filter_id_count;
      } else {
        $obj_idx .= $function[0]->filter_id;
      }

      return $obj_idx;
    }
  } elseif ( is_string( $function[0] ) ) {
    // Static Calling
    return $function[0] . '::' . $function[1];
  }
}