<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends Front_Controller {

  public function login(){
    //var_dump(User::Authenticate('jahdy@spotflare.com','jahdy'));
    $redirect = '/';
    $redirect_override = $this->input->get('redirect');
    if(!logged_in()){
      // If form submitted
      $this->load->helper('api');
      $result = process_login();
      $this->page_header = array(
        'icon' => 'fa-sign-in',
        'header' => 'Login / Sign Up',
      );
      $tests = array('unprocessed' => is_null($result), 'success' => $result === true, 'failure' => $result === false);
      $data = array('redirect_url' => current_url());
      show_sidebar(false);
      //var_dump(UserSession::EncodePassword('demo'));
      switch(true){
        case $tests['unprocessed']: // Form hasn't been submitted
          break;
        case $tests['success']:
          if($redirect_override){
            redirect($redirect_override);
          } else {
            redirect($redirect);
          }
          break;
        case $tests['failure']:
          $this->message['text'] = 'Login Unsuccessful';
          $this->message['classes'] = 'alert danger boxed';
          break;
      }
      $this->view('login', $data);
    } else {
      if($redirect_override){
        redirect($redirect_override);
      } else {
        redirect($redirect);
      }
    }
  }

  public function logout(){
    if(logged_in()) logout();
    redirect('/');
  }

  public function logger(){
    $this->page_header = array(
      'icon' => 'fa-sign-in',
      'header' => 'Login / Sign Up',
    );

    $this->view('logger');
  }

  public function admin_login(){
    if(!admin_logged_in()){
      // If form submitted
      if($action = $this->input->post('form-action')){
        $this->load->helper('data_input');
        $input = $this->input->post();
        switch($action){
          case 'login-form-submitted':
            if(!empty($input['email']) && !empty($input['password'])){
              $this->load->model('admin_model');
              $response = $this->admin_model->authenticate_user($input['email'], $input['password']);
              if(!empty($response)){
                // Log user in
                //var_dump($response);
                $this->admin_model->start_user_session($response[0]);
                // Redirect to admin section
                redirect('admin');
              } else {
                // Handle Error (invalid email / password combination)
                echo 'invalid email/password combination';
              }
            } else {
              // Handle error
              echo 'email and password fields must both be set';
            }
            break;
          case 'signup-form-submitted':
            break;
        }
      }
      $this->view('admin/login');
    } else redirect('admin');
  }

  public function admin_logout(){
    if(admin_logged_in()) admin_logout();
    redirect('admin');
  }

  public function index(){
    redirect('dashboard');
  }

  public function form_tester(){
    show_sidebar(false);
    $this->view('form-tester');
  }

  public function progress($projectId){
    $project = Project::Get($projectId);
    if($project){
      $this->project = $project;
      $this->organization = Organization::Get($this->project->getValue('organizationId'));
      $this->load->view('client-view');
    } else {
      show_404();
    }
  }

  public function addDummy(){
    $add = array(
      'dateAdded' => new MongoDate(),
      'organizationId' => UserSession::Get_Organization()->id(),
      'payload' => array(
        'recipients' => array(
          'to' => array(
            array(
              'name' => 'Jahdy Lancelot',
              'email' => 'jahdy@spotflare.com'
            ),
          ),
          'cc' => array(
            array(
              'email' => 'jahdy@cosmicstrawberry.com'
            ),
          )
        ),
        'sender' => array(
          'name' => 'Jermbo',
          'email' => 'jermbo@cosmicstrawberry.com'
        ),
        'subject' => 'Test Subject',
        'text_message' => 'Test Message'
      ),
    );
    //$result = QueueItemSendEmail::AddTrigger($add);
    //$result = QueueItemSendEmail::GetUnprocessed();
    //$result = QueueItemSendEmail::ProcessUnprocessed();
    //var_dump($result);

    var_dump(queueEmail('jahdy@spotflare.com', 'jahdy@spotflare.com','This is a test subject','This is my message body'));
  }

  public function confirmations($confirmationId = null){
    $action = $this->input->get('action');
    $action = in_array($action, ['approve','deny']) ? $action : false;
    $errors = [];
    $pageData = ['action' => $action,'isProcessed' => false];
    $processed = $this->input->get('processed') === 'true';
    
    $response = WFEvents::Subscribe(
      'someTopic',
      'Bytion_RequestParser::TestScript2',
      UserSession::Get_Organization()->id(),
      'slingshot'
    );

    $response = WFEvents::Publish(
      'someTopic',
      [
        'testData' => 'fartNoise',
        'reference' => [
          'entityType' => 'project',
          'entityId' => 'shoes',
          'context' => 'test'
        ]
      ],
      UserSession::Get_Organization()->id()
    );
    //var_dump($response);

    $noActionMessage = 'Please select "Approve" or "Deny" to send confirmation.';
    $pageData['noActionMessage'] = $noActionMessage;

    // get conf id
    if ($confirmationId) {
      $confirmation = Confirmations::Get($confirmationId);
      // get conf
      if ($confirmation) {
        $pageData['confirmation'] = $confirmation;
        // If it has been processed
        if($processed || (isset($confirmation['processed']) && $confirmation['processed'])){
          // Confirm that the confirmation has been processed
          if($confirmation['processed']){
            // Inform user that they are too late
            $pageData['isProcessed'] = true;
          } else {
            // else, return user to original confirmation page
            redirect('confirmations/'.$confirmationId);
          }

        } else {
          //var_dump($confirmation);
          if (isset($confirmation['payload']) && !empty($confirmation['payload'])){
            $pageData['payload'] = $confirmation['payload'];

            if ($action) {
              if (isset($confirmation['projectId'])) {
                // check if action is true or false
                $callback = $action == 'approve' ? $confirmation['callbackYes'] : $confirmation['callbackNo'];

                // fire appropriate callback
                if (is_callable($callback)) {
                  // update receipt message & then optional redirect

                  $returned = call_user_func_array($callback, array($confirmation['projectId']));

                  Confirmations::Update($confirmationId, [
                      'callbackResponse' => json_encode($returned),
                      'processed' => true,
                      'confirmed' => ($action == 'approve')
                    ]
                  );

                  $pageData['confirmation'] = $confirmation;
                  if ($confirmation['redirect']) {
                    if (strpos($confirmation['redirect'], '{confirmationId}')) {
                      $confirmation['redirect'] = str_replace('{confirmationId}', (string)$confirmationId, $confirmation['redirect']);
                    }
                    redirect($confirmation['redirect']);
                  }

                  $defaultMsg = 'Thank you, your submission has been received.';
                  $pageData['receiptMessage'] = isset($confirmation['receiptMessage']) ? $confirmation['receiptMessage'] : $defaultMsg;

                } else {
                  $errors[] = 'ERROR024: An error has occurred while attempting to process your request; Callback invalid.';
                }
              } else {
                $errors[] = 'ERROR025: An error has occurred while attempting to process your request; Project ID invalid.';
              }
            } else {
                $errors[] = $noActionMessage;
            }
          } else {
            $errors[] = 'ERROR026: An error has occurred while attempting to process your request; Payload invalid.';
          }
        }
      } else {
        $errors[] = 'Confirmation could not be found. Please ask your administrator for help.';
      }
    } else {
      $errors[] = 'Confirmation ID provided is invalid. Please ask your administrator to resend.';
    }

    show_sidebar(false);
    $pageData['errors'] = $errors;
    $this->view('confirmations', $pageData);

  }


}