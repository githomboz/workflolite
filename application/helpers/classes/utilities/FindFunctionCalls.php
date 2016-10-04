<?php

class FindFunctionCalls

{

  /**
   * @var $_functions Array of discovered functions
   */
  private $_functions = array();

  /**
   * @var Source Original source string
   */
  private $_source;

  private $_meta = array();


  /**
   * Discover all function declarations in a string and return uniform structure.
   * FindFunctionCalls constructor.
   * @param $string Source string to be searched
   */
  public function __construct($string)
  {
    $this->_source = $string;
    $this->process();
    $this->test = $this->discoverFunctions($string);
  }

  public function process(){
    $this->getCounts();
  }

  public function getCounts(){
    // Get number of opening braces
    $this->_meta['opening_brace'] = substr_count( $this->_source, '(');

    // Get number of closing braces
    $this->_meta['closing_brace'] = substr_count( $this->_source, ')');

  }

  public function parse($string, $parent = null){
    $firstFunctionOpen = strpos($string, '(');
    $firstFunctionClose = strrpos($string, ')');
    $argString = substr($string, ($firstFunctionOpen + 1), -1);
    $args = explode(',', $argString);
    if($firstFunctionOpen !== false){
      $this->_functions[] = array(
        'funcName' => substr($string, 0, $firstFunctionOpen),
        'params' => $args
      );
    }
  }
}