<?php
if (!defined("ACC")) {
    die();
} // Invalid entry point

class SessionAlert
{
    private $message;
    private $title;
    private $type;
    private $closable;
    private $block;
    
    public function __construct($message, $title, $type = "alert-info", $closable = true, $block = true)
    {
        $this->message = $message;
        $this->title = $title;
        $this->type = $type;
        $this->closable = $closable;
        $this->block = $block;
    }
    
    public function getAlertBox()
    {
        return BootstrapSkin::displayAlertBox($this->message, $this->type, $this->title, $this->block, $this->closable, true);
    }
    
    public static function append(SessionAlert $alert)
    {
        $data = array();
        if( isset($_SESSION['alerts']) )
        {
            $data = $_SESSION['alerts'];
        }
        
        $data[] = serialize( $alert );
    }
    
    public static function retrieve()
    {
        $block = array();
        if(isset($_SESSION['alerts'])) 
        {
            foreach($_SESSION['alerts'] as $a)
            {
                $block[] = unserialize($a);
            }
        }
        
        $_SESSION['alerts'] = array();
        
        return $block;
    }
}
