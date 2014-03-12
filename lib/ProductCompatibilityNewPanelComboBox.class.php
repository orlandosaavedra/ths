<?php

/**
 * Description of ProductCompatibilityNewPanelComboBox
 *
 * @author orlando
 */
class ProductCompatibilityNewPanelComboBox extends GtkComboBoxEntry
{
    public function __construct()
    {
        $model = new GtkListStore(GObject::TYPE_STRING);
        parent::__construct();
        $this->set_text_column(0);
        $this->clear();
        $this->set_model($model);
        $crt = new GtkCellRendererText();
        $this->pack_start($crt);
        $this->set_attributes($crt, 'text', 0);
        
    }
    
    
    public function clearEntry()
    {
        $model = $this->get_model();
        $model->clear();
        $model->append(array(''));
        $this->set_active(0);
        $model->clear();
    }
}
