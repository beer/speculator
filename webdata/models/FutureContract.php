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

    public function clearance($diff = 0)
    {
        $now = time();
        $year = date('Y', $now);
        $month = date('m', $now);

        return strtotime("third Wednesday", mktime(0, 0, 0, $month + $diff, 0, $year));
    }

    public function previous()
    {
        $yestoday = $this->date;
        while ($yestoday = $yestoday - 86400) {
            $row = FutureContract::search(array('date' => $yestoday, 'user_id' => $this->user_id));
            if (sizeof($row)) {
                return $row->first();
            }
        }
    }

    public function amount($field)
    {
        return number_format($this->{"{$field}_amount"});
    }

    public function point($field)
    {
        $field_amount = "{$field}_amount";
        return intval($this->{$field_amount}*1000/$this->{$field}/200);
    }

    public function detail($field)
    {
        return '契約金額:' . $this->amount($field) . '  平均點數:' . $this->point($field);
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
