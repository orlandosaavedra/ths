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
        foreach ($results as $product_id){
            $product = Product::getFromId($product_id);
            $this->searchFrame->appendResult($product);
        }
    }
}
