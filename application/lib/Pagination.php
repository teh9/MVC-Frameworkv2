<?php

    namespace application\lib;

    class Pagination {

        private $max = 10;
        private $route;
        private $index = '';
        private $current_page;
        private $total;
        private $limit;

        public function __construct($route, $total, $limit = 10) {

            $this->route = $route;
            $this->total = $total;
            $this->limit = $limit;

            $this->amount = $this->amount();

            $this->setCurrentPage();
        }

        public function get() {

            $links = null;

            $html = '<nav><ul class="pagination justify-content-center">';

            if ($this->amount() <= 10){
                for ($counter = 1; $counter <= $this->amount(); $counter++){
                    if ($counter == $this->current_page) {
                        $links .= $this->generateHtml($counter,true);
                    }else{
                        $links .= $this->generateHtml($counter);
                    }
                }
            }elseif($this->amount() > 10){

                if($this->current_page <= 4) {
                    for ($counter = 1; $counter < 8; $counter++){
                        if ($counter == $this->current_page) {
                            $links .= $this->generateHtml($counter,true);
                        }else{
                            $links .= $this->generateHtml($counter);
                        }
                    }
                    $links .= "<li class='page-item'><a class='page-link'>...</a></li>";
                    $links .= $this->generateHtml($this->amount() - 1);
                    $links .= $this->generateHtml($this->amount());
                }elseif($this->current_page > 4 && $this->current_page < $this->amount() - 4) {
                    $links .= $this->generateHtml(1);
                    $links .= $this->generateHtml(2);
                    $links .= "<li class='page-item'><a class='page-link'>...</a></li>";
                    for ($counter = $this->current_page - 2; $counter <= $this->current_page + 2; $counter++) {
                        if ($counter == $this->current_page) {
                            $links .= $this->generateHtml($counter,true);
                        }else{
                            $links .= $this->generateHtml($counter);
                        }
                    }
                    $links .= "<li class='page-item'><a class='page-link'>...</a></li>";
                    $links .= $this->generateHtml($this->amount() - 1);
                    $links .= $this->generateHtml($this->amount());
                }else {
                    $links .= $this->generateHtml(1);
                    $links .= $this->generateHtml(2);
                    $links .= "<li class='page-item'><a class='page-link'>...</a></li>";

                    for ($counter = $this->amount() - 6; $counter <= $this->amount(); $counter++) {
                        if ($counter == $this->current_page) {
                            $links .= $this->generateHtml($counter,true);
                        }else{
                            $links .= $this->generateHtml($counter);
                        }
                    }
                }
            }

            $html .= $links.' </ul></nav>';

            return $html;
        }

        private function generateHtml($page, $active = false, $text = null) {

            if (!$text) {
                $text = $page;
            }

            if($active == false){
                $active = '';
            }else{
                $active = ' active';
            }

            return '<li class="page-item '.$active.'"><a class="page-link" href="/'.$this->route['action'].'/'.$page.'">'.$text.'</a></li>';
        }

        private function setCurrentPage() {

            if (isset($this->route['page'])) {
                $currentPage = $this->route['page'];
            } else {
                $currentPage = 1;
            }

            $this->current_page = $currentPage;

            $this->prev = $this->current_page - 1;
            $this->next = $this->current_page + 1;

            if ($this->current_page > 0) {
                if ($this->current_page > $this->amount) {
                    $this->current_page = $this->amount;
                }
            } else {
                $this->current_page = 1;
            }
        }

        private function amount() {

            return ceil($this->total / $this->limit);
        }
    }