<?php

/**
 * Description of ProductCompatibilityNewPanelComboBox
 *
 * @author orlandoa
 */
class ProductCompatibilityEntry extends GtkEntry
{    
    public function setCompletion($values)
    {
        $completion = new GtkEntryCompletion();
        $liststore = new GtkListStore(GObject::TYPE_STRING);
        $completion->set_model($liststore);
        
        foreach ($values as $value){
            $liststore->append(array($value));
        }
        
        $completion->set_inline_completion(true);
        
        $this->set_completion($completion);
    }
    
    
    public function clearEntry()
    {
        $model = $this->get_model();
        $model->clear();
        $model->append(array(''));
        $this->set_active(0);
        $model->clear();
    }
    
    public function autocomplete()
    {
        exit();
        $model = $this->get_model();
        $iter = $model->get_iter_first();
        $str = $this->get_active_text();
        
        if (strlen($str)<3){
            return false;
        }
        
        echo 'hola'.PHP_EOL;
        
        while ($iter){
            $value = $model->get_value($iter, 0);
            if (!preg_match("/^$str/i", $value)){
                $model->remove($iter);
            }
            
            $iter = $model->iter_next($iter);
        }
        
        $this->popup();
    }
}
