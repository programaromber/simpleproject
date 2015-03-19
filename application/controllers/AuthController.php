<?php

class AuthController extends Zend_Controller_Action {

//    public $server = 'smtp.gmail.com';
//    public $config = array(
//        
//        'auth' => 'login',
//        'username' => 'cobra.simple.project@gmail.com',
//        'password' => 'cobra123456',
//        'ssl' => 'tls',
//        'port' => '25'
//    );
    public $server = 'smtp.cobra.com.br';
    public $config = array(
        'auth' => 'login',
        'username' => 'rafael.salles_bs@cobra.com.br',
        'password' => 'cgki91955947287**',
        'ssl' => 'tls', //ssl
        'port' => '25' //465
    );

    public function init() {
        $this->_model = new Model_DbTable_Usuario();
    }

    public function indexAction() {
        // action body
    }

    public function registroAction() {
        if ($this->getRequest()->isPost()) {
            $nome = $this->getRequest()->getPost('nome');
            $email = $this->getRequest()->getPost('email');
            $senha = $this->getRequest()->getPost('senha');
            $equipe_id = $this->getRequest()->getPost('equipe');
            $dados = array("nome" => $nome,
                "email" => $email,
                "senha" => md5($senha),
                "grupo" => "1",
                "data_cadastro" => new Zend_Db_Expr('NOW()'),
                "status" => "1",
                "equipe_id" => $equipe_id
            );
            $usuario = new Model_DbTable_Usuario();
            $usuario->insert($dados);
            $this->view->message = "Cadastrado com sucesso.";
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            //Inicia o adaptador Zend_Auth para banco de dados
            $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
            $authAdapter->setTableName('usuario')
                    ->setIdentityColumn('email')
                    ->setCredentialColumn('senha');
            //->setCredentialTreatment('SHA1(?)');
            //Define os dados para processar o login
            $authAdapter->setIdentity($email)
                    ->setCredential(md5($senha));
            //Efetua o login
            $auth = Zend_Auth::getInstance();
            $result = $auth->authenticate($authAdapter);
            //Verifica se o login foi efetuado com sucesso
//            print_r($result);
//            echo $result->isValid();
            if ($result->isValid()) {
                //Armazena os dados do usuário em sessão, apenas desconsiderando
                //a senha do usuário
                $info = $authAdapter->getResultRowObject(null, 'senha');
                $storage = $auth->getStorage();
                $storage->write($info);
                //Redireciona para o Controller protegido
                return $this->_helper->redirector->goToRoute(array('controller' => 'index'), null, true);
            } else {
                //Dados inválidos
                $this->_helper->FlashMessenger('Usuário ou senha inválidos!');
                $this->_helper->redirector->goToRoute(array('controller' => 'auth', 'action' => 'login'), null, true);
//                 echo "ERRO";
//                $this->_redirect('/auth/login');
            }
            $this->_helper->redirector->goToRoute(array('controller' => 'index', 'action' => 'index'), null, true);
        }
    }

    public function loginAction() {
        
        $msg = (int) $this->_getParam('msg');
        
        if($msg != null || $msg != 0){
            echo "<div class='alert alert-error'>* Efetue login ou registre-se para utilizar o sistema.</div>";
        }
        
        if ($this->getRequest()->isPost()) {
            $email = $this->getRequest()->getPost('auth_email');
            $senha = $this->getRequest()->getPost('auth_senha');
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            //Inicia o adaptador Zend_Auth para banco de dados
            $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
            $authAdapter->setTableName('usuario')
                    ->setIdentityColumn('email')
                    ->setCredentialColumn('senha');
            //->setCredentialTreatment('SHA1(?)');
            //Define os dados para processar o login
            $authAdapter->setIdentity($email)
                    ->setCredential(md5($senha));
            //Efetua o login
            $auth = Zend_Auth::getInstance();
            $result = $auth->authenticate($authAdapter);
            //Verifica se o login foi efetuado com sucesso
//            print_r($result);
//            echo $result->isValid();
            if ($result->isValid()) {
                //Armazena os dados do usuário em sessão, apenas desconsiderando
                //a senha do usuário
                $info = $authAdapter->getResultRowObject(null, 'senha');
                $storage = $auth->getStorage();
                $storage->write($info);
                //Redireciona para o Controller protegido
                return $this->_helper->redirector->goToRoute(array('controller' => 'index'), null, true);
            } else {
                //Dados inválidos
                $this->_helper->FlashMessenger('Usuário ou senha inválidos!');
                $this->_helper->redirector->goToRoute(array('controller' => 'auth', 'action' => 'login'), null, true);
//                 echo "ERRO";
//                $this->_redirect('/auth/login');
            }
        }
    }

    public function logoutAction() {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->redirector->goToRoute(array('controller' => 'index', 'action' => 'index'), null, true);
    }

    public function resetAction() {
        if ($this->getRequest()->isPost()) {
            $email = $this->getRequest()->getPost('reset_email');
            $usuario = new Model_DbTable_Usuario();
            $update = new Model_DbTable_Usuario();
            $usuarios = $usuario->fetchAll("email = '$email'", null, null, null);
            $usuario = $usuarios[0];
            $senha = $this->_geraSenha();
//            echo $usuario->id . " - " . $usuario->nome . " \n Nova Senha: $senha \n";

            $array = array(
                "senha" => md5($senha)
            );
//            print_r($array);
            $where = $update->getAdapter()->quoteInto('id = ?', (int) $usuario->id);
            $update->update($array, $where);
//            $result = mail($email, "Cobra Project: Troca de senha", "<b>Sua nova senha: $senha</b>");
//            if ($result) {
//                $this->_helper->redirector->goToRoute(array('controller' => 'auth', 'action' => 'login', 'msg' => 1), null, true);
//            } else {
//                echo "<div class='alert alert-error'>* Não foi possível resetar sua senha, contate o administrador ou o seu coordenador para solicitar uma nova senha!</div>";
//            }
            //Email
            Zend_Loader::loadClass('Zend_Mail_Transport_Smtp');
            Zend_Loader::loadClass('Zend_Mail');
            $transporte = new Zend_Mail_Transport_Smtp($this->server, $this->config); 
            $mail = new Zend_Mail();
           $result = $mail->setFrom("cobra.simple.project@gmail.com", "Contato Cobra Project")   // Quem esta enviando
                    ->addTo($email, $email)
                    ->setBodyText("<b>Sua nova senha: $senha</b>")             // mensagem sem formata?
                    ->setSubject("Cobra Project: Troca de senha")               // Assunto
                    ->send($transporte);
             if ($result) {
                echo 'Seu email foi enviado com sucesso.';
            } else {
                echo 'Não foi possível enviar seu email.';
            }
//            //Email
//            $this->_helper->redirector->goToRoute(array('controller' => 'auth', 'action' => 'login'), null, true);
        }
    }

    public function calendarioAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $tarefa = new Model_DbTable_Tarefa();
        $tarefas = $tarefa->fetchAll($where, null, null, null);
        $json = array();
        for ($i = 0; $i < count($tarefas); $i++) {
            $dataInicio = new Zend_Date($tarefas[$i]->data_inicio);
            $dataFim = new Zend_Date($tarefas[$i]->data_fim);
            $bg = "";
            $situacao = "";
            switch ($tarefas[$i]->situacao) {
                case '1':
                    $bg = 'rgb(58, 135, 173)';
                    $situacao = "CADASTRADA";
                    break;
                case '2':
                    $bg = 'rgb(248, 148, 6)';
                    $situacao = "EM EXECUÇAO";
                    break;
                case '3':
                    $bg = 'rgb(51, 51, 51)';
                    $situacao = "PENDENTE";
                    break;
                case '4':
                    $bg = 'rgb(70, 136, 71)';
                    $situacao = "CONCLUIDA";
                    break;
            }
            $json[$i] = array(
                'id' => $tarefas[$i]->id,
                'title' => $tarefas[$i]->nome,
                'start' => $dataInicio->get('yyyy-MM-dd'),
                'end' => $dataFim->get('yyyy-MM-dd'),
                'url' => "/cobraproject/public/tarefa/edit/id/" . $tarefas[$i]->id,
                'backgroundColor' => $bg,
                'borderColor' => $bg
            );
        }


        echo json_encode($json);
    }

    /**
     * Função para gerar senhas aleatórias
     *
     * @author    Thiago Belem <contato@thiagobelem.net>
     *
     * @param integer $tamanho Tamanho da senha a ser gerada
     * @param boolean $maiusculas Se terá letras maiúsculas
     * @param boolean $numeros Se terá números
     * @param boolean $simbolos Se terá símbolos
     *
     * @return string A senha gerada
     */
    private function _geraSenha() {
        $lmai = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $num = '1234567890';
        $simb = '!@#$%*-'; //$simb = '!@#$%*-';
        $retorno = '';
        $caracteres = '';
        $caracteres .= $lmai;
        $caracteres .= $num;
        $caracteres .= $simb;
        $len = strlen($caracteres);
        for ($n = 1; $n <= 8; $n++) {
            $rand = mt_rand(1, $len);
            $retorno .= $caracteres[$rand - 1];
        }
        return $retorno;
    }

}

