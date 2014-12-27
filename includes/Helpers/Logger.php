<?php

/**
 * Logger short summary.
 *
 * Logger description.
 *
 * @version 1.0
 * @author stwalkerster
 */
class Logger
{
    private static function CreateLogEntry(PdoDatabase $database, DataObject $object, $logaction, $comment = null)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction($logaction);
        $log->setObjectId($object->getId());
        $log->setObjectType(get_class($object));
        $log->setUser(User::getCurrent());
        $log->setComment($comment);
        $log->save();
    }
    
    public static function EmailConfirmed(PdoDatabase $database, Request $object)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("Email Confirmed");
        $log->setObjectId($object->getId());
        $log->setObjectType(get_class($object));
        $log->setUser(User::getCommunity());
        $log->setComment(null);
        $log->save();
    }

    #region Users
    /**
     * I don't like this. --stw
     * 
     * I'd prefer it split into different calls instead of passing logaction
     * @deprecated
     */
    public static function UserStatusChange(PdoDatabase $database, User $object, $comment, $logaction)
    {
        self::CreateLogEntry($database, $object, $logaction, $comment);
    }
    
    public static function RenamedUser(PdoDatabase $database, User $object, $comment)
    {
        self::CreateLogEntry($database, $object, "Renamed", $comment);
    }
    
    public static function UserPreferencesChange(PdoDatabase $database, User $object)
    {
        self::CreateLogEntry($database, $object, "Prefchange");
    }
    
    #endregion
    
    public static function InterfaceMessageEdited(PdoDatabase $database, InterfaceMessage $object)
    {
        self::CreateLogEntry($database, $object, "Edited");
    }
    
    #region Welcome Templates
    
    public static function WelcomeTemplateCreated(PdoDatabase $database, WelcomeTemplate $object)
    {
        self::CreateLogEntry($database, $object, "CreatedTemplate");
    }
    
    public static function WelcomeTemplateEdited(PdoDatabase $database, WelcomeTemplate $object)
    {
        self::CreateLogEntry($database, $object, "EditedTemplate");
    }
    
    public static function WelcomeTemplateDeleted(PdoDatabase $database, WelcomeTemplate $object)
    {
        self::CreateLogEntry($database, $object, "DeletedTemplate");
    }
    
    #endregion
    
    #region Bans
    
    public static function Banned(PdoDatabase $database, Ban $object, $reason)
    {
        self::CreateLogEntry($database, $object, "Banned", $reason);
    }
    
    public static function Unbanned(PdoDatabase $database, Ban $object, $reason)
    {
        self::CreateLogEntry($database, $object, "Unbanned", $reason);
    }
    
    #endregion
    
    #region Requests
    
    public static function DeferRequest(PdoDatabase $database, Request $object, $target)
    {
        self::CreateLogEntry($database, $object, "Deferred to $target");
    }
    
    public static function CloseRequest(PdoDatabase $database, Request $object, $target, $comment)
    {
        self::CreateLogEntry($database, $object, "Closed $target", $comment);
    }
    
    public static function Reserve(PdoDatabase $database, Request $object)
    {
        self::CreateLogEntry($database, $object, "Reserved");
    }
    
    public static function BreakReserve(PdoDatabase $database, Request $object)
    {
        self::CreateLogEntry($database, $object, "BreakReserve");
    }
    
    public static function Unreserve(PdoDatabase $database, Request $object)
    {
        self::CreateLogEntry($database, $object, "Unreserved");
    }
    
    public static function EditComment(PdoDatabase $database, Comment $object)
    {
        self::CreateLogEntry($database, $object->getRequestObject(), "EditComment-r");
        self::CreateLogEntry($database, $object, "EditComment-c");
    }

    public static function SendReservation(PdoDatabase $database, Request $object, User $target)
    {
        self::CreateLogEntry($database, $object, "SendReserved");
        
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("ReceiveReserved");
        $log->setObjectId($object->getId());
        $log->setObjectType(get_class($object));
        $log->setUser($target->getId());
        $log->setComment(null);
        $log->save();
    }

    #endregion
    
    #region Email templates
    
    public static function CreateEmail(PdoDatabase $database, EmailTemplate $object)
    {
        self::CreateLogEntry($database, $object, "CreatedEmail");
    }
    
    public static function EditedEmail(PdoDatabase $database, EmailTemplate $object)
    {
        self::CreateLogEntry($database, $object, "EditedEmail");
    }
    
    #endregion

}
