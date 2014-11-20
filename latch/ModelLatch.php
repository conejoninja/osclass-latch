<?php

class ModelLatch extends DAO
{

    private static $instance ;
    public static function newInstance() {
        if( !self::$instance instanceof self ) {
            self::$instance = new self ;
        }
        return self::$instance ;
    }

    function __construct() {
        parent::__construct();
        $this->setTableName('t_latch') ;
        $this->setPrimaryKey('fk_i_user_id') ;
        $this->setFields( array('pk_i_id', 'fk_i_user_id', 's_account_id', 'b_admin') ) ;
    }

    public function createTable()
    {
        $path = dirname(__FILE__) . '/struct.sql';
        $sql = file_get_contents($path);

        if(! $this->dao->importSQL($sql) ){
            throw new Exception( "Error importSQL::ModelLatch<br>struct.sql");
        }
    }

    public function findByUser($userId, $is_admin = 0) {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $this->dao->where('fk_i_user_id', $userId);
        $this->dao->where('b_admin', $is_admin);
        $result = $this->dao->get();

        if($result == false) {
            return array();
        }
        return $result->row();
    }

    public function dropTable() {
        $this->dao->query(sprintf('DROP TABLE %s', $this->getTableName()));
    }

    public function pair($userId, $accountId, $is_admin = 0) {
        $this->dao->insert($this->getTableName(), array(
            'fk_i_user_id' => $userId,
            's_account_id' => $accountId,
            'b_admin' => $is_admin
        ));
        return $this->dao->insertedId();
    }

    public function unpair($userId, $is_admin = 0) {
        return $this->dao->delete($this->getTableName(), array('fk_i_user_id' => $userId, 'b_admin' => $is_admin));
    }

}