<?php

/**
 * Product class to encapsulate DB products
 *
 * @author orlando
 */
class Product 
{
    const STATE_NEW=1;
    const STATE_USED=2;
    
    const STOCK_TOTAL=0;
    
    public $id=null;
    public $partnumber=null;
    public $state=null;
    public $description=null;
    public $cost=null;
    public $price=null;
    public $category=null;
    public $stock=array();
    public $category_id=null;
    
    /**
     * 
     * @param integer $pid
     * @return Product
     */
    public static function getFromId($pid)
    {
        if ($pid == null){
            throw new Exception('Product::id must be integer greater than 0');
        }
                
        $dbm = THSModel::singleton();
        
        $product = $dbm->getProduct($pid);
        
        return $product;
    }
    
    public function getStock($branch_id=Product::STOCK_TOTAL)
    {
        $dbm = THSModel::singleton();
        $stock = $dbm->getProductStock($this->id);
        
        return $stock[$branch_id];
    }
    
    public function getCompatibility()
    {
        
    }
    
    public function getCategory()
    {
        $dbm = THSModel::singleton();
        $category = $dbm->getProductCategory($this->id);
    }
    
    public function setCategory()
    {
        
    }

}
