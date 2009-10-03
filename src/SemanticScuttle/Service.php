<?php
class SemanticScuttle_Service
{
    /**
     * SQL database object
     *
     * @var sql_db
     */
    protected $db;



    /**
     * Returns the single service instance
     *
     * @internal
     * This function can be used once PHP 5.3 is minimum, because only
     * 5.3 supports late static binding. For all lower php versions,
     * we still need a copy of this method in each service class.
     *
     * @param DB $db Database object
     *
     * @return SemanticScuttle_Service
     */
	public static function getInstance($db)
    {
		static $instance;
		if (!isset($instance)) {
            $instance = new self($db);
        }
		return $instance;
	}

}
?>