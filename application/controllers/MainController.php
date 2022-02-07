<?php

    namespace application\controllers;

    use application\core\Controller;
    use application\lib\D;

    class MainController extends Controller {

        public function indexAction(){

            $this->view->render('TEST');
        }

    }