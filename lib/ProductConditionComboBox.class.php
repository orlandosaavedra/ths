<?php

/**
 * Description of ProductConditionComboBox
 *
 * @author orlando
 */
class ProductConditionComboBox extends GtkComboBox
{
    public function __construct()
    {
        $liststore = new GtkListStore(GObject::TYPE_LONG, GObject::TYPE_STRING);
        parent::__construct($liststore);
        $liststore->append(array(Product::STATE_NEW, 'Nuevo'));
        $liststore->append(array(Product::STATE_USED, 'Usado'));
        $crt = new GtkCellRendererText();
        $this->pack_start($crt);
        $this->set_attributes($crt, 'text', 1);
    }
    
    public function getActive()
    {
        $iter = $this->get_active_iter();
        if ($iter){
            $this->get_model()->get_value($iter, 0);
        }else{
            return null;
        }
    }
    
    
    public function setActive($condition)
    {
        $iter = $this->get_model()->get_iter_first();
        
        if ($condition === Product::STATE_NEW){            
            $this->set_active_iter($iter);
        }else{
            $this->set_active_iter($this->get_model()->iter_next($iter));
        }
    }
}
