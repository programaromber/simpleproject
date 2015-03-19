<?php

class EquipeController extends Zend_Controller_Action {

    public $where = null;

    public function init() {
        parent::init();
        $auth = Zend_Auth::getInstance()->getIdentity();
        if (!$auth) {
            $this->_helper->redirector->goToRoute(array('controller' => 'auth', 'action' => 'login', 'msg' => 1), null, true);
        }
        $this->_model = new Model_DbTable_Equipe();
    }

    public function indexAction() {
        $this->_forward('list');
    }

    /**
     * List all users
     *
     * @return void
     */
    public function listAction() {
        if ($this->getRequest()->isPost()) {
            $nome = $this->getRequest()->getPost('nome');
            $auth = Zend_Auth::getInstance()->getIdentity();
            if (empty($auth) and !empty($nome)) {
                $this->where[] = "nome like '%$nome%'";
            }
            $this->paginacao();
        } else if ($this->getRequest()->isGet()) {
            $this->paginacao();
        }
//        $this->view->equipes = $this->_model->fetchAll();
    }

    private function paginacao() {
        $pagina = $this->_request->getParam("pagina", "1");
        $porPagina = $this->_request->getParam("por-pagina", "5");
        $rangePaginas = $this->_request->getParam("range-pagina", "5");
        $equipes = $this->_model->fetchAll($this->where, null, null, null);
        if ($equipes->count() > 0) {
            $paginator = Zend_Paginator::factory($equipes);
            $paginator->setCurrentPageNumber($pagina);
            $paginator->setItemCountPerPage($porPagina);
            $paginator->setPageRange($rangePaginas);
            $this->view->equipes = $paginator;
        } else {
            $this->view->equipes = null;
        }
    }

    public function createAction() {
        if ($this->getRequest()->isPost()) {
            $nome = $this->getRequest()->getPost('nome');
//            $dia = $this->getRequest()->getPost('dia');
//            $mes = $this->getRequest()->getPost('mes');
//            $ano = $this->getRequest()->getPost('ano');
            //$data = new Zend_Date(date_create(get)) ;
//            $data = new Zend_Date();
            $dados = array("nome" => $nome
//                "dataCadastro" => new Zend_Date()
            );
            //            if (!empty($data_nascimento)) {
//                $locale = new Zend_Locale('pt_BR');
//                $data_nascimento = new Zend_Date($data_nascimento);
//                $data_nascimento = $data_nascimento->toString('YYYY-MM-dd', $locale);
//            }
            $equipe = new Model_DbTable_Equipe();
            $equipe->insert($dados);
            $this->view->message = "Cadastrado com sucesso.";
            $this->_helper->redirector->goToRoute(array('controller' => 'equipe', 'action' => 'index'), null, true);
        }
    }

    /**
     * Deleta um registro e redireciona para 'users/list'
     * Caso não seja informado nenhum ID pela url,
     * o usuário será redirecionado para 'users'
     *
     * @return void
     */
    public function deleteAction() {
        // verificamos se realmente foi informado algum ID
        if ($this->_hasParam('id') == false) {
            $this->_redirect('equipe');
        }

        $id = (int) $this->_getParam('id');
        $where = $this->_model->getAdapter()->quoteInto('id = ?', $id);
        $this->_model->delete($where);
//        $this->_redirect('equipe/list');
        $this->_helper->redirector->goToRoute(array('controller' => 'equipe', 'action' => 'index'), null, true);
    }

    public function editAction() {

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $this->_update($data);
            $this->_helper->redirector->goToRoute(array('controller' => 'equipe', 'action' => 'index'), null, true);
        }

        $equipe_id = (int) $this->_getParam('id');
        $equipe = $this->_model->find($equipe_id);

        if (count($equipe) == 0) {
            $this->view->message = "<div class='alert alert-error'>Registro n&atilde;o encontrado!</div>";
        }

        $this->view->equipe = $equipe->current();
    }

    private function _update($data) {
        $where = $this->_model->getAdapter()->quoteInto('id = ?', (int) $data['id']);
        $data = array(
            'nome' => $data['nome']
        );
        return $this->_model->update($data, $where);
    }

}

