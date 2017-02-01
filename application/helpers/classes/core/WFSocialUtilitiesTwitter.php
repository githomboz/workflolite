<?php

class WFSocialUtilitiesTwitter
{

  public function __construct(){
  }

  public static function SimpleValidate($payload){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->addDebug('Entering ...');
    $response = WFClientInterface::GetPayloadTemplate();

    if(isset($payload['twitterHandle']) && !empty($payload['twitterHandle'])){
      $apiUrl = 'http://dottedmap.com/api/v1/validator/parse_get_source?source=twitter|@'.str_replace('@','',$payload['twitterHandle']);

      CI()->load->library('Curl');

      $returnJson = CI()->curl->simple_get($apiUrl);

      if(WFClientInterface::Valid_JSON($returnJson)){
        $logger->addDebug('Return json', $returnJson);
        //$response['response']['returnJSON'] = json_decode($returnJson, true);
        $returnData = json_decode($returnJson, true);
        if(!$returnData['errors'] && isset($returnData['response']['validated'])){
          $logger->addDebug('No errors from API');
          if(isset($returnData['response']['validated'][0])){
            $logger->addDebug('Validated data', $returnData['response']['validated'][0]);
            $response['response']['success'] = true;
            $response['response']['data'] = $returnData['response']['validated'][0];
          } else {
            $logger->addError('No data found for the given social account');
          }
        } else {
          foreach((array) $returnData['errors'] as $error){
            $logger->addError((string) $error);
          }
        }
      } else {
        echo '<pre>';
        echo($returnJson);
        echo '</pre>';
        $logger->addError('Invalid response format from Social Validator API');
      }

    } else {
      $logger->addError('Twitter handle invalid');
    }

    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }



}