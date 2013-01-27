<?php
class FutureContractRow extends Pix_Table_Row
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

class FutureContract extends SpeculatorTable
{
    public function init()
    {
        $this->_name = 'futures_contracts';
        $this->_rowClass = 'FutureContractRow';

        $this->_primary = 'id';

        $this->_columns['id'] = array('type' => 'int', 'size' => 10, 'unsigned' => true, 'auto_increment' => true);
        $this->_columns['type'] = array('type' => 'varchar', 'size' => 50);
        $this->_columns['user_id'] = array('type' => 'int', 'size' => 10);
        $this->_columns['date'] = array('type' => 'int', 'size' => 10);
        $this->_columns['buy'] = array('type' => 'int', 'size' => 10);
        $this->_columns['buy_amount'] = array('type' => 'int', 'size' => 10);
        $this->_columns['sell'] = array('type' => 'int', 'size' => 10);
        $this->_columns['sell_amount'] = array('type' => 'int', 'size' => 10);
        $this->_columns['diff'] = array('type' => 'int', 'size' => 10);
        $this->_columns['diff_amount'] = array('type' => 'int', 'size' => 10);
        $this->_columns['created_at'] = array('type' => 'int', 'size' => 10);
        $this->_columns['updated_at'] = array('type' => 'int', 'size' => 10);
    }
}
