<?php

class FactoryTest extends TestBase
{
    public function testGetDb()
    {
        $this->assertInstanceOf(
            'sql_db',
            SemanticScuttle_Service_Factory::getDb()
        );
    }
}
?>