<?php


namespace application\classes;


class Paginator {
    private $page;
    private $pages;
    private $windowCentr;
    private $window;
    private $totalRows;
    private $onPage;

    public function __construct(array $models,$onPage,$window){
        $this->totalRows=$models['ads']->getNumberOfAds($models['users']->get()['id']);
        $this->page = (isset($_GET['page'])&&(+$_GET['page']>0)) ? ((int) $_GET['page']) : 1;
        $this->models=$models;
        $this->window=$window;
        $this->onPage=$onPage;
        $this->windowCentr=floor($window/2);
        $this->pages=ceil($this->totalRows/$onPage);

        if($this->page==1){
            $this->limiOffset=0;
        }else{
            $this->limiOffset=$onPage*($this->page-1);
        }
    }

    public function getData(){
       return $this->models['ads']->getAdsByUserId($this->models['users']->get()['id'],$this->limiOffset,$this->onPage);
    }

    public function render(){

        $page=$this->page;
        $window=$this->window;
        $windowCentr=$this->windowCentr;
        $pages=$this->pages;
        $i=0;


        ob_start();
        include_once '../views/site/paginator.phtml';
        $content = ob_get_clean();

        return $content;
    }
} 