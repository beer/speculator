<?php
class OptionTradeRow extends Pix_Table_Row
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

class OptionTrade extends SpeculatorTable
{
    public function init()
    {
        $this->_name = 'options_trades';
        $this->_rowClass = 'OptionTradeRow';

        $this->_primary = 'id';

        $this->_columns['id'] = array('type' => 'int', 'size' => 10, 'unsigned' => true, 'auto_increment' => true);
        $this->_columns['type'] = array('type' => 'varchar', 'size' => 50);
        $this->_columns['user_id'] = array('type' => 'int', 'size' => 10);
        $this->_columns['date'] = array('type' => 'int', 'size' => 10);
        $this->_columns['buycall'] = array('type' => 'int', 'size' => 10);
        $this->_columns['buycall_amount'] = array('type' => 'int', 'size' => 10);
        $this->_columns['buyput'] = array('type' => 'int', 'size' => 10);
        $this->_columns['buyput_amount'] = array('type' => 'int', 'size' => 10);
        $this->_columns['calldiff'] = array('type' => 'int', 'size' => 10);
        $this->_columns['calldiff_amount'] = array('type' => 'int', 'size' => 10);
        $this->_columns['sellcall'] = array('type' => 'int', 'size' => 10);
        $this->_columns['sellcall_amount'] = array('type' => 'int', 'size' => 10);
        $this->_columns['sellput'] = array('type' => 'int', 'size' => 10);
        $this->_columns['sellput_amount'] = array('type' => 'int', 'size' => 10);
        $this->_columns['putdiff'] = array('type' => 'int', 'size' => 10);
        $this->_columns['putdiff_amount'] = array('type' => 'int', 'size' => 10);
        $this->_columns['created_at'] = array('type' => 'int', 'size' => 10);
        $this->_columns['updated_at'] = array('type' => 'int', 'size' => 10);
    }
}
