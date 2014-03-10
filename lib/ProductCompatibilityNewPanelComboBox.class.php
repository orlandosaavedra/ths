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
        parent::__construct($model);
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
