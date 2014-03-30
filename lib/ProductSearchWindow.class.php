<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProductSearchWindow
 *
 * @author orlando
 */
class ProductSearchWindow extends GtkWindow
{
    /**
     *
     * @var ProductSearchFrame
     */
    public $searchFrame;
    
    public function __construct()
    {
        parent::__construct();
        $this->searchFrame = new ProductSearchFrame();
        $this->searchFrame->listview->get_column(5)->set_visible(false);
        $this->add($this->searchFrame);
        $this->set_title('Buscar producto');
        $this->searchFrame->connect_simple('search', array($this, 'search'));
    }
    
    public function search()
    {
        $search = $this->searchFrame->getSearch();
        $dbm = THSModel::singleton();
        $results = $dbm->searchProduct($search, $this->searchFrame->compatibility->getActiveFilter());
        $this->searchFrame->clear();
        foreach ($results as $product){
            $this->searchFrame->appendResult($product);
        }
    }
}
