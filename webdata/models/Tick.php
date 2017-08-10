<?php
class TickRow extends Pix_Table_Row
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

class Tick extends SpeculatorTable
{
    public function init()
    {
        $this->_name = 'ticks';
        $this->_rowClass = 'TickRow';

        $this->_primary = 'id';

        $this->_columns['id'] = array('type' => 'int', 'size' => 10, 'unsigned' => true, 'auto_increment' => true);
        $this->_columns['date'] = array('type' => 'int', 'size' => 10);
        $this->_columns['time'] = array('type' => 'int', 'size' => 10);
        $this->_columns['twse'] = array('type' => 'float', 'size' => 10);
        $this->_columns['volume'] = array('type' => 'bigint', 'size' => 15);
        $this->_columns['created_at'] = array('type' => 'int', 'size' => 10);
        $this->_columns['updated_at'] = array('type' => 'int', 'size' => 10);
    }
}
