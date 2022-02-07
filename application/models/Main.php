<?php

    namespace application\models;

    use application\lib\D;

    class Main{

        public function testFunction(){
           return D::findAll('users');
        }

    }