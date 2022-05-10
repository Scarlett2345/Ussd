<?php
    require 'vendor/autoload.php';
    use AfricasTalking\SDK\AfricasTalking;
    include_once 'util.php';
    include_once 'db.php';

    class Sms {        
        protected $AT;
        protected $phone;
        
        function __construct($phone)
        {
            $this->phone = $phone;
            $this->AT = new AfricasTalking(Util::$API_USERNAME, Util::$API_KEY);
        }

        public function getPhone(){
            return $this->phone;
        }

        public function sendSms($message){
            //get the sms service
            //$sms = $this->AT->sms();
            $sms = $this->AT->sms();
            //use the SMS service to send SMS
            $result = $sms->send([
                'to'      => $this->getPhone(),
                'message' => $message,
                'from'    => Util::$COMPANY_NAME
            ]);
            return $result;
        }
    }
?>