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
        $this->setFields( array('fk_i_user_id', 's_account_id') ) ;
    }

    public function createTable()
    {
        $path = dirname(__FILE__) . '/struct.sql';
        $sql = file_get_contents($path);

        if(! $this->dao->importSQL($sql) ){
            throw new Exception( "Error importSQL::ModelLatch<br>struct.sql");
        }
    }

    public function dropTable() {
        $this->dao->query(sprintf('DROP TABLE %s', $this->getTableName()));
    }

    public function pair($userId, $accountId) {
        $this->dao->insert($this->getTableName(), array(
            'fk_i_user_id' => $userId,
            's_account_id' => $accountId
        ));
        return $this->dao->insertedId();
    }

    public function unpair($userId) {
        return $this->dao->delete($this->getTableName(), array('fk_i_user_id' => $userId));
    }

}