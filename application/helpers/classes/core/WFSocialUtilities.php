<?php

require_once 'WFSocialUtilitiesTwitter.php';

class WFSocialUtilities
{

  private static $_TWITTER = null;

  public function __construct(){
    self::Twitter();
  }

  public static function Twitter(){
    if(!self::$_TWITTER) self::$_TWITTER = new WFSocialUtilitiesTwitter();
    return self::$_TWITTER;
  }

  public static function FraudAnalysis($payload){
    $logger = new WFLogger(__METHOD__, __FILE__);
    $logger->addDebug('Entering ...');
    $response = WFClientInterface::GetPayloadTemplate();

    $apiUrl = 'http://dottedmap.com/api/v1/blacklist/scrutinize';

    CI()->load->library('Curl');

    $returnJson = CI()->curl->simple_post($apiUrl, $payload);

    if(WFClientInterface::Valid_JSON($returnJson)){
      $logger->addDebug('Return json', $returnJson);
      $returnData = json_decode($returnJson, true);
      if(!$returnData['errors']){
          $logger->addDebug('No errors from API');
          $response['response']['success'] = true;
          $response['response']['data'] = $returnData['response'];

      } else {
        foreach((array) $returnData['errors'] as $error){
          $logger->addError((string) $error);
        }
      }
    } else {
      echo '<pre>';
      echo($returnJson);
      echo '</pre>';
      $logger->addError('Invalid response format from Fraud Analyzer API');
    }

    $response['logs'] = $logger->getLogsArray();
    $logger->sync();
    $response['logger'] = $logger;
    $response['errors'] = $logger->hasErrors(__FUNCTION__);
    return $response;
  }



}