<?php
class FutureTickRow extends Pix_Table_Row
{
    public function preInsert()
    {
        $this->created_at = time();
    }

    public function preSave()
    {
        $this->updated_at = time();
    }

    public function getTicks()
    {
    }
}

class FutureTick extends SpeculatorTable
{
    public function init()
    {
        $this->_name = 'futures_ticks';
        $this->_rowClass = 'FutureTickRow';

        $this->_primary = 'id';

        $this->_columns['id'] = array('type' => 'int', 'size' => 10, 'unsigned' => true, 'auto_increment' => true);
        $this->_columns['date'] = array('type' => 'int', 'size' => 10);
        $this->_columns['time'] = array('type' => 'int', 'size' => 10);
        $this->_columns['label'] = array('type' => 'varchar', 'size' => 30);
        $this->_columns['open'] = array('type' => 'float', 'size' => 10);
        $this->_columns['top'] = array('type' => 'float', 'size' => 10);
        $this->_columns['low'] = array('type' => 'float', 'size' => 10);
        $this->_columns['close'] = array('type' => 'float', 'size' => 10);
        $this->_columns['bid'] = array('type' => 'float', 'size' => 10);
        $this->_columns['bid_count'] = array('type' => 'int', 'size' => 10);
        $this->_columns['ask'] = array('type' => 'float', 'size' => 10);
        $this->_columns['ask_count'] = array('type' => 'int', 'size' => 10);
        $this->_columns['volume'] = array('type' => 'bigint', 'size' => 15);
        $this->_columns['change'] = array('type' => 'float', 'size' => 10);
        $this->_columns['amplitude'] = array('type' => 'float', 'size' => 10);
        $this->_columns['ex_close'] = array('type' => 'float', 'size' => 10);
        $this->_columns['created_at'] = array('type' => 'int', 'size' => 10);
        $this->_columns['updated_at'] = array('type' => 'int', 'size' => 10);

        //$this->_relations['5mins'] = array('rel' => 'has_many', 'type' => '5min', 'foreign_key' => 'date', 'delete' => true);

    }
}
