<?php

/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 2/24/17
 * Time: 5:07 PM
 */
class WFSimpleForm
{

  public static function GetForm($formId){
    return [];
  }

  public static function GetFormData($formId){
    $form = self::GetForm($formId);
    if($form){
      return isset($form['data']) ? $form['data'] : null;
    }
  }

  public static function VerifyFormTypeFormat($formData){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $response = WFClientInterface::GetPayloadTemplate();

    if(is_string($formData)){
      $formData = json_decode_cs($formData, true);
      if($formData['_valid']){
        $formData = $formData['_decode'];
      } else {
        $logger->setLine(__LINE__)->addError('Invalid json string provided for form data');
      }
    }

    $response['response']['formData'] = $formData;

    if(!$logger->hasErrors(__FUNCTION__)){
      if(isset($formData['type'])){
        if(in_array($formData['type'], ['object','array'])){
          $response['response']['success'] = true;
          $response['response']['verified'] = WFSimpleForm_Element::VerifyElementType($formData);
        } else {
          $logger->setLine(__LINE__)->addError('Invalid root element. Must be types `object` or  `array`.', $formData['type']);
        }
      } else {
        $logger->setLine(__LINE__)->addError('Type not set');
      }
    }

    $response['logs'] = $logger->getLogsArray();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }

  public static function RenderForm($formData){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $response = WFClientInterface::GetPayloadTemplate();

    $validationResponse = self::VerifyFormTypeFormat($formData);
    if(isset($validationResponse['errors']) && !empty($validationResponse['errors'])) {
      $logger->setLine(__LINE__)->addError('Validation errors have occurred');
    } else {
      $response['response']['success'] = true;
      $response['response']['data'] = self::_drawFromFormData($validationResponse['response']['formData']);
    }

    $response['logs'] = $logger->getLogsArray();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }

  private static function _generateIDAttribute($formData){
    $id = md5(json_encode($formData));
    return $id;
  }

  private static function _drawFromFormData($formData){
    //ar_dump($formData);
    $drawFunction = 'WFSimpleForm_Element::_draw_' . $formData['type'];
    if(is_callable($drawFunction)){
      $output = '<form method="post" ';
      //var_dump($formData);
      $output .= 'data-formfly="' . base64_encode(json_encode($formData)) . '" ';
      $output .= 'id="formfly-' . self::_generateIDAttribute($formData) . '" ';
      $output .= 'class="form-fly" ';
      $output .= '>';
      $output .= call_user_func_array($drawFunction, [$formData, null]);
      $output .= '<button type="submit" class="submit-formfly-btn">Submit</button>';
      $output .= '</form>';
      return $output;
    }
  }

}

class WFSimpleForm_Element {

  protected $type = null;

  protected $_elements = []; // WFSimpleForm_Elements that have been added to the current element
  protected $_attributes = []; // Additional attributes to add to data array

  protected $_errors = [];
  private $_default_error = 'NO DATA ELEMENTS';

  public function __construct($type){

    $atts = self::GetTypeAttributes();
    $types = array_keys($atts);
    if(in_array($type, $types)){
      $this->type = $type;
      if(in_array($type, ['object','array'])) $this->_errors[] = $this->_default_error;
    } else {
      $this->_errors[] = 'Invalid type provided (`'.(string) $type.'`)';
    }

  }

  public function addElement($element){
    $added = false;
    if($element instanceof WFSimpleForm_Element){
      if(!$element->errors()) {
        $added = true;
        $this->_elements[] = $element->data();
      }
    } elseif(is_array($element)) {
      $validation = self::VerifyElement($element);
      if(!$validation['errors']){
        $added = true;
        $this->_elements[] = $element;
      }
    }
    return $added;
  }

  public function errors(){
    return !empty($this->_errors);
  }

  public function getElements(){
    return $this->_elements;
  }

  public function data(){
    // Create Added data
    $data = [
      'type' => $this->type,
    ];
    switch($this->type){
      case 'object': $data['properties'] = $this->getElements();
        break;
      case 'array': $data['items'] = $this->getElements();
        break;
    }

    foreach($this->_attributes as $attribute){
      if(in_array($attribute, self::GetTypeAttributes($this->type))){
        $data[$attribute] = $this->_attributes[$attribute];
      }
    }

    return $data;
  }

  public static function GetTypeAttributes($type = null){
    $attributes = [
      'string' => ['enums','minLength','maxLength','pattern'],
      'number' => ['integer','minimum','exclusiveMinimum','maximum','exclusiveMaximum'],
      'boolean' => [],
      'array' => ['items','minItems','maxItems'],
      'object' => ['properties','description','required','optional']
    ];
    if($type){
      if(isset($attributes[$type])) return $attributes[$type]; else return null;
    }
    return $attributes;
  }

  public static function VerifyElementType(array $element){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $response = WFClientInterface::GetPayloadTemplate();

    $type = $element['type'];
    $response['response']['validationResults'] = self::_verify_type_tests($type, $element);

    $response['logs'] = $logger->getLogsArray();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }

  public static function _verify_type_tests($type, $element, $return_valid = false){
    $attributes = self::GetTypeAttributes($type);
    $response = [];
    foreach($attributes as $attribute){
      if(isset($element[$attribute])) {
        $test = 'WFSimpleForm_Element::_verify_type_' . $type . '_' . $attribute;
        $rand = md5($test . time() . rand(0, (255*255*255)));
        $response[$rand] = [
          'test' => $test,
          'value' => $element[$attribute],
          'testRan' => false,
        ];
        if(is_callable($test)) {
          $valid = call_user_func_array($test, array($element[$attribute]));
          $response[$rand]['testRan'] = true;
        } else {
          $valid = true;
        }
        $response[$rand]['valid'] = $valid;
      }
    }
    //var_dump($response);

    if($return_valid){
      foreach($response as $test) if(!$test['valid']) return false;
      return true;
    }
    return array_values($response);
  }

  /**
   * Verify that passed value is an array of string options
   * @param $value
   * @return bool
   */
  public static function _verify_type_object_properties($value){
    $response = true;
    $atts = self::GetTypeAttributes();
    $types = array_keys($atts);
    if(!is_array($value)) $response = false;
    if($response) foreach($value as $elementKey => $element) {
      $isNestedObject = !isset($value['type']) && isset($element['type']);
      if($isNestedObject){
        $response = self::_verify_type_tests($element['type'], $element, true);
      } else {
        $response = self::_verify_type_tests($value['type'], $value, true);
      }
    }
    return $response;
  }

  /**
   * Verify that passed value is an array of string options
   * @param $value
   * @return bool
   */
  public static function _verify_type_object_required($value){
    $response = true;
    if(!is_array($value)) $response = false;
    if($response) foreach($value as $item) if(!is_string($item)) $response = false;
    return $response;
  }

  /**
   * Verify that passed value is an array of string options
   * @param $value
   * @return bool
   */
  public static function _verify_type_string_enums($value){
    $response = true;
    if(!is_array($value)) $response = false;
    if($response) foreach($value as $item) if(!is_string($item)) $response = false;
    return $response;
  }

  /**
   * Verify that passed value is an integer more than 1
   * @param $value
   * @return bool
   */
  public static function _verify_type_string_minLength($value){
    return (int) $value > 1;
  }

  /**
   * Verify that passed value is an integer more than 0
   * @param $value
   * @return bool
   */
  public static function _verify_type_string_maxLength($value){
    return (int) $value > 0;
  }

  /**
   * Verify that passed value is a string and a valid regex pattern
   * @param $value @todo: Make sure to add a test for valid 'js' regex pattern
   * @return bool
   */
  public static function _verify_type_string_pattern($value){
    return is_string($value);
  }

  public static function _draw($element, $name = null, $parent = null){
    $output = '';

    switch ($element['type']):
      case 'string':
        $output .= self::_draw_string($element, $name, $parent);
        break;
      case 'number':
        $output .= self::_draw_number($element, $name, $parent);
        break;
      case 'boolean':
        $output .= self::_draw_boolean($element, $name, $parent);
        break;
      case 'array':
        $output .= self::_draw_array($element, $name, $parent);
        break;
      case 'object':
        $output .= self::_draw_object($element, $name, $parent);
        break;
      endswitch;
    return $output;
  }

  public static function _draw_string($element, $name, $parent = null){
    $output = '';
    $originalName = true;
    if(!$name){
      $originalName = false;
      $name = self::_generate_field_name($element['type']);
    }
    if($element['type'] === 'string'){
      $output .= '<div class="csform__' . $element['type'] . '">'."\n";
      if(isset($element['label']) && !empty($element['label'])) $output .= self::_draw_label($element['label']);
      $placeholder = isset($element['placeholder']) ? $element['placeholder'] : ($originalName ? self::_parse_label($name) : null);
      $required = ($parent && isset($parent['required']) && is_array($parent['required']) && in_array($name, $parent['required']));
      $optional = ($parent && isset($parent['optional']) && is_array($parent['optional']) && in_array($name, $parent['optional']));

      $isOptional = $required === false || $optional === true;

      $classes = '';
      if(!$isOptional) $classes .= ' required';

      if(isset($element['enum'])){
        $output .= '<select>' . "/n";
        $output .= '<option>Select '.strtolower(self::_parse_label($name)) . ($isOptional ? ' (optional)' : '') . '</option>';
        foreach($element['enum'] as $option){
          $output .= '<option value="'. $option .'">' . $option . '</option>';
        }
        $output .= '</select>';
      } else {
        $output .= '<input type="text" class="'.trim($classes).'" name="' . $name . '" ';
        if($placeholder){
          $output .= 'placeholder="' . $placeholder;
          $output .= ($isOptional? ' (optional)':'') .'"';
        }
        $output .= ' />';
      }

      $output .= '</div><!--/.csform__' . $element['type'] . '-->'."\n";
    }
    return $output;
  }

  public static function _draw_number($element, $name, $parent = null){
    $output = '';
    $originalName = true;
    if(!$name){
      $originalName = false;
      $name = self::_generate_field_name($element['type']);
    }
    if($element['type'] === 'number'){
      $output .= '<div class="csform__' . $element['type'] . '">'."\n";
      if(isset($element['label']) && !empty($element['label'])) $output .= self::_draw_label($element['label']);
      $placeholder = isset($element['placeholder']) ? $element['placeholder'] : ($originalName ? self::_parse_label($name) : null);
      $required = ($parent && isset($parent['required']) && is_array($parent['required']) && in_array($name, $parent['required']));
      $optional = ($parent && isset($parent['optional']) && is_array($parent['optional']) && in_array($name, $parent['optional']));

      $isOptional = $required === false || $optional === true;

      $classes = '';
      if(!$isOptional) $classes .= ' required';

      if(isset($element['enum'])){
        $output .= '<select>' . "/n";
        $output .= '<option>Select '.strtolower(self::_parse_label($name)) . ($isOptional ? ' (optional)' : '') . '</option>';
        foreach($element['enum'] as $option){
          $output .= '<option value="'. $option .'">' . $option . '</option>';
        }
        $output .= '</select>';
      } else {
        $output .= '<input type="text" class="'.trim($classes).'" name="' . $name . '" ';
        if($placeholder){
          $output .= 'placeholder="' . $placeholder;
          $output .= ($isOptional? ' (optional)':'') .'"';
        }
        $output .= ' />';
      }

      $output .= '</div><!--/.csform__' . $element['type'] . '-->'."\n";
    }
    return $output;
  }


  public static function _draw_boolean($element, $name, $parent = null){
    $output = '';
    $originalName = true;
    if(!$name){
      $originalName = false;
      $name = self::_generate_field_name($element['type']);
    }
    if($element['type'] === 'boolean'){
      $output .= '<div class="csform__' . $element['type'] . '">'."\n";

      $output .= '<input type="checkbox" name="'.$name.'" /> ';
      $output .= self::_draw_label($name);

      $output .= '</div><!--/.csform__' . $element['type'] . '-->'."\n";
    }
    return $output;
  }

  public static function _draw_array($element, $name, $parent = null){
    $output = '';
    $originalName = true;
    if(!$name){
      $originalName = false;
      $name = self::_generate_field_name($element['type']);
    }
    $label = isset($element['label']) ? $element['label'] : ($originalName ? $name : null);

    //var_dump($label, $name, $originalName);
    $fieldName = $originalName ? $name : null;
    if($element['type'] === 'array'){
      $output .= '<div class="csform__' . $element['type'] . '">'."\n";

      $classes = '';
      if($label) {
        $output  .= self::_draw_label($label);
        $classes .= ' has-label';
      }

      $output .= '<div class="csform__repeater' . $classes . '" data-repeater_field="' . $fieldName . '">'."\n";
      //var_dump($label);
      //var_dump($element['items']);
      //var_dump($fieldName);
      //var_dump($parent);
      $output .= self::_draw($element['items'], $fieldName, $parent);
      $output .= '<!--/.csform_repeater--></div>'."\n";
      $output .= '<button type="submit" class="repeater-add-btn">+ Add</button>';
      $output .= "\n";

      $output .= '</div><!--/.csform__' . $element['type'] . '-->'."\n";
    }
    return $output;
  }

  public static function _draw_object($element, $name, $parent = null){
    $output = '';
    $originalName = true;
    if(!$name){
      $originalName = false;
      $name = self::_generate_field_name($element['type']);
    }
    $label = isset($element['label']) ? $element['label'] : ($originalName ? $name : null);
    $suppressLabel = isset($element['suppressLabel']) && $element['suppressLabel'];
    //var_dump($label);
    if($element['type'] === 'object'){
      $output .= '<div class="csform__' . $element['type'] . '">'."\n";



      if($label && !$suppressLabel) $output .= self::_draw_label($label);
      foreach($element['properties'] as $elementName => $property){
        $output .= self::_draw($property, $elementName, $element);
      }

      $output .= '</div><!--/.csform__' . $element['type'] . '-->'."\n";
    }
    return $output;
  }

  public static function _parse_label($name){
    // Handle underscored
    if(strpos($name, '_')) $name = str_replace('_', ' ', $name);
    // Handle CamelCase
    $name = preg_replace(array('/(?<=[^A-Z])([A-Z])/', '/(?<=[^0-9])([0-9])/'), ' $0', $name);
    return ucwords($name);
  }

  public static function _draw_label($name){
    $output = '<label>' . self::_parse_label($name) . '</label>';
    return $output;
  }

  public static function _generate_field_name($type){
    return $type . time().'_'.rand(0, (255*255*255));
  }



}