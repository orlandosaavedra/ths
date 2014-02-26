<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IncomeWindow
 *
 * @author orlando
 */
class ProductsWindow extends GtkWindow
{   
    
    public $__gsignals = array(
        'create' => array(
            GObject::SIGNAL_RUN_LAST,
            GObject::TYPE_BOOLEAN,
            array(GObject::TYPE_LONG, GtkRequisition::gtype)),
        'modify' => array(
            GObject::SIGNAL_RUN_LAST,
            GObject::TYPE_BOOLEAN,
            array(GObject::TYPE_LONG, GtkRequisition::gtype)),
        'stock' => array(
            GObject::SIGNAL_RUN_LAST,
            GObject::TYPE_BOOLEAN,
            array(GObject::TYPE_LONG, GtkRequisition::gtype))
    );
    
    public function __construct()
    {
        parent::__construct();
        
        $this->_build();
    }
    
    public function _build()
    {        
        $b = new GtkButton('Crear');
        $b->connect_simple('clicked', array($this, 'emit'), 'create');
        $c = new GtkButton('Modificar');
        $c->connect_simple('clicked', array($this, 'emit'), 'modify');
        $d = new GtkButton('Stock');
        $d->connect_simple('clicked', array($this, 'emit'), 'stock');
        
        $hbox = new GtkHBox;
        $hbox->pack_start($b);
        $hbox->pack_start($c);
        $hbox->pack_start($d);
        $this->add($hbox);
        $this->set_size_request(400, 50);
        $this->set_title('THS - Productos');
        $this->set_position(Gtk::WIN_POS_CENTER_ON_PARENT);
    }
}

GObject::register_type('ProductsWindow');