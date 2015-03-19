<?php

class Model_DbTable_Atividade extends Zend_Db_Table_Abstract
{

    protected $_name = 'atividade';
    protected $_primary = 'id';
    protected $_referenceMap = array(
        'Usuario' => array(
            'columns' => array('usuario_id'),
            'refTableClass' => 'Model_DbTable_Usuario',
            'refColumns' => array('id')
        ),
        'Tarefa' => array(
            'columns' => array('tarefa_id'),
            'refTableClass' => 'Model_DbTable_Tarefa',
            'refColumns' => array('id')
        ) 
    );

}

