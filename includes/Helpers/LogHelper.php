<?php

/**
 * LogHelper short summary.
 *
 * LogHelper description.
 *
 * @version 1.0
 * @author stwalkerster
 */
class LogHelper
{
    const OBJECT_USER = "User";
    const OBJECT_REQUEST = "Request";
    const OBJECT_BAN = "Ban";
    const OBJECT_IMESSAGE = "InterfaceMessage";
    const OBJECT_COMMENT = "Comment";
    const OBJECT_EMAIL = "Email";
    const OBJECT_TEMPLATE = "Template";

    
    const ACTION_EMAILCONF = "Email Confirmed";
    const ACTION_EDITED = "Edited";
    
    
    public static function EmailConfirmed(PdoDatabase $database, Request $request)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("Email Confirmed");
        $log->setObjectId($request->getId());
        $log->setObjectType(self::OBJECT_REQUEST);
        $log->setUser(User::getCommunity());
        $log->setComment(null);
        $log->save();
    }

    #region Users
    /**
     * I don't like this. --stw
     * 
     * @deprecated
     */
    public static function UserStatusChange(PdoDatabase $database, User $object, $comment, $logaction)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction($logaction);
        $log->setObjectId($object->getId());
        $log->setObjectType(self::OBJECT_USER);
        $log->setUser(User::getCurrent());
        $log->setComment($comment);
        $log->save();
    }
    
    public static function RenamedUser(PdoDatabase $database, User $object, $comment)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("Renamed");
        $log->setObjectId($object->getId());
        $log->setObjectType(self::OBJECT_USER);
        $log->setUser(User::getCurrent());
        $log->setComment($reason);
        $log->save();
    }
    
    public static function UserPreferencesChange(PdoDatabase $database, User $object)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("Prefchange");
        $log->setObjectId($object->getId());
        $log->setObjectType(self::OBJECT_USER);
        $log->setUser(User::getCurrent());
        $log->setComment($reason);
        $log->save();
    }
    
    #endregion
    
    public static function InterfaceMessageEdited(PdoDatabase $database, InterfaceMessage $object)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("Edited");
        $log->setObjectId($object->getId());
        $log->setObjectType(self::OBJECT_IMESSAGE);
        $log->setUser(User::getCurrent());
        $log->setComment(null);
        $log->save();
    }
    
    #region Welcome Templates
    
    public static function WelcomeTemplateCreated(PdoDatabase $database, WelcomeTemplate $object)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("CreatedTemplate");
        $log->setObjectId($object->getId());
        $log->setObjectType(self::OBJECT_TEMPLATE);
        $log->setUser(User::getCurrent());
        $log->setComment(null);
        $log->save();
    }
    
    public static function WelcomeTemplateEdited(PdoDatabase $database, WelcomeTemplate $object)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("EditedCreatedTemplate");
        $log->setObjectId($object->getId());
        $log->setObjectType(self::OBJECT_TEMPLATE);
        $log->setUser(User::getCurrent());
        $log->setComment(null);
        $log->save();
    }
    
    public static function WelcomeTemplateDeleted(PdoDatabase $database, WelcomeTemplate $object)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("DeletedTemplate");
        $log->setObjectId($object->getId());
        $log->setObjectType(self::OBJECT_TEMPLATE);
        $log->setUser(User::getCurrent());
        $log->setComment(null);
        $log->save();
    }
    
    #endregion
    
    #region Bans
    
    public static function Banned(PdoDatabase $database, Ban $object, $reason)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("Banned");
        $log->setObjectId($object->getId());
        $log->setObjectType(self::OBJECT_BAN);
        $log->setUser(User::getCurrent());
        $log->setComment($reason);
        $log->save();
    }
    
    public static function Unbanned(PdoDatabase $database, Ban $object, $reason)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("Unbanned");
        $log->setObjectId($object->getId());
        $log->setObjectType(self::OBJECT_BAN);
        $log->setUser(User::getCurrent());
        $log->setComment($reason);
        $log->save();
    }
    
    #endregion
    
    #region Requests
    
    public static function DeferRequest(PdoDatabase $database, Request $object, $target)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("Deferred to $target");
        $log->setObjectId($object->getId());
        $log->setObjectType(self::OBJECT_REQUEST);
        $log->setUser(User::getCurrent());
        $log->setComment(null);
        $log->save();
    }
    
    public static function CloseRequest(PdoDatabase $database, Request $object, $target, $comment)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("Closed $target");
        $log->setObjectId($object->getId());
        $log->setObjectType(self::OBJECT_REQUEST);
        $log->setUser(User::getCurrent());
        $log->setComment($comment);
        $log->save();
    }
    
    public static function Reserve(PdoDatabase $database, Request $object)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("Reserved");
        $log->setObjectId($object->getId());
        $log->setObjectType(self::OBJECT_REQUEST);
        $log->setUser(User::getCurrent());
        $log->setComment(null);
        $log->save();
    }
    
    public static function BreakReserve(PdoDatabase $database, Request $object)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("BreakReserve");
        $log->setObjectId($object->getId());
        $log->setObjectType(self::OBJECT_REQUEST);
        $log->setUser(User::getCurrent());
        $log->setComment(null);
        $log->save();
    }
    
    public static function Unreserve(PdoDatabase $database, Request $object)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("Unreserved");
        $log->setObjectId($object->getId());
        $log->setObjectType(self::OBJECT_REQUEST);
        $log->setUser(User::getCurrent());
        $log->setComment(null);
        $log->save();
    }
    
    public static function EditComment(PdoDatabase $database, Comment $object)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("EditComment-r");
        $log->setObjectId($object->getRequest());
        $log->setObjectType(self::OBJECT_REQUEST);
        $log->setUser(User::getCurrent());
        $log->setComment(null);
        $log->save();
        
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("EditComment-c");
        $log->setObjectId($object->getId());
        $log->setObjectType(self::OBJECT_COMMENT);
        $log->setUser(User::getCurrent());
        $log->setComment(null);
        $log->save();
    }

    public static function SendReservation(PdoDatabase $database, Request $object, User $target)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("SendReserved");
        $log->setObjectId($object->getId());
        $log->setObjectType(self::OBJECT_REQUEST);
        $log->setUser(User::getCurrent());
        $log->setComment(null);
        $log->save();
        
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("ReceiveReserved");
        $log->setObjectId($object->getId());
        $log->setObjectType(self::OBJECT_REQUEST);
        $log->setUser($target->getId());
        $log->setComment(null);
        $log->save();
    }

    #endregion
    
    #region Email templates
    
    public static function CreateEmail(PdoDatabase $database, EmailTemplate $object)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("CreatedEmail");
        $log->setObjectId($object->getId());
        $log->setObjectType(self::OBJECT_EMAIL);
        $log->setUser(User::getCurrent());
        $log->setComment(null);
        $log->save();
    }
    
    public static function EditedEmail(PdoDatabase $database, EmailTemplate $object)
    {
        $log = new Log();
        $log->setDatabase($database);
        $log->setAction("EditedEmail");
        $log->setObjectId($object->getId());
        $log->setObjectType(self::OBJECT_EMAIL);
        $log->setUser(User::getCurrent());
        $log->setComment(null);
        $log->save();
    }
    
    #endregion

}
