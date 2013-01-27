<?php
class OptionContractRow extends Pix_Table_Row
{
    public function preInsert()
    {
        $this->created_at = time();
    }

    public function preSave()
    {
        $this->updated_at = time();
    }
}

class OptionContract extends SpeculatorTable
{
    public function init()
    {
        $this->_name = 'options_contracts';
        $this->_rowClass = 'OptionContractRow';

        $this->_primary = 'id';

        $this->_columns['id'] = array('type' => 'int', 'size' => 10, 'unsigned' => true, 'auto_increment' => true);
        $this->_columns['type'] = array('type' => 'varchar', 'size' => 50);
        $this->_columns['user_id'] = array('type' => 'int', 'size' => 10);
        $this->_columns['date'] = array('type' => 'int', 'size' => 10);
        $this->_columns['buycall'] = array('type' => 'int', 'size' => 10);
        $this->_columns['buycall_amount'] = array('type' => 'int', 'size' => 10);
        $this->_columns['buyput'] = array('type' => 'int', 'size' => 10);
        $this->_columns['buyput_amount'] = array('type' => 'int', 'size' => 10);
        $this->_columns['buydiff'] = array('type' => 'int', 'size' => 10);
        $this->_columns['buydiff_amount'] = array('type' => 'int', 'size' => 10);
        $this->_columns['sellcall'] = array('type' => 'int', 'size' => 10);
        $this->_columns['sellcall_amount'] = array('type' => 'int', 'size' => 10);
        $this->_columns['sellput'] = array('type' => 'int', 'size' => 10);
        $this->_columns['sellput_amount'] = array('type' => 'int', 'size' => 10);
        $this->_columns['selldiff'] = array('type' => 'int', 'size' => 10);
        $this->_columns['selldiff_amount'] = array('type' => 'int', 'size' => 10);
        $this->_columns['created_at'] = array('type' => 'int', 'size' => 10);
        $this->_columns['updated_at'] = array('type' => 'int', 'size' => 10);
    }
}
