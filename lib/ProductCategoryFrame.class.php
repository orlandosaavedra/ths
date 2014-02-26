<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProductCategoryFrame
 *
 * @author orlando
 */
class ProductCategoryFrame extends GtkFrame
{
    
    public $__gsignals = array(
        'lock' => 
            array(
            GObject::SIGNAL_RUN_LAST,
            GObject::TYPE_BOOLEAN,
            array(GObject::TYPE_LONG, GtkRequisition::gtype))
    );
    /**
     *
     * @var CategoriesComboBox
     */
    public $combo;
    
    public function __construct()
    {
        parent::__construct('Categoria');
        $this->set_border_width(3);
        $this->combo = new CategoriesComboBox;
        $this->connect_simple('lock', array($this->combo, 'set_sensitive'), false);
        
        $confbtn = new GtkButton('');
        $image = GtkImage::new_from_icon_name(Gtk::STOCK_PREFERENCES, Gtk::ICON_SIZE_BUTTON);
        $label = $confbtn->get_child();
        $label->destroy();
        $confbtn->add($image);
        $confbtn->connect_simple('clicked', array($this, 'modifyCategories'));
        
        $vbox = new GtkVbox();
        $hbox = new GtkHBox;
        $hbox->pack_start($this->combo);
        $hbox->pack_start($confbtn, false, false);
        
        
        $vbox->pack_start($hbox);
        
        $this->add($vbox);
        $this->populate();
    }
    
    public function modifyCategories()
    {
        $win = new CategoriesWindow(CategoriesWindow::CATEGORY_FRAME);
        $win->set_transient_for($this->get_toplevel());
        $win->set_modal(true);
        $win->connect_simple('destroy', array($this, 'populate'));
        $win->show_all();
    }
    
    public function populate()
    {
        $this->combo->populate();
    }
    
    /**
     * Returns id of the selected category
     * @return type
     */
    public function getSelectedCategory()
    {
        $dbm = new THSModel();
        $available = $dbm->getProductCategories();
        $selected = $this->combo->getActive();
        return $selected;
    }
    
    public function lock()
    {
        $this->emit('lock');
       
    }
}

GObject::register_type('ProductCategoryFrame');