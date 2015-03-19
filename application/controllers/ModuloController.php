<?php

class ModuloController extends Zend_Controller_Action {

    public $where = null;

    public function init() {
        parent::init();
        $auth = Zend_Auth::getInstance()->getIdentity();
        if (!$auth) {
            $this->_helper->redirector->goToRoute(array('controller' => 'auth', 'action' => 'login', 'msg' => 1), null, true);
        }
        $this->_model = new Model_DbTable_Modulo();
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
//            $this->where = "";
            if (!empty($nome)) {
                $this->where[] = "nome like '%$nome%'";
            }
            $dataPrazo = $this->getRequest()->getPost('dataEntrega');
            if (!empty($dataPrazo)) {
                $data = explode("/", $dataPrazo);
                $dataPrazo = $data[2] . "-" . $data[1] . "-" . $data[0];
                $this->where[] = "data_entrega = '" . $dataPrazo . "'";
            }

            $this->paginacao();
        } else if ($this->getRequest()->isGet()) {
            $this->paginacao();
        }
    }

    private function paginacao() {
        $pagina = $this->_request->getParam("pagina", "1");
        $porPagina = $this->_request->getParam("por-pagina", "5");
        $rangePaginas = $this->_request->getParam("range-pagina", "5");
        $modulos = $this->_model->fetchAll($this->where, null, null, null);
        if ($modulos->count() > 0) {
            $paginator = Zend_Paginator::factory($modulos);
            $paginator->setCurrentPageNumber($pagina);
            $paginator->setItemCountPerPage($porPagina);
            $paginator->setPageRange($rangePaginas);
            $this->view->modulos = $paginator;
        } else {
            $this->view->modulos = null;
        }
    }

    public function createAction() {
        if ($this->getRequest()->isPost()) {
            $nome = $this->getRequest()->getPost('nome');
            $descricao = $this->getRequest()->getPost('descricao');

            $dataPrazo = new Zend_Date($this->getRequest()->getPost('dataPrazo'), 'dd/MM/yyyy');
            //$dataPrazo = date($this->getRequest()->getPost('dataPrazo');
//            $dataPrazo->setTimezone('America/Belem');
            $dados = array("nome" => $nome,
                "descricao" => $descricao,
                "data_entrega" => $dataPrazo->get('yyyy-MM-dd')
            );
            $modulo = new Model_DbTable_Modulo();
            $modulo->insert($dados);
            $this->view->message = "Cadastrado com sucesso.";
            $this->_helper->redirector->goToRoute(array('controller' => 'modulo', 'action' => 'index'), null, true);
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
            $this->_redirect('modulo');
        }

        $id = (int) $this->_getParam('id');
        $where = $this->_model->getAdapter()->quoteInto('id = ?', $id);
        $this->_model->delete($where);
//        $this->_redirect('equipe/list');
        $this->_helper->redirector->goToRoute(array('controller' => 'modulo', 'action' => 'index'), null, true);
    }

    public function editAction() {

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $this->_update($data);
            $this->_helper->redirector->goToRoute(array('controller' => 'modulo', 'action' => 'index'), null, true);
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
        $dataPrazo = new Zend_Date($data['dataEntrega'], 'dd/MM/yyyy');
        $data = array(
            'nome' => $data['nome'],
            "descricao" => $data['descricao'],
            "data_entrega" => $dataPrazo->get('yyyy-MM-dd')
        );
        return $this->_model->update($data, $where);
    }

    public function relatorioAction() {

        
            $where = "";
            $modulos = null;
            $modulo_id = (int) $this->_getParam('id');
            if ($modulo_id != null || $modulo_id != 0) {
                $where = " id = " . $modulo_id;
                $modulos = $this->_model->fetchAll($where, null, null, null);
            } else {
                $modulos = $this->_model->fetchAll();
            }
            $this->view->modulos = $modulos;
            $this->view->modulo_id = $modulo_id;

        
    }
    
    
    public function pdfAction(){
             $where = "";
            $modulos = null;
            $modulo_id = (int) $this->_getParam('id');
            if ($modulo_id != null || $modulo_id != 0) {
                $where = " id = " . $modulo_id;
                $modulos = $this->_model->fetchAll($where, null, null, null);
            } else {
                $modulos = $this->_model->fetchAll();
            }
            $html = "

<script>
    <!--
    $(document).ready(function(){
        $('#dataEntrega').mask('99/99/9999');
        $('#dataInicio').mask('99/99/9999');
        $('#dataFim').mask('99/99/9999');
        $('#myModal').hide();
    });
    $(function() {
        $( '#dataEntrega' ).datepicker();
    });
    -->
</script>
<div class='form_description' align='center'>
      <h2>RELATÓRIO DE ATIVIDADES</h2>
</div>
<br/>
<div style='background-color: grey; font-size: 8px; width: 100%'><br/></div>
<br/>
<div>";
            if (count($modulos) > 0) {

                foreach ($modulos as $row) {
                    $dataEntrega = new Zend_Date($row->data_entrega);
                    $tarefas = $row->findDependentRowset('Model_DbTable_Tarefa');
                    $percentual = 0;
                    $total = count($tarefas) * 100;
                    foreach ($tarefas as $tarefa) {
                        $percentual = $percentual + $tarefa->percentual;
                    }
                    $percentual = ($percentual / $total ) * 100;
                    $html .= "
                <table align='center' width='100%' cellspacing='1' cellpadding='1' border='0'>
                    <thead>
                        <tr>
                            <th>Modulo</th>
                            <th>Data da entrega</th>
                            <th>Progresso</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>" .$row->id ." - ". $row->nome . "</td>
                        <td>" . $dataEntrega->get('dd/MM/yyyy') . "</td>
                        <td>" . number_format($percentual, 0) . "%</td>
                    </tr>
                    </tbody>
                 </table>
                 ";
                    if (count($tarefas) > 0) {
                        $html .= "
                     <table width='100%'  cellspacing='1' cellpadding='1' border='0'>
                                    <thead>
                                        <tr>
                                            <th width='30%'>Tarefa</th>
                                            <th>Equipe</th>
                                            <th>Data Inicio</th>
                                            <th>Data Fim</th>
                                            <th>Percentual</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    ";
                        foreach ($tarefas as $tarefa) {
                            $dataInicio = new Zend_Date($tarefa->data_inicio);
                            $dataFim = new Zend_Date($tarefa->data_fim);
                            $equipe = $tarefa->findParentRow('Model_DbTable_Equipe', 'Equipe');

                            $html .= "
                                            <tr>
                                                <td>" . $tarefa->id . " - " . $tarefa->nome . "</td>
                                                <td>" . $equipe->nome . "</td>
                                                <td>" . $dataInicio->get('dd/MM/yyyy') . "</td>
                                                <td>" . $dataFim->get('dd/MM/yyyy') . "</td>
                                                <td>" . $tarefa->percentual . "%</td>
                                            </tr>";
                        }
                        $html .= " </tbody></table>";
                    } else {
                        $html .= "
                        <table width='100%'  cellspacing='1' cellpadding='1' border='0'>
                           <thead>
                               <tr>
                                  <th><p>Nenhum registro encontrado.</p></th>
                               </tr>
                           </thead>
                        </table>";
                    }

                    $html .= "<br/>
                <div style='background-color: grey; font-size: 6px; width: 100%'><br/></div>
                <br/>";
                }
            } else {
                $html .= "
            <table width='100%'  cellspacing='1' cellpadding='1' border='0'>
                <thead>
                    <tr>
                        <th><p>Nenhum registro encontrado.</p></th>
                    </tr>
                </thead>
             </table>";
            }
            $html .= "</div>";

            Zend_Loader::loadClass('Zend_Pdf');


            $html = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
            <title>Relatório</title>
        <style>
        
table				{font: 88%/1.7em 'Trebuchet MS', 'Bitstream Vera Sans', Verdana, Helvetica, sans-serif;
					border-collapse: separate; border-spacing: 0; margin: 0 0 1em 0; color:#000;}

a 					{color: #09f; text-decoration: none; border-bottom: 1px solid;}
a:visited 			{color: #c3c; font-weight: normal;}
a:hover 			{border-bottom-style: dotted;}

thead th,
thead td			{font-weight: bold; line-height:normal; text-align: left; border-bottom: 0.4em solid #09f;}

tfoot th,
tfoot td			{text-align: left; border-top: 0.4em solid #09f; font-weight: bold}

th,
td 					{padding: 0.25em;}

tbody th,
td					{text-align: left; vertical-align: top;}
tbody th			{font-weight: normal; white-space: nowrap;}

tbody th a:link,
tbody th a:visited 	{font-weight: bold;}

tbody th + td		{white-space: nowrap;}

tbody td,
tbody th 			{border: 1px solid #fff; border-width: 1px 0;}

tbody tr.odd th,
tbody tr.odd td 	{border-color: #deded8; background: #f9f9fb;}

tbody tr:hover td,
tbody tr:hover th 	{background: #fbfbf8;}
caption 			{font-weight: bold; font-size: 1.7em; text-align: left; margin: 0; padding: 0.5em 0.25em;}

td + td + td + td {white-space: nowrap;}
td + td + td + td a:before {content:'\2193 ';}
a[href^='http://']:not([href*='http://icant.co.uk'])::after {content: '\2197'; }
td + td + td + td a[href^='http://']:not([href*='http://icant.co.uk'])::after {content: ''; }

        </style>
        </head><body>$html</body></html>";
            require_once 'dompdf6/dompdf_config.inc.php';
            require_once 'Zend/Loader/Autoloader.php';
            require_once('Zend/Pdf.php');
            $autoloader = Zend_Loader_Autoloader::getInstance(); // assuming we're in a controller 
            $autoloader->pushAutoloader('DOMPDF_autoload');
            $dompdf = new DOMPDF();


            $dompdf->load_html($html);
            $dompdf->set_paper('letter', 'landscape');
            $this->view->dompdf = $dompdf;
            $dompdf->render();
            $this->_helper->layout->disableLayout();
//        $this->_helper->layout->setLayout('prints');
            $this->_helper->viewRenderer->setNoRender();
//        echo $pdf;
            $tym = date('g:i s');
            $filename = 'modulo' . $tym;
            $dompdf->stream($filename . ".pdf", array("Attachment" => 0));
//        $pdf = new Zend_Pdf();
//        $pdf = Zend_Pdf::parse("TESTE");
//        echo $pdf->render();
//print_r($html);
        exit;
    }

}

