<?php

class Model_DbTable_Equipe extends Zend_Db_Table_Abstract {

    protected $_name = 'equipe';
    protected $_primary = 'id';
    protected $_dependentTables = array('Model_DbTable_Tarefa','Model_DbTable_Usuario');
    /**
     * Table columns
     *
     * @access protected
     * @var    array
     */
    protected $_cols = array(
        'id',
        'nome'
    );
    /**
     * Is unique name?
     *
     * Check if $name is unique name in database
     *
     * @param  string $name
     * @return boolean
     */
    public function isUniqueName($nome)
    {
        $select = $this->select();
        $select->from($this->_nome, 'COUNT(*) AS num')
               ->where('nome = ?', $nome);
 
        return ($this->fetchRow($select)->num == 0) ? true : false;
    }

}

