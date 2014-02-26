<?php
/**
 * Description of CartFrame
 *
 * @author orlando
 */

class ProductCartFrame extends GtkFrame
{
    /**
     *
     * @var GtkTreeView
     */
    private $_view;
    
    /**
     *
     * @var GtkLabel
     */
    public $total;
    
    public $__gsignals = array(
        'quote' => array(
            GObject::SIGNAL_RUN_LAST,
            GObject::TYPE_BOOLEAN,
            array(GObject::TYPE_LONG, GtkRequisition::gtype)),
        'sell' => array(
            GObject::SIGNAL_RUN_LAST,
            GObject::TYPE_BOOLEAN,
            array(GObject::TYPE_LONG, GtkRequisition::gtype))
    );
    
    public function __construct()
    {
        parent::__construct();
        $this->_createLayout();
    }
    
    /**
     * Returns a vbox with all the side buttons
     */
    private function _createSidePanel()
    {
        $vbox = new GtkVbox();
        $delbtn = new GtkButton('Eliminar');
        $delbtn->set_size_request(170, 30);
        $delbtn->connect_simple('clicked', array($this, 'delete'));
        
        $vbox->pack_start($delbtn, false, false);
        
        $quotebtn = new GtkButton('Generar cotización');
        $quotebtn->connect_simple('clicked', array($this, 'emit'), 'quote');
        
        $vbox->pack_start($quotebtn, false, false);
        
        
        $checkbtn = new GtkButton('Realizar Venta');
        $checkbtn->connect_simple('clicked', array($this, 'emit'), 'sell');
        
        $vbox->pack_start($checkbtn, false, false);
        
        $hbox = new GtkHBox(true);
        $hbox->pack_start(new GtkLabel('Total: '));
        $hbox->pack_start($this->total = new GtkEntry('0'));
        $vbox->pack_end($hbox, false, false);
        
        $hbox  =new GtkHBox(true);
        $hbox->pack_start(new GtkLabel('IVA:'));
        $hbox->pack_start($this->tax = new GtkEntry('0'));
        $vbox->pack_end($hbox, false, false);
        
        $hbox = new GtkHBox(true);
        $hbox->pack_start(new GtkLabel('Descuento:'));
        $hbox->pack_start($this->discount = new GtkEntry('0'));
        $vbox->pack_end($hbox, false, false);
        
        $hbox = new GtkHBox(true);
        $hbox->pack_start(new GtkLabel('Subtotal:'));
        $hbox->pack_start($this->subtotal = new GtkEntry('0'));
        $vbox->pack_end($hbox, false, false);
        
        $this->subtotal->set_alignment(1);
        $this->subtotal->set_editable(false);
        $this->tax->set_alignment(1);
        $this->tax->set_editable(false);
        $this->tax->connect('key-press-event', array('Main', 'restrictNumbersOnly'));
        $this->total->set_alignment(1);
        $this->total->connect('key-press-event', array('Main', 'restrictNumbersOnly'));
        $this->total->connect_after('key-release-event', array($this, 'recalc'));
        $this->discount->set_alignment(1);
        $this->discount->connect('key-press-event', array('Main', 'restrictNumbersOnly'));
        $this->discount->connect_after('key-release-event', array($this, 'recalc'));
        
        return $vbox;
    }
    
    /**
     * 
     */
    private function _createLayout()
    {
        $vbox = new GtkVBox;
        $hbox = new GtkHBox;
        
        $hbox->pack_start($this->_createListView());        
        $hbox->pack_start($this->_createSidePanel(), false);
        
        $this->add($hbox);
    }
    
    /**
     * Returns all rows (raw data) contained in the cart
     * @return array
     */
    public function getRows()
    {
        $data = array();
        
        $iter = $this->_view->get_model()->iter_children();
        
        while ($iter != null){
            /* Clear the array */
            $row = array();
            /* Add the iter */
            $row[] = $iter;
            /* Values */
            for ($i=0;$i<$this->_view->get_model()->get_n_columns();++$i){
                $row[] = $this->_view->get_model()->get_value($iter, $i);
            }
            $data[] =$row;
            $iter =$this->_view->get_model()->iter_next($iter);
        }
        
        return $data;
    }
    
    /**
     * 
     * @return \Product
     */
    public function getProducts()
    {
        $rows = $this->getRows();
        $products = array();
        foreach ($rows as $row){
            $product = Product::getFromId($row[1]);
            $product->price = $row[4];
            $product->qty = $row[5];
            $products[] =$product;
        }
        
        return $products;
    }
    
    /**
     * Recalculates Total
     */
    public function recalc($changed=null)
    {
        if (null === $changed){
            $changed = $this->discount;
        }
        
        $model = $this->_view->get_model();
        $iter = $model->iter_children();
        $subtotal = 0;
        
        while ($iter !== null){
            $q = (int) $model->get_value($iter, 4);
            $subtotal += (int) $model->get_value($iter, 3) * $q;
            $iter = $model->iter_next($iter);
        }
        
        $this->subtotal->set_text("$subtotal");
        
        if ($changed === $this->discount){
            $discount = $this->discount->get_text();
            $total = $subtotal-$discount;
            $tax = round($total*0.19, 0);
            $total = $total+$tax;
            $this->tax->set_text($tax);
            $this->total->set_text($total);
        }else{
            $total = $this->total->get_text();
            $tax = round($total - ($total/1.19), 0);
            $this->tax->set_text($tax);
            
            $asubtotal = $total - $tax;
            
            if ($subtotal < $asubtotal){
                
            }else if ($subtotal>$asubtotal){
                $discount = $subtotal-$asubtotal;
                $this->discount->set_text($discount);
            }
            
        }
        
        return false;
    }
    
    /**
     * 
     * @param array $data
     */
    public function append(Product $product)
    {
        /* Get the rows data to compare */
        $model = $this->_view->get_model();
        $iter = $model->get_iter_first();
        
        while ($iter != null){
            $id = $model->get_value($iter, 0);
            if ($id === (integer)$product->id){
                $nitems = (int)$model->get_value($iter, 4);
                $model->set($iter, 4, ++$nitems);
                $this->recalc();
                return;
            }
            
            $iter = $model->iter_next($iter);
        }
        
        /* At this point no match was found */
        $data = array($product->id, $product->partnumber, $product->description, $product->price);
        $data[] = 1;
        $model->append($data);
        $this->recalc();
        return;
    }
    
    public function delete()
    {
        list($model, $iter) = $this->_view->get_selection()->get_selected();
        
        if (!is_object($iter)){
            return false;
        }
        
        $model->remove($iter);
        $this->recalc();
    }
    
    /**
     * Creates the GtkTreeView and encapsulate it into a GtkScrolledWindow
     * @return GtkScrolledWindow
     */
    private function _createListView()
    {
        $scrwin = new GtkScrolledWindow();
        $scrwin->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
        
        $this->_model = new GtkListStore(
                GObject::TYPE_LONG,   // id
                GObject::TYPE_STRING, //PN
                GObject::TYPE_STRING, //descripcion
                GObject::TYPE_LONG, //precio
                Gobject::TYPE_LONG //Cantidad
        );
        
        $this->_view = new GtkTreeView($this->_model);
        
        foreach ($this->_createColumns() as $column){
            $this->_view->append_column($column);
        }
        
        $scrwin->add($this->_view);
        $this->_view->set_grid_lines(Gtk::TREE_VIEW_GRID_LINES_BOTH);
        
        return $scrwin;
    }
    
    /**
     * Creates GtkTreeViewColumns
     * @return \GtkTreeViewColumn
     */
    private function _createColumns()
    {
        $columns = array();
        $columnHeaders = array(
            'Código',
            'Numero de parte',
            'Descripción',
            'Precio',
            'Cantidad'
        );
        
        for ($i=0;$i<count($columnHeaders);++$i){
            $crt = new GtkCellRendererText();
            $column = new GtkTreeViewColumn($columnHeaders[$i], $crt, 'text', $i);
            $columns[] = $column;
            
            
            if ($columnHeaders[$i] == 'Descripción'){
                $column->set_expand(true);
            }
            
            if ($columnHeaders[$i] == 'Precio'){
                $crt->set_property('editable',true);
                $crt->connect('edited', array($this, 'edit'), $i);
            }
            
            if ($columnHeaders[$i] == 'Cantidad'){
                $crt->set_property('xalign', 0.5);
                $crt->set_property('editable',true);
                $crt->connect('edited', array($this, 'edit'), $i);
            }
        }
        
        return $columns;
    }
    
    public function edit($cell, $path, $new, $col)
    {
        $model = $this->_view->get_model();
        $iter = $model->get_iter_from_string($path);
        $model->set($iter, $col, $new);
        $this->recalc();
    }
    
    public function clear()
    {
        $this->_view->get_model()->clear();
    }
    
    public function getTotal()
    {
        return $this->total->get_text();
    }
}

GObject::register_type('ProductCartFrame');