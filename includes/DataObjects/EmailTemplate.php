<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

class EmailTemplate extends DataObject
{
    private $name;
    private $text;
    private $jsquestion;
    private $oncreated;
    private $active;
    
    public function save()
    {
        if($this->isNew)
		{ // insert
			$statement = $this->dbObject->prepare("INSERT INTO `emailtemplate` (name, text, jsquestion, oncreated, active) VALUES (:name, :text, :jsquestion, :oncreated, :active);");
			$statement->bindParam(":name", $this->name);
			$statement->bindParam(":text", $this->text);
			$statement->bindParam(":jsquestion", $this->jsquestion);
			$statement->bindParam(":oncreated", $this->oncreated);
			$statement->bindParam(":active", $this->active);
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
			$statement = $this->dbObject->prepare("UPDATE `emailtemplate` SET name = :name, text = :text, jsquestion = :jsquestion, oncreated = :oncreated, active = :active WHERE id = :id LIMIT 1;");
			$statement->bindParam(":id", $this->id);
			$statement->bindParam(":name", $this->name);
			$statement->bindParam(":text", $this->text);
			$statement->bindParam(":jsquestion", $this->jsquestion);
			$statement->bindParam(":oncreated", $this->oncreated);
			$statement->bindParam(":active", $this->active);
            
			if(!$statement->execute())
			{
				throw new Exception($statement->errorInfo());
			}
		}
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
        return $this->oncreated;
    }

    public function setOncreated($oncreated){
        $this->oncreated = $oncreated;
    }

    public function getActive(){
        return $this->active;
    }

    public function setActive($active){
        $this->active = $active;
    }
}
