<?php
/**
 * Description of CategoriesComboBox
 *
 * @author orlando
 */
class ProductCategoryComboBox extends GtkComboBox
{
    public function __construct()
    {
        $liststore= new GtkListStore(GObject::TYPE_LONG, GObject::TYPE_STRING);
        parent::__construct($liststore);
        $cellr = new GtkCellRendererText();
        $this->pack_start($cellr);
        $this->set_attributes($cellr, 'text', 1);
    }
    
    public function fetch()
    {
        $dbm = THSModel::singleton();
        $categories = $dbm->getProductCategories();
        $model = $this->get_model();
        $model->clear();
        
        foreach ($categories as $cat){
            $model->append(array($cat->id, $cat->name));
        }
    }
    
    /**
     * @return Category
     */
    public function getActive()
    {
        $iter = $this->get_active_iter();
        
        if (null === $iter){
            return null;
        }
        
        $model = $this->get_model();
        
        $a = new ProductCategory();
        $a->id = $model->get_value($iter, 0);
        $a->name = $model->get_value($iter, 1);
        
        return $a;
    }
    
    /**
     * 
     * @param integer $cat_id
     * @return boolean true in success, false if fail
     */
    public function setActive($cat_id)
    {
        $model = $this->get_model();
        $iter = $model->get_iter_first();
        
        while ($iter != null){
            $val = $model->get_value($iter, 0);
            if ($val == $cat_id){
                $this->set_active_iter($iter);
                return true;
            }
            $iter = $model->iter_next($iter);
        }
        
        return false;
    }

}
