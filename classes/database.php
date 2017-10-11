<?php
class Database
{
    private $dbPath;
	
    public function __construct($dbPath)
    {
        $this->dbPath = $dbPath;
    }

	public function getBunqContext()
    {
		$db = new SQLite3($this->dbPath);
		$results = $db->query('SELECT ContextJson FROM bunqContext');
		while ($context = $results->fetchArray()) {
			$bunqContext = $context['ContextJson'];
		}

        return base64_decode($bunqContext);
    }
	
	public function setBunqContext($json)
    {
		$db = new SQLite3($this->dbPath);
		$setBunqContext = $db->exec("INSERT INTO bunqContext (ContextJson) VALUES ('".base64_encode($json)."');");

        return $setBunqContext;
    }

}
?>