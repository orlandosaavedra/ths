<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BranchesComboBox
 *
 * @author orlando
 */
class BranchesComboBox extends GtkComboBox
{
    public function __construct()
    {
        $liststore = new GtkListStore(GObject::TYPE_LONG,GObject::TYPE_STRING);
        parent::__construct($liststore);
        $cellr = new GtkCellRendererText();
        $this->pack_start($cellr);
        $this->set_attributes($cellr, 'text', 1);
    }
    
    public function populate()
    {
        $model = $this->get_model();
        $model->clear();
        
        $dbm = new THSModel();
        $branches = $dbm->getBranches();
        $dbm->close();
        
        foreach ($branches as $branch){
            $model->append(array($branch->id, $branch->name));
        }
    }
    
    /**
     * 
     * @return Branch
     */
    public function getSelected()
    {
        $iter = $this->get_active_iter();
        //If the selection is null return false.
        if ($iter === null){
            return false;
        }
        
        $model = $this->get_model();
        
        $branch = Branch::retrieve($model->get_value($iter, 0));
        
        return $branch;
    }
}
