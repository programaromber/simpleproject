<?php

class TarefaController extends Zend_Controller_Action {

    public $where = null;

    public function init() {
        parent::init();
        $auth = Zend_Auth::getInstance()->getIdentity();
        if (empty($auth)) {
            $this->_helper->redirector->goToRoute(array('controller' => 'auth', 'action' => 'login', 'msg' => 1), null, true);
        }
        $this->_model = new Model_DbTable_Tarefa();
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
        $equipe = new Model_DbTable_Equipe();
        $this->view->equipes = $equipe->fetchAll();
        $modulo = new Model_DbTable_Modulo();
        $this->view->modulos = $modulo->fetchAll();
        if ($this->getRequest()->isPost()) {
            $nome = $this->getRequest()->getPost('nome');
            $descricao = $this->getRequest()->getPost('descricao');
            $situacao = $this->getRequest()->getPost('situacao');
            $equipe = $this->getRequest()->getPost('equipe');
            $modulo = $this->getRequest()->getPost('modulo');
            $dataInicio = $this->getRequest()->getPost('dataInicio');
            $dataFim = $this->getRequest()->getPost('dataFim');

            if (!empty($nome)) {
                $this->where[] = "nome like '%$nome%'";
            }
            if (!empty($descricao)) {
                $this->where[] = "desricao like '%$descricao%'";
            }
            if (!empty($situacao)) {
                $this->where[] = "situacao = $situacao";
            }
            if (!empty($equipe)) {
                $this->where[] = "equipe_id = $equipe";
            }
            if (!empty($modulo)) {
                $this->where[] = "modulo_id = $modulo";
            }
//            if (!empty($dataInicio)) {
//                $data = explode("/", $dataInicio);
//                $dataInicio = $data[2] . "-" . $data[1] . "-" . $data[0];
//                $this->where[] = "data_inicio >= '$dataInicio'";
//            }
//            if (!empty($dataFim)) {
//                $data = explode("/", $dataFim);
//                $dataFim = $data[2] . "-" . $data[1] . "-" . $data[0];
//                $this->where[] = "data_fim <= '$dataFim'";
//            }

            $this->paginacao();
        } else if ($this->getRequest()->isGet()) {
            $this->paginacao();
        }
//        $this->view->tarefas = $this->_model->fetchAll();
    }

    private function paginacao() {
        $pagina = $this->_request->getParam("pagina", "1");
        $porPagina = $this->_request->getParam("por-pagina", "15");
        $rangePaginas = $this->_request->getParam("range-pagina", "15");
        $tarefas = $this->_model->fetchAll($this->where, null, null, null);
        if ($tarefas->count() > 0) {
            $paginator = Zend_Paginator::factory($tarefas);
            $paginator->setCurrentPageNumber($pagina);
            $paginator->setItemCountPerPage($porPagina);
            $paginator->setPageRange($rangePaginas);
            $this->view->tarefas = $paginator;
        } else {
            $this->view->tarefas = null;
        }
    }

    public function createAction() {

        if ($this->getRequest()->isPost()) {
            $nome = $this->getRequest()->getPost('nome');
            $descricao = $this->getRequest()->getPost('descricao');
            $dataInicio = new Zend_Date($this->getRequest()->getPost('dataInicio'), 'dd/MM/yyyy');
            $dataFim = new Zend_Date($this->getRequest()->getPost('dataFim'), 'dd/MM/yyyy');
            $percentual = $this->getRequest()->getPost('percentual');
            $situacao = $this->getRequest()->getPost('situacao');
            $equipe = $this->getRequest()->getPost('equipe');
            $modulo = $this->getRequest()->getPost('modulo');
            $dados = array("nome" => $nome,
                "descricao" => $descricao,
                "data_inicio" => $dataInicio->get('yyyy-MM-dd'),
                "data_fim" => $dataFim->get('yyyy-MM-dd'),
                "percentual" => $percentual,
                "situacao" => $situacao,
                "equipe_id" => $equipe,
                "modulo_id" => $modulo
            );
            $tarefa = new Model_DbTable_Tarefa();
//            $this->addEvento($tarefa);
            $tarefa->insert($dados);
            $this->view->message = "Cadastrado com sucesso.";
            $this->_helper->redirector->goToRoute(array('controller' => 'tarefa', 'action' => 'index'), null, true);
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
            $this->_redirect('tarefa');
        }

        $id = (int) $this->_getParam('id');
        $where = $this->_model->getAdapter()->quoteInto('id = ?', $id);
        $this->_model->delete($where);
//        $this->_redirect('equipe/list');
        $this->_helper->redirector->goToRoute(array('controller' => 'tarefa', 'action' => 'index'), null, true);
    }

    public function editAction() {

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $this->_update($data);
            $this->_helper->redirector->goToRoute(array('controller' => 'tarefa', 'action' => 'index'), null, true);
        }

        $tarefa_id = (int) $this->_getParam('id');
        $tarefa = $this->_model->find($tarefa_id);

        if (count($tarefa) == 0) {
            $this->view->message = "<div class='alert alert-error'>Registro n&atilde;o encontrado!</div>";
        }

        $this->view->tarefa = $tarefa->current();
    }

    private function _update($data) {
        $where = $this->_model->getAdapter()->quoteInto('id = ?', (int) $data['id']);
        $dataInicio = new Zend_Date($data['dataInicio'], 'dd/MM/yyyy');
        $dataFim = new Zend_Date($data['dataFim'], 'dd/MM/yyyy');
        $data = array("nome" => $data['nome'],
            "descricao" => $data['descricao'],
            "data_inicio" => $dataInicio->get('yyyy-MM-dd'),
            "data_fim" => $dataFim->get('yyyy-MM-dd'),
            "percentual" => $data['percentual'],
            "situacao" => $data['situacao'],
            "equipe_id" => $data['equipe'],
            "modulo_id" => $data['modulo']
        );
        return $this->_model->update($data, $where);
    }

    public function addAction() {


        if ($this->getRequest()->isPost()) {
            $usuario = $this->getRequest()->getPost('usuario');
            $tarefa_id = $this->getRequest()->getPost('tarefa');
            $dados = array("tarefa_id" => $tarefa_id,
                "usuario_id" => $usuario
            );
            $atividade = new Model_DbTable_Atividade();
            $atividade->insert($dados);
//            $this->view->tarefa = $tarefa->current();
            $this->_helper->redirector->goToRoute(array('controller' => 'tarefa', 'action' => 'edit', 'id' => $tarefa_id), null, true);
        }

        $tarefa_id = (int) $this->_getParam('id');
        $tarefa = $this->_model->find($tarefa_id);

        if (count($tarefa) == 0) {
            $this->view->message = "<div class='alert alert-error'>Registro n&atilde;o encontrado!</div>";
        }

        $this->view->tarefa = $tarefa->current();
    }

    public function removeAction() {

        if ($this->_hasParam('id') == false) {
            $this->_redirect('tarefa');
        }

        $id = (int) $this->_getParam('id');

        $atividade = new Model_DbTable_Atividade();
        $atividade = $atividade->fetchAll("id = " . $id);
        $tarefa_id = $atividade[0]->tarefa_id;
        $atividade = new Model_DbTable_Atividade();
        $where = $this->_model->getAdapter()->quoteInto('id = ?', $id);
        $atividade->delete($where);
        $this->_helper->redirector->goToRoute(array('controller' => 'tarefa', 'action' => 'edit', 'id' => $tarefa_id), null, true);
    }

    private function addEvento($tarefa) {
        Zend_Loader::loadClass('Zend_Gdata_App_HttpException');
        Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
        Zend_Loader::loadClass('Zend_Gdata_Docs');
        Zend_Loader::loadClass('Zend_Gdata');
        Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
        Zend_Loader::loadClass('Zend_Gdata_Calendar');
        Zend_Loader::loadClass('Zend_Http_Client');
        Zend_Loader::loadClass('Zend_Http_Client_Exception');
        Zend_Loader::loadClass('Zend_Http_Client');
        Zend_Loader::loadClass('Zend_Http_Client_Adapter_Proxy');

$clientproxy = new Zend_Http_Client();

// configuracoes para utilizar proxy
$clientproxy->setConfig(array(
        'adapter' => 'Zend_Http_Client_Adapter_Proxy',
        'proxy_host' => '10.8.0.251',
        'proxy_port' => 3128,
        'proxy_user' => '',
        'proxy_pass' => '',
        'sslusecontext' => false,
));

// define a URL da requisicao
$clientproxy->setUri("https://www.google.com:443");

// realiza uma requisicao GET
$response = $clientproxy->request('GET');

// imprime a resposta da requisicao
//echo $response->getBody();
// Configure the proxy connection  
//        $config = array(
//            'adapter' => 'Zend_Http_Client_Adapter_Proxy',
//            'proxy_host' => '10.8.0.251',
//            'proxy_port' => 3128
//        );

// We are setting http://www.google.com:443 as the initial URL since we need to perform  
// ClientLogin authentication first.  
//        $proxiedHttpClient = new Zend_Http_Client('http://www.google.com:443', $config);

        $username = 'cobra.simple.project@gmail.com';
        $password = 'cobra123456';
        $service = Zend_Gdata_Docs::AUTH_SERVICE_NAME;
        
        try {
            if($response->getBody()){
            //print_r($clientproxy);
            $client = Zend_Gdata_ClientLogin::getHttpClient($username, $password, $service, $clientproxy);
            echo 'erro!!!!!';
            $service = new Zend_Gdata_Calendar($client);

            $event = $service->newEventEntry();
            $event->content = $service->newContent($tarefa->nome);
            $event->quickAdd = $service->newQuickAdd("true");
//        $service->insertEvent($event);
            $newEvent = $service->insertEvent($event);
            print_r($newEvent);
            } else {
                echo "ERRO";
            }
        } catch (Zend_Gdata_App_HttpException $httpException) {
            exit("An error occurred trying to connect to the proxy server\n" .
                    $httpException->getMessage() . "\n");
        }
    }

}

