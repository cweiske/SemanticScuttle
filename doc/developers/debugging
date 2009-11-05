How to debug SemanticScuttle
============================


Database queries
----------------
In config.php, enable debugMode.
Further, add the following there:
-------
register_shutdown_function(
    create_function('', <<<FNC
\$GLOBALS['db'] = SemanticScuttle_Service_Factory::getDb();
\$GLOBALS['db']->sql_report('display');
FNC
    )
);
------
To see database queries in SemanticScuttle, add
> ?explain=1
to your URL.
