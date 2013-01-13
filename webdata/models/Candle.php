<?php
class CandleRow extends Pix_Table_Row
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

class Candle extends SpeculatorTable
{
    public function init()
    {
        $this->_name = 'candle';
        $this->_rowClass = 'CandleRow';

        $this->_primary = 'id';

        $this->_columns['id'] = array('type' => 'int', 'size' => 10, 'unsigned' => true, 'auto_increment' => true);
        $this->_columns['time'] = array('type' => 'int', 'size' => 10);
        $this->_columns['open'] = array('type' => 'float', 'size' => 10);
        $this->_columns['top'] = array('type' => 'float', 'size' => 10);
        $this->_columns['low'] = array('type' => 'float', 'size' => 10);
        $this->_columns['close'] = array('type' => 'float', 'size' => 10);
        $this->_columns['created_at'] = array('type' => 'int', 'size' => 10);
        $this->_columns['updated_at'] = array('type' => 'int', 'size' => 10);
    }
}
