<?php

/**
 * Created by PhpStorm.
 * User: benezerlancelot
 * Date: 10/22/16
 * Time: 12:11 PM
 */

require_once 'MetaObject.php';

class MetaAddress extends MetaObject {

  protected static $_type = 'address';

  public function display(array $lineBreaksAfterFields = null){
    if($this->_cache) return $this->_cache;
    else {
      if(!$lineBreaksAfterFields) $lineBreaksAfterFields[] = 'street';
      $html = '';
      foreach(self::getRequiredFields() as $field){
        if(isset($this->_data[$field])) {
          $html .= '<span class="' . $field . '">' . $this->_data[$field] . '</span>';
          if(in_array($field, array('city','street'))) $html .= ', ';
          $html .= (!empty($lineBreaksAfterFields) ? (in_array($field, $lineBreaksAfterFields) ? '<br />' : '') : '');
          $html .= ' ';
        }
      }
      $html .= '<a href="#map_it"><i class="fa fa-map-marker"></i></a>';
      $this->_cache = $html;
      return $this->_cache;
    }
  }

  public static function formatData($val){
    return $val;
  }

  public static function defaultValidationRoutines(){
    return array_merge(parent::defaultValidationRoutines(), array('MetaAddress::valid_address'));
  }

  public static function getRequiredFields(){
    return array('street','city','state','zip'); // @todo: Do not reorder this list. It contributes to display format.
  }

  public static function valid_address($val){
    foreach(self::getRequiredFields() as $field) {
      if(!isset($val[$field])) return false;
      if($field == 'zip' && !is_numeric($val[$field])) return false;
      if($field == 'zip' && strlen($val[$field]) != 5) return false;
    }
    return true;
  }

  public static function test_valid_address(){
    $cases = array(
      array('val' => array(
        'street' => '5704 Candlewood Street',
        'city' => 'West Palm Beach',
        'state' => 'FL',
        'zip' => '33407'
      ), 'assert' => true),
    );
    return $cases;
  }

  public static function get_states(){
    $states_json = '{
    "AL": "Alabama",
    "AK": "Alaska",
    "AS": "American Samoa",
    "AZ": "Arizona",
    "AR": "Arkansas",
    "CA": "California",
    "CO": "Colorado",
    "CT": "Connecticut",
    "DE": "Delaware",
    "DC": "District Of Columbia",
    "FM": "Federated States Of Micronesia",
    "FL": "Florida",
    "GA": "Georgia",
    "GU": "Guam",
    "HI": "Hawaii",
    "ID": "Idaho",
    "IL": "Illinois",
    "IN": "Indiana",
    "IA": "Iowa",
    "KS": "Kansas",
    "KY": "Kentucky",
    "LA": "Louisiana",
    "ME": "Maine",
    "MH": "Marshall Islands",
    "MD": "Maryland",
    "MA": "Massachusetts",
    "MI": "Michigan",
    "MN": "Minnesota",
    "MS": "Mississippi",
    "MO": "Missouri",
    "MT": "Montana",
    "NE": "Nebraska",
    "NV": "Nevada",
    "NH": "New Hampshire",
    "NJ": "New Jersey",
    "NM": "New Mexico",
    "NY": "New York",
    "NC": "North Carolina",
    "ND": "North Dakota",
    "MP": "Northern Mariana Islands",
    "OH": "Ohio",
    "OK": "Oklahoma",
    "OR": "Oregon",
    "PW": "Palau",
    "PA": "Pennsylvania",
    "PR": "Puerto Rico",
    "RI": "Rhode Island",
    "SC": "South Carolina",
    "SD": "South Dakota",
    "TN": "Tennessee",
    "TX": "Texas",
    "UT": "Utah",
    "VT": "Vermont",
    "VI": "Virgin Islands",
    "VA": "Virginia",
    "WA": "Washington",
    "WV": "West Virginia",
    "WI": "Wisconsin",
    "WY": "Wyoming"
}';
    return json_decode($states_json, true);
  }

}