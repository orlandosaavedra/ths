<?php

/**
 * Description of ProductCategoryListView
 *
 * @author orlando
 */
class ProductCategoryListView extends GtkTreeView
{
    const COLUMN_ID=0;
    const COLUMN_NAME=1;
    public function __construct()
    {
        $model = new GtkListStore(GObject::TYPE_LONG, GObject::TYPE_STRING);
        parent::__construct($model);
        $this->build();
    }

    protected function build()
    {
        $colTitles = array('Id', 'Nombre');
        for ($i=0;$i<count($colTitles);++$i){
            $crt = new GtkCellRendererText();
            $col = new GtkTreeViewColumn($colTitles[$i], $crt, 'text', $i);
            if($i=== self::COLUMN_ID){
                $col->set_visible(false);
            }else{
                $col->set_sort_column_id($i);
            }
            
            $this->append_column($col);
        }
    }
    
    public function append(ProductCategory $pcat)
    {
        $model = $this->get_model();
        $row  =array($pcat->id, $pcat->name);
        $model->append($row);
    }
    
    /**
     * 
     * @return \ProductCategory
     */
    public function getSelected()
    {
        list ($model, $iter) = $this->get_selection()->get_selected();
        
        if ($iter !== null){
            $row = new ProductCategory;
            $row->id = $model->get_value($iter, self::COLUMN_ID);
            $row->name = $model->get_value($iter, self::COLUMN_NAME);

            return $row;
        }
    }
    
    public function removeSelected()
    {
        list ($model, $iter) = $this->get_selection()->get_selected();
        
        if ($iter !== null){
            $model->remove($iter);
        }
    }
}
