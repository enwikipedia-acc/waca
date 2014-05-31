<?php

namespace Waca\API\Actions;

use Waca\API\ApiException as ApiException;
use Waca\API\ApiActionBase as ApiActionBase;
use Waca\API\IApiAction as IApiAction;

/**
 * API Count action
 */
class StatusAction extends ApiActionBase implements IApiAction
{
    /**
     * The datbase
     * @var PdoDatabase $database
     */
    private $database;
    
    public function execute(\DOMElement $doc_api)
    {
        $this->database = gGetDb();
        
        $statusElement = $this->document->createElement("status");
        $doc_api->appendChild($statusElement);
		
        $mailconfirm = "Confirmed";			
        $query = $this->database->prepare("SELECT COUNT(*) AS count FROM acc_pend WHERE pend_status = :pstatus AND pend_mailconfirm = :pmailconfirm;");
        $query->bindValue(":pmailconfirm", $mailconfirm);
        
        global $availableRequestStates;
        foreach( $availableRequestStates as $key => $value ) 
        {
            $query->bindValue(":pstatus", $key);
            $query->execute();
            $sus = $query->fetchColumn();
            $statusElement->setAttribute($value['api'], $sus);
            $query->closeCursor();
        }

        $query = $this->database->prepare("SELECT COUNT(*) AS count FROM ban WHERE (duration > UNIX_TIMESTAMP() OR duration = -1) AND active = 1;");
        $query->execute();
        $sus = $query->fetchColumn();
        $statusElement->setAttribute("bans", $sus);
        $query->closeCursor();

        $query = $this->database->prepare("SELECT COUNT(*) AS count FROM user WHERE status = :ulevel;");
        $query->bindValue(":ulevel", "Admin");
        $query->execute();
        $sus = $query->fetchColumn();
        $statusElement->setAttribute("useradmin", $sus);
        $query->closeCursor();
        
        $query->bindValue(":ulevel", "User");
        $query->execute();
        $sus = $query->fetchColumn();
        $statusElement->setAttribute("user", $sus);
        $query->closeCursor();
        
        $query->bindValue(":ulevel", "New");
        $query->execute();
        $sus = $query->fetchColumn();
        $statusElement->setAttribute("usernew", $sus);
        $query->closeCursor();
        
        return $doc_api;
    }
}
