<?php
class SpeculatorTable extends Pix_Table
{
    protected function _getDb() 
    {
        $json = file_get_contents(__DIR__ . '/../../config/database.json');
        $config = json_decode($json)->develop;

        $link = new mysqli($config->host, $config->user, $config->password, $config->database);
        $link->set_charset('utf8');
        return new Pix_Table_Db_Adapter_Mysqli($link);
    }
}
