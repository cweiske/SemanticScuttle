<?php
/* Connect to the database and build services */

class SemanticScuttle_Service_Factory
{
	public function __construct($db, $serviceoverrules = array())
    {
	}

	public function getServiceInstance($name, $servicedir = null)
    {
		global $dbhost, $dbuser, $dbpass, $dbname, $dbport, $dbpersist, $dbtype;
		static $instances = array();
		static $db;
		if (!isset($db)) {
			require_once 'SemanticScuttle/db/'. $dbtype .'.php';
			$db = new sql_db();
			$db->sql_connect($dbhost, $dbuser, $dbpass, $dbname, $dbport, $dbpersist);
			if(!$db->db_connect_id) {
				message_die(CRITICAL_ERROR, "Could not connect to the database", $db);
			}
			$db->sql_query("SET NAMES UTF8"); 
		}		
		
		if (!isset($instances[$name])) {
			if (isset($serviceoverrules[$name])) {
				$name = $serviceoverrules[$name];
			}
			if (!class_exists($name)) {
				if (!isset($servicedir)) {
					$servicedir = 'SemanticScuttle/Service/';
				}
								
				require_once $servicedir . $name . '.php';
			}
			$instances[$name] = call_user_func(
                array('SemanticScuttle_Service_' . $name, 'getInstance'),
                $db
            );
		}
		return $instances[$name];
	}
}
?>
