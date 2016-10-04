<?php

function get_location_data($address){
  $api = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=';
  $address = urlencode($address);
  $CI =& get_instance();
  $CI->load->helper('views');
  $response = curl_get($api.$address);
  if(!empty($response)){
    $data = json_decode($response);
    //var_dump($data);
    if(!empty($data) && isset($data->results[0]->geometry->location->lat)){
      $rtn = array(
        'full' => $data->results[0]->formatted_address,
        'latitude' => $data->results[0]->geometry->location->lat,
        'longitude' => $data->results[0]->geometry->location->lng,
      );
      if(isset($data->results[0]->address_components)){
        foreach((array)$data->results[0]->address_components as $i => $comp){
          foreach(array('street_number'=>'street_number','route'=>'street_name','locality'=>'city','administrative_area_level_2'=>'county','administrative_area_level_1'=>'state','postal_code'=>'zip') as $dp => $nice_name)
            if(in_array($dp, $comp->types)) {
              if($nice_name == 'state'){
                $rtn[$nice_name] = $comp->short_name;
              } else {
                $rtn[$nice_name] = $comp->long_name;
              }
            }
        }
      }
      if(isset($rtn['street_number']) && isset($rtn['street_name'])) $rtn['address'] = $rtn['street_number'] . ' ' . $rtn['street_name'];
      return $rtn;
    };
  }
  return false;
}

function distance_between_coordinates($lat1, $lon1, $lat2, $lon2, $unit = 'm') {
  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);

  switch(strtolower($unit)){
    case 'k':
    case 'kilometers':
      return ($miles * 1.609344);
      break;
    case 'n':
    case 'nautical miles':
      return ($miles * 0.8684);
      break;
    default: return $miles;
      break;
  }
}
