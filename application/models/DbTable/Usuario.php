<?php

class Model_DbTable_Usuario extends Zend_Db_Table_Abstract
{

    protected $_name = 'usuario';
    protected $_primary = 'id';
    protected $_dependentTables = array('Model_DbTable_Atividade');
    protected $_referenceMap = array(
        'Equipe' => array(
            'columns' => array('equipe_id'),
            'refTableClass' => 'Model_DbTable_Equipe',
            'refColumns' => array('id')
        ) 
    );

}

