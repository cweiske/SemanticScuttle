<?php
/**
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */

/**
 * Connect to the database and build services.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Service_Factory
{
    /**
     * Array of service instances.
     * Key is service name (i.e. "Bookmark")
     *
     * @var array
     */
    protected static $instances = array();

    /**
     * Database connection
     *
     * @var sql_qb
     */
    protected static $db = null;

    /**
     * Array of service names -> new service class
     * in case you want to overwrite the services.
     *
     * Key is the old service name (i.e. "Bookmark"),
     * value the new class name, e.g.
     * "My_Cool_Own_BookmarkService"
     *
     * @var array
     */
    public static $serviceoverrides = array();



    /**
     * Returns the service for the given name.
     *
     * @param string $name Service name (i.e. "Bookmark")
     *
     * @return SemanticScuttle_Service Service object
     */
    public static function get($name)
    {
        self::loadDb();
        self::loadService($name);
        return self::$instances[$name];
    }



    /**
     * Loads service with the given name into
     * self::$instances[$name].
     *
     * @param string $name Service name (i.e. 'Bookmark')
     *
     * @return void
     */
    protected static function loadService($name)
    {
        if (isset(self::$instances[$name])) {
            return;
        }

        if (isset(self::$serviceoverrides[$name])) {
            $class = self::$serviceoverrides[$name];
        } else {
            $class = 'SemanticScuttle_Service_' . $name;
        }

        if (!class_exists($class)) {
            //PEAR classname to filename rule
            $file = str_replace('_', '/', $class) . '.php';
            include_once $file;
        }

        self::$instances[$name] = call_user_func(
            array($class, 'getInstance'),
            self::$db
        );
    }



    /**
     * Loads self::$db if it is not loaded already.
     * Dies if the connection could not be established.
     *
     * @return void
     */
    protected static function loadDb()
    {
        global $dbhost, $dbuser, $dbpass, $dbname,
            $dbport, $dbpersist, $dbtype, $dbneedssetnames;

        if (self::$db !== null) {
            return;
        }
        include_once 'SemanticScuttle/db/'. $dbtype .'.php';
        $db = new sql_db();
        $db->sql_connect(
            $dbhost, $dbuser, $dbpass, $dbname, $dbport, $dbpersist
        );
        if (!$db->db_connect_id) {
            message_die(
                CRITICAL_ERROR,
                'Could not connect to the database',
                self::$db
            );
        }

        $dbneedssetnames && $db->sql_query('SET NAMES UTF8');

        self::$db = $db;
    }



    /**
     * Returns sql database object
     *
     * @return sql_db Database instance
     */
    public static function getDb()
    {
        self::loadDb();
        return self::$db;
    }

}
?>
