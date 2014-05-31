<?php

/**
 * Email template data object
 * 
 * This is the close reasons thing.
 */
class EmailTemplate extends DataObject
{
    private $name;
    private $text;
    private $jsquestion;
    private $oncreated = 0;
    private $active = 1;
    private $preloadonly = 0;
        
    public static function getActiveTemplates($forCreated, PdoDatabase $database = null)
    {
        if($database == null)
        {
            $database = gGetDb();   
        }
        
        global $createdid;
        
    	$statement = $database->prepare("SELECT * FROM `emailtemplate` WHERE oncreated = :forcreated AND active = 1 AND preloadonly = 0 AND id != :createdid;");
    	$statement->bindValue(":createdid", $createdid);
    	$statement->bindValue(":forcreated", $forCreated);
        
    	$statement->execute();
        
    	$resultObject = $statement->fetchAll( PDO::FETCH_CLASS, get_called_class() );
        
        foreach ($resultObject as $t)
        {
            $t->setDatabase($database);
            $t->isNew = false;
        }
        
    	return $resultObject;
    }
    
    public static function getAllActiveTemplates($forCreated, PdoDatabase $database = null)
    {
        if($database == null)
        {
            $database = gGetDb();   
        }
        
    	$statement = $database->prepare("SELECT * FROM `emailtemplate` WHERE oncreated = :forcreated AND active = 1;");
    	$statement->bindValue(":forcreated", $forCreated);
        
    	$statement->execute();
        
    	$resultObject = $statement->fetchAll( PDO::FETCH_CLASS, get_called_class() );
        
        foreach ($resultObject as $t)
        {
            $t->setDatabase($database);
            $t->isNew = false;
        }
        
    	return $resultObject;
    }
    
    public static function getByName($name, PdoDatabase $database)
    {
    	$statement = $database->prepare("SELECT * FROM `emailtemplate` WHERE name = :name LIMIT 1;");
    	$statement->bindValue(":name", $name);
    
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
			$statement = $this->dbObject->prepare("INSERT INTO `emailtemplate` (name, text, jsquestion, oncreated, active, preloadonly) VALUES (:name, :text, :jsquestion, :oncreated, :active, :preloadonly);");
			$statement->bindValue(":name", $this->name);
			$statement->bindValue(":text", $this->text);
			$statement->bindValue(":jsquestion", $this->jsquestion);
			$statement->bindValue(":oncreated", $this->oncreated);
			$statement->bindValue(":active", $this->active);
			$statement->bindValue(":preloadonly", $this->preloadonly);
            
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
			$statement = $this->dbObject->prepare("UPDATE `emailtemplate` SET name = :name, text = :text, jsquestion = :jsquestion, oncreated = :oncreated, active = :active, preloadonly = :preloadonly WHERE id = :id LIMIT 1;");
			$statement->bindValue(":id", $this->id);
			$statement->bindValue(":name", $this->name);
			$statement->bindValue(":text", $this->text);
			$statement->bindValue(":jsquestion", $this->jsquestion);
			$statement->bindValue(":oncreated", $this->oncreated);
			$statement->bindValue(":active", $this->active);
			$statement->bindValue(":preloadonly", $this->preloadonly);
            
			if(!$statement->execute())
			{
				throw new Exception($statement->errorInfo());
			}
		}
    }
    
    /**
     * Override delete() from DataObject
     */
    public function delete()
    {
        throw new Exception("You shouldn't be doing that, you'll break logs.");   
    }
    
    public function getName(){
        return $this->name;
    }

    public function setName($name){
        $this->name = $name;
    }

    public function getText(){
        return $this->text;
    }

    public function setText($text){
        $this->text = $text;
    }

    public function getJsquestion(){
        return $this->jsquestion;
    }

    public function setJsquestion($jsquestion){
        $this->jsquestion = $jsquestion;
    }

    public function getOncreated(){
        return $this->oncreated == 1;
    }

    public function setOncreated($oncreated){
        $this->oncreated = $oncreated ? 1 : 0;
    }

    public function getActive(){
        return $this->active == 1;
    }

    public function setActive($active){
        $this->active = $active ? 1 : 0;
    }

    public function getPreloadOnly()
    {
        return $this->preloadonly == 1;
    }

    public function setPreloadOnly($preloadonly)
    {
        $this->preloadonly = $preloadonly ? 1 : 0;
    }
}
