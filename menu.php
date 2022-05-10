<?php
  include_once 'util.php';
  include_once 'user.php';

  class Menu{
    protected $text;
    Protected $sessionId;
    protected $textArray1;

    function  __construct(){}

    public function StarterMenu(){ 
      //Starter menu
      $response  = "CON Welcome to Adopt a Tree. Are you ready? \n";
      $response .= "1. Yes \n";
      $response .= "2. No \n";
      $response .= "3. FAQs \n";
      echo $response;
    }

    public function UserMenu($name){ 
      //Starter menu
      $response  = "Welcome back, " . $name. " ,to Adopt a Tree. Are you ready? \n";
      $response .= "1. Proceed to Adopt\n";
      $response .= "2. FAQs \n";
      return $response;
    }
      
    public function YesMenu(){
      //Yes Reply
      $response  = "CON Amazing Initiative. Please choose your next step \n";
      $response .= "1. Learn about our program \n";
      $response .= "2. FAQs \n";
      $response .= "3. Register to Adopt a Tree\n";
      echo $response;
    }

    public function YesRegisteredMenu(){
      //Yes Reply
      $response  = "CON Amazing Initiative. Please choose your next step \n";
      $response .= "1. Learn about our program \n";
      $response .= "2. FAQs \n";
      $response .= "3. Login and Adopt a Tree";
      echo $response;
    }
       
    public function NoMenu(){
      //No Reply
      $response  = "CON Would you like to know more about us? \n";
      $response .= "1. Learn about our program \n";
      $response .= "2. FAQs";
      echo $response;
    }
      
    public function AdoptionMenu(){ //works
      //Note how we start the response with CON 
      //registeres user menu 
      $response  = "CON Please choose the amount you wish to donate \n";
      $response .= "1. Ksh 1,100 (100 Mangrooves planted)  \n";
      $response .= "2. Ksh 11,000 (1,000 Mangrooves planted)\n";
      $response .= "3. Ksh 110,000 (10,000 Mangrooves planted)\n";
      $response .= "4. Enter any amount of trees you wish to donate (Each tree at Ksh 11)";
      echo $response;
    }

    public function persistInvalidEntry($sessionId, $user, $ussdLevel, $pdo){
      $stmt = $pdo->prepare("INSERT INTO ussdsession (sessionId, uid, ussdLevel) values(?,?,?)");
      $stmt->execute([$sessionId, $user->readUserId($pdo), $ussdLevel]);
      $stmt = null; 
    }

    public function invalidEntry($ussdStr, $user, $sessionId, $pdo){
      $stmt = $pdo->prepare("SELECT ussdLevel FROM ussdsession WHERE sessionId=? AND uid=?");
      $stmt->execute([$sessionId, $user->readUserId($pdo)]);
      $result = $stmt->fetchAll();
      if (count($result) == 0){
        return $ussdStr;
      }
        $strArray = explode("*", $ussdStr);
      
        foreach($result as $value){
          unset($strArray[$value['ussdLevel']]);
        }
        $strArray = array_values($strArray);
        return join("*", $strArray);
    }

    public function addCCode($phone){
      return Util::$COUNTRY_CODE . substr($phone, 1);
    }
    //code works till here

    public function middleware($text, $textArray1, $user, $sessionId, $pdo){
      //remove entries for going back and going to the main menu
      return $this->invalidEntry($this->goBack($this->goToMainMenu($text), $textArray1), $user, $sessionId, $pdo);
    }

    public function goBack($text, $textArray1){
      //1*3*full name*pin*confirmpin*98*YesMenu
      $explodedText = explode("*", $text);
      while(array_search(Util::$GO_BACK, $explodedText) != false){
        $firstIndex = array_search(Util::$GO_BACK, $explodedText);
        $explodedText = array_slice($explodedText, $firstIndex +1);
      } 
      return join ("*", $explodedText);
    }

    public function goToMainmenu($text){
      //1*3*full name*pin*confirmpin*99*Adoption menu
      $explodedText = explode("*", $text);
      while(array_search(Util::$GO_TO_MAIN_MENU, $explodedText) != false){
        $firstIndex = array_search(Util::$GO_TO_MAIN_MENU, $explodedText);
        $explodedText = array_splice($explodedText, $firstIndex -1, 4);
      }

      return join ("*", $explodedText);
    }

    public function registerMenu($textArray, $phoneNumber){
      //building menu for user registration 
        $level = count($textArray);
       if($level == 1){
            echo "CON Please enter your full name:";
       } else if($level == 2){
            echo "CON Please enter set you PIN:";
       }else if($level == 3){
            echo "CON Please re-enter your PIN:";
       }else if($level == 4){
            $name = $textArray[1];
            $pin = $textArray[2];
            $confirmPin = $textArray[3];
            if($pin != $confirmPin){
                echo "END Your pins do not match. Please try again";
            }else{
                //connect to DB and register a user. 
                echo "END You have been registered";
                #$sms = new Sms();
                #$message = "You have been registered";
                #$sms->sendSms($message,$phoneNumber);
                
            }
       }
    }
    
  }



?>