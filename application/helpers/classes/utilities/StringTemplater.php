<?php

require_once 'FindFunctionCalls.php';

class StringTemplater

{

  private static $_curlyPattern = '/{([^}]*)}/';

  public static function GetHooks($string){
    preg_match_all(self::$_curlyPattern, $string, $matches);
    foreach($matches[1] as $match){
      $return[] = array(
        '_raw' => $match,
        'parsed' => self::ParseToDataFormat($match)
      );
    }
    return $return;
  }

  public static function TestStrings(){
    $results = array();
    $tests = array(
      'Hello {user.name} and {user.email}. You\'ve completed workflow {workflow.name} {task.name}.  This project is due on {dateFormatter(dueDate)}',
      'Hello {capitalize(applyNickname(user.name))}. Welcome to {destination(user.id, date("F j Y", now()),"location")}',
      'Hello {name}, today is {date} and we are {dataCall(test.name, "wonder", testeroo(task.date))} {dataCall2(dataCall3(test))}'    );
    foreach($tests as $test){
      $results[] = self::GetHooks($test);
    }
    return $results;
  }

  public static function ParseToDataFormat($string, $level = 0){
    if($level >= 20) return; else $level++;
    $string = trim($string);
    $firstFunctionOpen = strpos($string, '(');
    $firstFunctionClose = strrpos($string, ')');
    // If is a function
    if($firstFunctionOpen !== false && $firstFunctionClose !== false){
      $argString = trim(substr($string, ($firstFunctionOpen + 1), -1));
      $args = explode(',', $argString);
      foreach($args as $i => $arg) if(trim($arg) == '') unset($args[$i]); else $args[$i] = trim($arg);
      $params = array();
      $args = array_values($args);
      foreach($args as $arg){
        $params[] = self::ParseToDataFormat($arg, $level);
      }
      $return = array(
        'funcName' => substr($string, 0, $firstFunctionOpen),
        'params' => $params
      );
    } else { // If it is just a string
      if($string[0] === '"' || $string[0] === "'"){
        $return = array(
          'value' => str_replace(array('"',"'"), '', $string)
        );
      } else {
        $return = array(
          'subValue' => $string
        );
      }
    }
    return $return;
  }

}