<?php
class TickVolumeRow extends Pix_Table_Row
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

class TickVolume extends SpeculatorTable
{
    public function init()
    {
        $this->_name = 'ticks';
        $this->_rowClass = 'TickRow';

        $this->_primary = 'id';

        $this->_columns['id'] = array('type' => 'int', 'size' => 10, 'unsigned' => true, 'auto_increment' => true);
        $this->_columns['date'] = array('type' => 'int', 'size' => 10);
        $this->_columns['time'] = array('type' => 'int', 'size' => 10);
        $this->_columns['buy_count'] = array('type' => 'int', 'size' => 10);
        $this->_columns['buy_volume'] = array('type' => 'int', 'size' => 10);
        $this->_columns['sell_count'] = array('type' => 'int', 'size' => 10);
        $this->_columns['sell_volume'] = array('type' => 'int', 'size' => 10);
        $this->_columns['deal_count'] = array('type' => 'int', 'size' => 10);
        $this->_columns['deal_volume'] = array('type' => 'int', 'size' => 10);
        $this->_columns['volume'] = array('type' => 'bigint', 'size' => 15);
        $this->_columns['created_at'] = array('type' => 'int', 'size' => 10);
        $this->_columns['updated_at'] = array('type' => 'int', 'size' => 10);
    }
}
