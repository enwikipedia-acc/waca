<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

class User extends DataObject
{
    private $username;
    private $email;
    private $password;
    private $status;
    private $onwikiname;
    private $welcome_sig;
    private $lastactive;
    private $forcelogout;
    private $secure;
    private $checkuser;
    private $identified;
    private $welcome_template;
    private $abortpref;
    private $confirmationdiff;
    private $emailsig;
    
    // cache variable of the current user - it's never going to change in the middle of a request.
    private static $currentUser;
    
    public static function getCurrent(PdoDatabase $database = null)
    {
        if($database === null)
        {
            $database = gGetDb();   
        }
        
        if(User::$currentUser === null)
        {
            User::$currentUser = User::getById($_SESSION['userID'], $database);
        }
        
        return User::$currentUser;
    }
    
    public static function getByUsername($username, PdoDatabase $database)
    {
        $statement = $database->prepare("SELECT * FROM `" . strtolower( get_called_class() ) . "` WHERE username = :id LIMIT 1;");
		$statement->bindParam(":id", $username);

		$statement->execute();

		$resultObject = $statement->fetchObject( get_called_class() );

		if($resultObject != false)
		{
			$resultObject->isNew = false;
            $resultObject->setDatabase($database); 
		}

		return $resultObject;
    }
 
    public function save()
    {
		if($this->isNew)
		{ // insert
			$statement = $this->dbObject->prepare(
                "INSERT INTO `user` (" . 
                "username, email, password, status, onwikiname, welcome_sig, lastactive, forcelogout," . 
                "secure, checkuser, identified, welcome_template, abortpref, confirmationdiff, emailsig" . 
                ") VALUES (" . 
                ":username, :email, :password, :status, :onwikiname, :welcome_sig, :lastactive, :forcelogout," . 
                ":secure, :checkuser, :identified, :welcome_template, :abortpref, :confirmationdiff, :emailsig" . 
                ");");
			$statement->bindParam(":username", $this->username);
			$statement->bindParam(":email", $this->email);
			$statement->bindParam(":password", $this->password);
			$statement->bindParam(":onwikiname", $this->onwikiname);
			$statement->bindParam(":welcome_sig", $this->welcome_sig);
			$statement->bindParam(":lastactive", $this->lastactive);
			$statement->bindParam(":forcelogout", $this->forcelogout);
			$statement->bindParam(":secure", $this->secure);
			$statement->bindParam(":checkuser", $this->checkuser);
			$statement->bindParam(":identified", $this->identified);
			$statement->bindParam(":welcome_template", $this->welcome_template);
			$statement->bindParam(":abortpref", $this->abortpref);
			$statement->bindParam(":confirmationdiff", $this->confirmationdiff);
			$statement->bindParam(":emailsig", $this->emailsig);
			if($statement->execute())
			{
				$this->isNew = false;
				$this->id = $this->dbObject->lastInsertId();
			}
			else
			{
				throw new Exception($statement->errorInfo());
			}
		}
		else
		{ // update
			$statement = $this->dbObject->prepare("UPDATE `user` SET " . 
                "id = :id, username = :username, email = :email, password = :password, status = :status, " .
                "onwikiname = :onwikiname, welcome_sig = :welcome_sig, lastactive = :lastactive, " .
                "forcelogout = :forcelogout, secure = :secure, checkuser = :checkuser, identified = :identified, " .
                "welcome_template = :welcome_template, abortpref = :abortpref, confirmationdiff = :confirmationdiff, " .
                "emailsig = :emailsig," .
                "WHERE id = :id LIMIT 1;");
			$statement->bindParam(":id", $this->id);
			$statement->bindParam(":username", $this->username);
			$statement->bindParam(":email", $this->email);
			$statement->bindParam(":password", $this->password);
			$statement->bindParam(":onwikiname", $this->onwikiname);
			$statement->bindParam(":welcome_sig", $this->welcome_sig);
			$statement->bindParam(":lastactive", $this->lastactive);
			$statement->bindParam(":forcelogout", $this->forcelogout);
			$statement->bindParam(":secure", $this->secure);
			$statement->bindParam(":checkuser", $this->checkuser);
			$statement->bindParam(":identified", $this->identified);
			$statement->bindParam(":welcome_template", $this->welcome_template);
			$statement->bindParam(":abortpref", $this->abortpref);
			$statement->bindParam(":confirmationdiff", $this->confirmationdiff);
			$statement->bindParam(":emailsig", $this->emailsig);            
			if(!$statement->execute())
			{
				throw new Exception($statement->errorInfo());
			}
		} 
    }

    public function authenticate($password)
    {
        $result = authutils::testCredentials($password, $this->password);
        
        if($result == true)
        {
            // password version is out of date, update it.
            if(!authutils::isCredentialVersionLatest($this->password))
            {
                $this->password = authutils::encryptPassword($password);
                $this->save();
            }
        }
        
        return $result;
    }
    
    public function getUsername(){
        return $this->username;
    }

    public function setUsername($username){
        $this->username = $username;
        $this->forcelogout = 1;
    }

    public function getEmail(){
        return $this->email;
    }

    public function setEmail($email){
        $this->email = $email;
    }

    public function setPassword($password){
        $this->password = authutils::encryptPassword($password);
    }

    public function getStatus(){
        return $this->status;
    }

    public function getOnWikiName(){
        return $this->onwikiname;
    }

    public function setOnWikiName($onwikiname){
        $this->onwikiname = $onwikiname;
    }

    public function getWelcomeSig(){
        return $this->welcome_sig;
    }

    public function setWelcomeSig($welcome_sig){
        $this->welcome_sig = $welcome_sig;
    }

    public function getLastActive(){
        return $this->lastactive;
    }

    public function setLastActive($lastactive){
        $this->lastactive = $lastactive;
    }

    public function getForcelogout(){
        return $this->forcelogout;
    }

    public function setForcelogout($forcelogout){
        $this->forcelogout = $forcelogout;
    }
    
    public function getSecure(){
        return $this->secure;
    }

    public function setSecure($secure){
        $this->secure = $secure;
    }

    public function getCheckuser(){
        return $this->checkuser;
    }

    public function setCheckuser($checkuser){
        $this->checkuser = $checkuser;
    }

    public function getIdentified(){
        return $this->identified;
    }

    public function setIdentified($identified){
        $this->identified = $identified;
    }

    public function getWelcomeTemplate(){
        return $this->welcome_template;
    }

    public function setWelcomeTemplate($welcome_template){
        $this->welcome_template = $welcome_template;
    }

    public function getAbortPref(){
        return $this->abortpref;
    }

    public function setAbortPref($abortpref){
        $this->abortpref = $abortpref;
    }

    public function getConfirmationDiff(){
        return $this->confirmationdiff;
    }

    public function setConfirmationDiff($confirmationdiff){
        $this->confirmationdiff = $confirmationdiff;
    }

    public function getEmailSig(){
        return $this->emailsig;
    }

    public function setEmailSig($emailsig){
        $this->emailsig = $emailsig;
    }

    private function updateStatus($status, $logaction, $comment)
    {
        $oldstatus = $this->status;
        
        if(!$this->dbObject->beginTransaction())
        {
            throw new Exception("Could not begin database transaction");
        }
        
        try
        {
            $this->status = $status;            
            $statusquery = $this->dbObject->prepare("UPDATE user SET status = :status WHERE id = :id;");
            $statusquery->bindParam(":status", $status);
            $statusquery->bindParam(":id", $this->id);
            
            // TODO: update me to use new logging systems.
            $logquery = $this->dbObject->prepare("INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES (:id, :user, :action, CURRENT_TIMESTAMP(), :cmt);");
            $logquery->bindParam(":user", User::getCurrent($this->dbObject)->getUsername());
            $logquery->bindParam(":id", $this->id);
            $logquery->bindParam(":action", $logaction);
            $logquery->bindParam(":cmt", $comment);
            
            $statusquery->execute();
            $logquery->execute();
        }
        catch( Exception $ex )
        {
            // something went wrong, so rollback and rethrow for someone else to handle the error.
            $this->dbObject->rollBack();
            $this->status = $oldstatus;
            
            throw $ex;
        }
        
        $this->dbObject->commit();
        
    }
    
    public function approve()
    {
        $this->updateStatus("User", "Approved", null);
    }
    
    public function suspend($comment)
    {
        $this->updateStatus("Suspended", "Suspended", $comment);
    }
    
    public function decline($comment)
    {
        $this->updateStatus("Declined", "Declined", $comment);
    }
    
    public function promote()
    {
        $this->updateStatus("Admin", "Promoted", null);
    }
    
    public function demote($comment)
    {
        $this->updateStatus("User", "Demoted", $comment);
    }

    public function isAdmin()
    {
        return $this->status == "Admin";
    }
    
    public function isCheckuser()
    {
        return $this->checkuser == 1;
    }
    
    public function isIdentified()
    {
        return $this->identified == 1;   
    }
    
    public function isSuspended()
    {
        return $this->status == "Suspended";
    }
}
