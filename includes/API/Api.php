<?php

namespace Waca\API;

/**
 * API
 */
class Api
{    
    public function __construct()
    {
        header("Content-Type: text/xml");

        // javascript access control
        if(isset($_SERVER['HTTP_ORIGIN']))
        {
            if(in_array($_SERVER['HTTP_ORIGIN'], $CORSallowed))
            {
                header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
            }
        }
    }
    
    public function execute()
    {
        // get the request action, defaulting to help
        $requestAction = "";
        if(isset($_GET['action']))
        {
            $requestAction = $_GET['action'];
        }
        
        switch ($requestAction)
        {
        	case "count":
                $result = new Actions\CountAction();
                $data = $result->run();
                break;
        	case "status":
                $result = new Actions\StatusAction();
                $data = $result->run();
                break;
        	case "stats":
                $result = new Actions\StatsAction();
                $data = $result->run();
                break;
        	case "help":
                $result = new Actions\HelpAction();
                $data = $result->run();
                break;
        	default:
                $result = new Actions\UnknownAction();
                $data = $result->run();
                break;
        }
        
        return $data;
    }
    
    public static function getActionList()
    {
        return array( "count", "status", "stats", "help" );
    }
}
