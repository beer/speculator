<?php
class SpeculatorTable extends Pix_Table
{
    protected function _getDb() 
    {
        // for auke.us
        if (!getenv('DATABASE_URL')) {
            $json = file_get_contents(__DIR__ . '/../../config/database.json');
            $config = json_decode($json)->develop;

            $link = new mysqli($config->host, $config->user, $config->password, $config->database);
        } else {
            if (!preg_match('#mysql://([^:]*):([^@]*)@([^/]*)/(.*)#', strval(getenv('DATABASE_URL')), $matches)) {
                die('mysql only');
            }
            $link = new mysqli($matches[3], $matches[1], $matches[2], 'user_matsu-yueh-390194');
        }
        $link->set_charset('utf8');
        return new Pix_Table_Db_Adapter_Mysqli($link);
    }
}
