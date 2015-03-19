<?php

class UsuarioController extends Zend_Controller_Action {

    public $where = null;

    public function init() {
        parent::init();
        $auth = Zend_Auth::getInstance()->getIdentity();
        if (empty($auth)) {
            $this->_helper->redirector->goToRoute(array('controller' => 'auth', 'action' => 'login', 'msg' => 1), null, true);
        }
        $this->_model = new Model_DbTable_Usuario();
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
/*        $email = "someone@somewhere.com";
        $default = "http://www.somewhere.com/homestar.jpg";
        $size = 40;
        $grav_url = "http://www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . "?d=" . urlencode($default) . "&s=" . $size;
        "<img src='<?php echo //$grav_url; ?>' alt='' />";
*/
        if ($this->getRequest()->isPost()) {
            $nome = $this->getRequest()->getPost('nome');
            $email = $this->getRequest()->getPost('email');
            $equipe_id = $this->getRequest()->getPost('equipe');
            $dataCadastro = $this->getRequest()->getPost('dataCadastro');
//            $this->where = "";
            if (!empty($nome)) {
                $this->where[] = "nome like '%$nome%'";
            }
            if (!empty($email)) {
                $this->where[] = "email like '%$email%'";
            }
            if (!empty($equipe_id)){
                $this->where[] = "equipe_id = $equipe_id";
            }
            if (!empty($dataCadastro)) {
                $data = explode("/", $dataCadastro);
                $dataCadastro = $data[2] . "-" . $data[1] . "-" . $data[0];
                $this->where[] = "data_cadastro = '" . $dataCadastro . "'";
            }
            $this->paginacao();
        } else if ($this->getRequest()->isGet()) {
            $this->paginacao();
        }
//        $this->view->usuarios= $this->_model->fetchAll();
    }

    private function paginacao() {
        $pagina = $this->_request->getParam("pagina", "1");
        $porPagina = $this->_request->getParam("por-pagina", "5");
        $rangePaginas = $this->_request->getParam("range-pagina", "5");
        $usuarios = $this->_model->fetchAll($this->where, null, null, null);
        if ($usuarios->count() > 0) {
            $paginator = Zend_Paginator::factory($usuarios);
            $paginator->setCurrentPageNumber($pagina);
            $paginator->setItemCountPerPage($porPagina);
            $paginator->setPageRange($rangePaginas);
            $this->view->usuarios = $paginator;
        } else {
            $this->view->usuarios = null;
        }
    }

    public function createAction() {
        if ($this->getRequest()->isPost()) {
            $nome = $this->getRequest()->getPost('nome');
            $email = $this->getRequest()->getPost('email');
            $senha = $this->getRequest()->getPost('senha');
            $grupo = $this->getRequest()->getPost('grupo');
            $usuario_id = $this->getRequest()->getPost('equipe');
            $dados = array("nome" => $nome,
                "email" => $email,
                "senha" => md5($senha),
                "grupo" => $grupo,
                "data_cadastro" => new Zend_Db_Expr('NOW()'),
                "status" => "1",
                "equipe_id" => $usuario_id
            );
            $usuario = new Model_DbTable_Usuario();
            $usuario->insert($dados);
            $this->view->message = "Cadastrado com sucesso.";
            $this->_helper->redirector->goToRoute(array('controller' => 'usuario', 'action' => 'index'), null, true);
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
            $this->_redirect('usuario');
        }

        $id = (int) $this->_getParam('id');
        $where = $this->_model->getAdapter()->quoteInto('id = ?', $id);
        $this->_model->delete($where);
//        $this->_redirect('equipe/list');
        $this->_helper->redirector->goToRoute(array('controller' => 'usuario', 'action' => 'index'), null, true);
    }

    public function editAction() {

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $this->_update($data);
            $this->_helper->redirector->goToRoute(array('controller' => 'usuario', 'action' => 'index'), null, true);
        }

        $usuario_id = (int) $this->_getParam('id');
        $usuario = $this->_model->find($usuario_id);

        if (count($usuario) == 0) {
            $this->view->message = "<div class='alert alert-error'>Registro n&atilde;o encontrado!</div>";
        }

        $this->view->usuario = $usuario->current();
    }

    private function _update($data) {
        $where = $this->_model->getAdapter()->quoteInto('id = ?', (int) $data['id']);
        $senha = $data['senha'];
        $array = array();
        if (empty($senha)) {
            $array = array("nome" => $data['nome'],
                "email" => $data['email'],
                "grupo" => $data['grupo'],
                "equipe_id" => $data['equipe']
            );
        } else {
            $array = array("nome" => $data['nome'],
                "email" => $data['email'],
                "senha" => md5($senha),
                "grupo" => $data['grupo'],
                "equipe_id" => $data['equipe']
            );
        }



        return $this->_model->update($array, $where);
    }

    /**
     * Get either a Gravatar URL or complete image tag for a specified email address.
     *
     * @param string $email The email address
     * @param string $s Size in pixels, defaults to 80px [ 1 - 512 ]
     * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
     * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
     * @param boole $img True to return a complete IMG tag False for just the URL
     * @param array $atts Optional, additional key/value attributes to include in the IMG tag
     * @return String containing either just a URL or a complete image tag
     * @source http://gravatar.com/site/implement/images/php/
     */
    function get_gravatar($email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array()) {
        $url = 'http://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$s&d=$d&r=$r";
        if ($img) {
            $url = '<img src="' . $url . '"';
            foreach ($atts as $key => $val)
                $url .= ' ' . $key . '="' . $val . '"';
            $url .= ' />';
        }
        return $url;
    }

}

