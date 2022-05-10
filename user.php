<?php
   class User{
       protected $name;
       protected $phone;
       protected $pin;
       protected $amount;
       protected $uid;

       function __construct($phone)
       {
           $this ->phone = $phone;
       }

       //setters and getters
       public function setName($name){
           $this->name = $name;
       }

       public function getName(){
         return $this->name;
       }

       public function getPhone(){
        return $this->phone;
       }

       public function setPin($pin){
        $this->pin = $pin;
       }

       public function getPin(){
        return $this->pin;
       }

       public function setAmount($amount){
        $this->amount = $amount;
       }

       public function getAmount(){
        return $this->amount;
       }

       public function register($pdo){
           try {
               //hash the pin
               $hashedpin = password_hash($this->getPin(), PASSWORD_DEFAULT);
               $stmt = $pdo->prepare("INSERT INTO user (name, pin, phone, amount) values(?,?,?,?)");
               $stmt->execute([$this->getName(),$hashedpin,$this->getPhone(), $this->getAmount()]);
           } catch (PDOException $e) {
               echo $e->getMessage();
           }
       }

       public function IsUserRegistered($pdo){
           $stmt = $pdo->prepare("SELECT * FROM user WHERE phone=?");
           $stmt->execute([$this->getPhone()]);
           if(count($stmt->fetchAll()) > 0){
               return true;
           }else{
               return false;
           }
       }

       public function readName($pdo){
        $stmt = $pdo->prepare("SELECT * FROM user WHERE phone=?");
        $stmt->execute([$this->getPhone()]);
        $row = $stmt->fetch();
        return $row['name'];
       }
    
       public function printName($pdo){
        $stmt = $pdo->prepare("SELECT * FROM user WHERE phone=?");
        $stmt->execute([$this->getPhone()]);
        $row = $stmt->fetch();
        echo $row['name'];
        return $row['name'];
       }

       public function readUserId($pdo){
           $stmt = $pdo->prepare("SELECT uid FROM user WHERE phone=?");
           $stmt->execute([$this->getPhone()]);
           $row = $stmt->fetch();
           $this->uid = $row['uid'];
           return $row['uid'];
       }

       public function getUId(){
        return $this->uid;
      }

       public function readTId($pdo){
        $stmt = $pdo->prepare("SELECT tid FROM transaction WHERE uid=?");
        $stmt->execute([$this->readUserId($pdo)]);
        $row = $stmt->fetch();
        return $row['tid'];
       }



       public function correctPin($pdo){
           $stmt = $pdo->prepare("SELECT pin FROM user WHERE phone=?");
           $stmt->execute([$this->getPhone()]);
           $row = $stmt->fetch();
           if ($row == null){
               return false;
           }
           if (password_verify($this->getPin(), $row['pin'])){
               return true;
           }
          // return false;
       }

       public function checkAmount($pdo){
        $stmt = $pdo->prepare("SELECT amount FROM user WHERE phone=?");
        $stmt->execute([$this->getPhone()]);
        $row = $stmt->fetch();
        return $row['amount'];
       }


   }




?>