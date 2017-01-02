<?php

require APPPATH.'third_party/aws-autoloader.php';

//date_default_timezone_set('UTC');

use Aws\DynamoDb\Exception\DynamoDbException;

$sdk = new Aws\Sdk(array(
  //'endpoint' => 'http://localhost:8000',
  'region'   => 'us-west-2',
  'version'  => 'latest',
));

global $dynamodb;

$dynamodb = $sdk->createDynamoDb();

function addToDynamoDBTable($tableName, $data, $dataMap = array()){
  global $dynamodb;

  $validate = validateDynamoDbData($data, $dataMap);

  if($validate['isValid']){
    $response = $dynamodb->putItem([
      'TableName' => $tableName,
      'Item' => $validate['validated'],
      'ReturnConsumedCapacity' => 'TOTAL',
    ]);
  }

  $response['_item'] = $validate['validated'];

  return $response;
}

function getAllFromDynamoDBTable($tableName){
  global $dynamodb;

  $response = $dynamodb->scan([
    'TableName' => $tableName,
  ]);

  return $response;
}

function getByIdFromDynamoDBTable($tableName, $id){
  global $dynamodb;

  $response = $dynamodb->getItem([
    'TableName' => $tableName,
    'ConsistentRead' => true,
    'Key' => [
      'id' => [
        'S' => $id
      ]
    ],
    'ProjectionExpression' => 'Id, ISBN, Title, Authors'
  ]);

  return $response;
}

function validateDynamoDbData($data, $dataMap = array()){
  $return = [
    'validated' => array(),
    'errors' => array(),
    'isValid' => false
  ];

  foreach($data as $field => $saveData){
    if(is_array($saveData)){
      foreach($saveData as $dataType => $value){
        if(in_array($dataType, array('S','N','SS','NS','BOOL'))){
          if(isset($dataMap[$field])){
            $return['validated'][$field] = array($dataMap[$field] => $value);
          } else {
            $return['validated'][$field] = array($dataType => $value);
          }
        } else {
          $return['validated'][$field] = array('S' => $value);
        }
      }
    } else {
      $return['validated'][$field] = array('S' => $saveData);
    }
  }

  if(!isset($return['validated']['id'])) $return['validated']['id'] = array('S' => md5(json_encode($data)).md5(time()));

  if(empty($return['errors']) && !empty($return['validated'])) $return['isValid'] = true;
  return $return;
}

//$response = addToDynamoDBTable('send_email', array('farts' => 'smelly', 'list' => array('SS' => array('string 1','string 2'))));
//if(isset($response['_item'])) var_dump($response);
//var_dump(getAllFromDynamoDBTable('send_email'));
//var_dump(getByIdFromDynamoDBTable('send_mail','65eb62fc9832623ca94c0530a547f0963d3e74d4b26e6caca2ce3db004f56907'));

function queueEmail($recipients, $sender, $subject, $text_message, $html_message = null, $carbonCopy = null, $blindCarbonCopy = null, array $caller = null){
  $add = array('dateAdded' => new MongoDate(), 'organizationId' => UserSession::Get_Organization()->id());

  $payload['organizationId'] = (string) $add['organizationId'];
  $payload['recipients'] = array(
    'to' => QueueItemSendEmail::ParseEmailRecipients($recipients),
    'cc' => QueueItemSendEmail::ParseEmailRecipients($carbonCopy),
    'bcc' => QueueItemSendEmail::ParseEmailRecipients($blindCarbonCopy),
  );

  foreach($payload['recipients'] as $group => $recipients) if(!isset($recipients)) unset($payload['recipients'][$group]);

  $sender = QueueItemSendEmail::ParseEmailRecipients($sender);
  $payload['sender'] = isset($sender[0]) ? $sender[0] : null;
  $payload['subject'] = $subject;
  $payload['text_message'] = $text_message;
  $payload['html_message'] = $html_message;
  if($caller){
    foreach(array('organizationId','projectId','taskId','userId') as $field) if(isset($caller[$field])){
      $payload[$field] = (string) $caller[$field];
    }
  }

  $add['payload'] = $payload;

  return QueueItemSendEmail::AddTrigger($add);
}

function queueSMS($recipients, $message, $caller = null){

}

function queueFormEmail(WFForms $form){

}

function queueValidateTwitter($twitter_handle, $caller = null){

}