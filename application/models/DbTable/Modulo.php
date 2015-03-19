<?php

class Model_DbTable_Modulo extends Zend_Db_Table_Abstract
{

    protected $_name = 'modulo';
    protected $_primary = 'id';
    protected $_dependentTables = array('Model_DbTable_Tarefa');
}

