<?php

class Logger
{

  private static $CI;
  public static $dbInitialized = false;

  /**
   * The collection that this record belongs to
   * @var string
   */
  protected static $_collection = 'logs';


  public static function CI()
  {
    if (!self::$CI) {
      self::$CI = get_instance();
    }
    return self::$CI;
  }

  public static function initDB()
  {
    if (!self::$dbInitialized) {
      self::CI()->load->model(array(
        'main_model'
      ));
      self::$dbInitialized = true;
    }
  }

  public static function SaveToDb(MongoId $id, array $data)
  {
    self::initDB();
    return self::$CI->main_model->_update($data, array('_id' => $id), static::CollectionName());
  }


  public static function CollectionName()
  {
    return static::$_collection;
  }

  public static function Create($data)
  {
    $filtered = di_allowed_only($data, mongo_get_allowed(static::CollectionName()));
    return self::CI()->mdb->insert(static::CollectionName(), $filtered);
  }

  public static function Update($id, $data)
  {
    if (!is_array($id)) $id = array(_id($id));
    foreach ($id as $i => $theId) $id[$i] = _id($theId);
    return self::CI()->mdb->whereIn('_id', $id)->set(di_allowed_only($data, mongo_get_allowed(static::CollectionName())))->updateAll(static::CollectionName());
  }

  public static function Post($message, $data = null, $type = 'debug', array $context = null){
    $add = array(
      'dateAdded' => new MongoDate(),
      'type' => in_array($type, array('debug','error','info')) ? $type : 'debug',
      'message' => $message,
      'data' => json_decode(json_encode($data)),
      'context' => $context
    );
    return self::Create($add);
  }

}