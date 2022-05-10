<?php
// http://b143-41-80-96-180.ngrok.io/USSD/index.php remember to change everytime you restart ngrok
include_once 'menu.php';
include_once 'db.php';
include_once 'user.php';
include_once 'util.php';
include_once 'sms.php';
include_once 'transaction.php';

//$IsRegistered = false;
//!$IsRegistered = true;

// Read the variables sent via POST from our API
$sessionId   = $_POST["sessionId"];
$serviceCode = $_POST["serviceCode"];
$phoneNumber = $_POST["phoneNumber"];
$text        = $_POST["text"];

$user = new User($phoneNumber);
$db = new DbConnector();
$pdo = $db->connectToDB();
$menu = new Menu();
$textArray1 = explode ("*", $text);
$level = count($textArray1);
//$text = $menu->middleware($text, $textArray1, $user, $sessionId, $pdo);


if ($text == "" ) { 
  // This is the first request. 
  // string is empty
  if (!$user->IsUserRegistered($pdo)){
   $menu->StarterMenu();
  }else { 
    // This is the first request. 
    // string is empty
    echo "CON " . $menu->UserMenu($user->readName($pdo)); 
  }    

}else if($level == 1 && !$user->IsUserRegistered($pdo)){
  //level 0 
  //user is unregistered and option 1 is selected from starter menu
  switch($textArray1[0]){
    case 1: 
        $menu->YesMenu();
    break;
    case 2:
        $menu->NoMenu();
    break;
    case 3://FAQs
       echo "END You will receive an sms shortly. Thank you for your time" ;
       $sms = new Sms($user->getPhone());
       $message = "Thank you for your interest in our tree planting campaign.\n For answers to your most frequently asked questions visit\n https://bit.ly/3KHd4Vv";
       $sms->sendSms($message);
    break;      
    default:
        echo "END Invalid Choice. Try again!";
    break;
  }

}else if($level == 1 && $user->IsUserRegistered($pdo)){ 
  //level 0
  //user is registered and option 1 is selected from starter menu
  switch($textArray1[0]){
    case 1: 
        $menu->YesRegisteredMenu();
    break;
    case 2: //FAQs after registration
      echo "END You will receive an sms shortly. Thank you for your time" ;
      $sms = new Sms($user->getPhone());
      $message = "Thank you for your interest in our tree planting campaign.\n For answers to your most frequently asked questions visit\n https://bit.ly/3KHd4Vv";
      $sms->sendSms($message);
    break;     
    default:
    echo "END Invalid Choice. Try again!";
    break;
  }   
}
// This is the registration part.

if($level == 2 && !$user->IsUserRegistered($pdo) && $textArray1[1] == 3){ 
  echo "CON Please enter your full name:\n"; 
}else if($level == 3 && !$user->IsUserRegistered($pdo) && $textArray1[1] == 3){ 
  echo "CON Please set your PIN::\n";
}else if($level == 4 && !$user->IsUserRegistered($pdo) && $textArray1[1] == 3){ 
  echo "CON Please re-enter your PIN:\n";
}else if($level == 5 && !$user->IsUserRegistered($pdo) && $textArray1[1] == 3){ 
  $name = $textArray1[2];
  $pin = $textArray1[3];
  $confirmPin = $textArray1[4];
  if($pin != $confirmPin){
    echo "END Your pins do not match. Please try again";
  }else{
    //connect to DB and register a user. 
    $user->setName($name);
    $user->setPin($pin);
    $user->setAmount(Util::$USER_AMOUNT);
    $user->register($pdo);
    $response = "END You will receive an sms shortly. Please re-enter *384*87337# to adopt";
    echo $response;
    $sms = new Sms($user->getPhone());
    $message = "Dear" . $name . ", Thank you for registering for our For Trees Club. To continue further dial *384*87337#";
    $sms->sendSms($message);
  } 
}

//Login part plus program info and FAQs
else if($level == 2){ 
  switch($textArray1[1]){
    case 1: //Program info for both Yes and No
      echo "END You will receive an sms shortly. Thank you for your time";
      $sms = new Sms($user->getPhone());
      $message = "Thank you for your interest in our tree planting campaign.\n For our program info visit\n https://bit.ly/39JDIQR";
      $sms->sendSms($message);
    break;
    case 2: //FAQs for both Yes and No
      echo "END You will receive an sms shortly. Thank you for your time";
      $sms = new Sms($user->getPhone());
      $message = "Thank you for your interest in our tree planting campaign.\n For answers to your most frequently asked questions visit\n https://bit.ly/3KHd4Vv";
      $sms->sendSms($message);
    break;
    case 3: 
      echo "CON Please enter your PIN:\n";
  } 

}

else if($level == 3 && $user->IsUserRegistered($pdo)){ // php hash comparison error
  //Pin verification
  if($user->correctPin($pdo)){
    echo "END Wrong PIN!";
  }else if(!$user->correctPin($pdo)){
    $menu->AdoptionMenu();
  }

}else if($level == 4 && $user->IsUserRegistered($pdo)){ // this part of the cord works
  //this is the confirmation menu and mpesa prompt
  switch($textArray1[3]){
    case 1:
      $amount = 1100;
      $response =  " CON Confirm donation of Ksh " .$amount. " to our project \n";
      $response .= "1. Confirm";
      echo $response;
    break;
    case 2:
      $amount = 11000;
      $response =  " CON Confirm donation of Ksh " .$amount. " to our project \n";
      $response .= "2. Confirm";
      echo $response;
    break;
    case 3:
      $amount = 110000;
      $response =  " CON Confirm donation of Ksh " .$amount. " to our project \n";
      $response .= "3. Confirm";
      echo $response;
    break;
    case 4:
      $response = "CON Enter any amount of trees you wish to donate (Each tree at Ksh 11):";
      echo $response;
    break;
  }//calculation of amount
}else if($level == 5 && $user->IsUserRegistered($pdo)){
   if($textArray1[3] == 4){ 
    $trees = $textArray1[4];
    $amount = $trees * 11;
    $response = "CON Confirm donation of Ksh " .$amount. " to our project \n";
    $response .= "1. Confirm";
    echo $response;
  }
else {
    switch($textArray1[4]){ 
      case 1:
        $amount = 1100;
        $newAmount = $user->checkAmount($pdo) + $amount;
        $txn = new Transaction($amount);
        $txn->donated($pdo, $user->readUserId($pdo), $newAmount);
        $response = "END You will receive an mpesa prompt. Please enter your pin and you will receive an sms confirming you purchase";
        echo $response;
        $sms = new Sms($user->getPhone());
        $message = "Dear " . $user->printName($pdo) . " Thank you for your donation of ". $amount ." to plant ". $amount / 11 ." trees. You are currently responsible for removing " . ($amount/11) * 12.3 ." kg of CO2 from the atmosphere annually.\n https://www.fortrees.club/about";
        $sms->sendSms($message,$phoneNumber);  
      break;
      case 2:
        $amount = 11000;
        $newAmount = $user->checkAmount($pdo) + $amount;
        $user->donated($pdo, $newAmount);
        $response = "END You will receive an mpesa prompt. Please enter your pin and you will receive an sms confirming you purchase";
        echo $response;
        $sms = new Sms($user->getPhone());
        $message = "Dear " . $user->printName($pdo) . " Thank you for your donation of ". $user->getAmount() ." to plant ". $user->getAmount() / 11 ." trees. You are currently responsible for removing " . ($user->getAmount()/11) * 12.3 ." kg of CO2 from the atmosphere annually.\n https://www.fortrees.club/about";
        $sms->sendSms($message,$phoneNumber); 
      break;
      case 3:
        $amount = 110000;
        $newAmount = $user->checkAmount($pdo) + $amount;
        $user->donated($pdo, $newAmount);
        $response = "END You will receive an mpesa prompt. Please enter your pin and you will receive an sms confirming you purchase";
        echo $response;
        $sms = new Sms($user->getPhone());
        $message = "Dear " . $user->printName($pdo) . " Thank you for your donation of Ksh ". $amount ." to plant ". $amount / 11 ." trees. You are currently responsible for removing " . ($user->checkAmount($pdo)/11) * 12.3 ." kg of CO2 from the atmosphere annually.\n https://www.fortrees.club/about";
        $sms->sendSms($message,$phoneNumber);
      break;
    
  }}
}else if ($level == 6 && $user->IsUserRegistered($pdo)){
  if ($textArray1[5] == 1){
    $newAmount = $user->checkAmount($pdo) + $user->getAmount();
    $user->donated($pdo, $newAmount);
    print_r($user->getAmount()) ;
    echo "END You will receive an mpesa prompt. Please enter your pin and you will receive an sms confirming you purchase";
    $sms = new Sms($user->getPhone());
    $message = "Dear " . $user->printName($pdo) . " Thank you for your donation of ". $user->getAmount() ." to plant ". $user->getAmount() / 11 ." trees. You are currently responsible for removing " . ($user->getAmount()/11) * 12.3 ." kg of CO2 from the atmosphere annually.\n https://www.fortrees.club/about";
    $sms->sendSms($message,$phoneNumber); 
  }
}    
//}
// Echo the response back to the API
//header('Content-type: text/plain');
//echo $response;
?>