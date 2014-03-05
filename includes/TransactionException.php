<?php
if (!defined("ACC")) {
    die();
} // Invalid entry point

class TransactionException extends Exception
{
    private $title;
    private $alertType;
    
    public function __construct($message, $title = "Error occured during transaction", $alertType = "alert-error", $code = 0, Exception $previous = null) 
    {
        $this->title = $title;
        $this->alertType = $alertType;
        parent::__construct($message, $code, $previous);
    }
    
    public function getTitle()
    {
        return $this->title;   
    }
    
    public function getAlertType()
    {
        return $this->alertType;   
    }
}
