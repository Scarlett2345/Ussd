<?php
  class Transaction{
      protected $amount;

      function __construct($amount){
          $this->amount = $amount;
      }

      public function getAmount(){
        return $this->amount;
       }

       public function donated($pdo, $uid, $newAmount){
        $pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE);
        try {
            $pdo->beginTransaction();
            $stmtT = $pdo->prepare("INSERT INTO transaction (amount, uid) values(?,?)");
            $stmtU = $pdo->prepare("UPDATE user SET amount=? WHERE uid=?");

            $stmtT->execute([$this->getAmount(), $uid]);
            $stmtU->execute([$newAmount, $uid]);
            
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return "an error has occured";
        }
    }
  }


?>