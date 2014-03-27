<?php
/**
 * Description of ProductSearchWindow
 *
 * @author orlando
 */
class ProductsTab extends GtkVbox
{
    /**
     *
     * @var ProductSearchFrame
     */
    public $searchFrame;
    
    public function __construct()
    {
        parent::__construct();
        
        $createPanel = new GtkHBox(true);
        $createPanel->pack_start(new GtkLabel('Crear Producto:'));
        $createbtn = GtkButton::new_from_stock(Gtk::STOCK_NEW);
        $createbtn->connect_simple('clicked', array($this, 'create'));
        $createPanel->pack_start($createbtn);

        $this->pack_start($createPanel, false, false, 10);
        $sep = new GtkHSeparator();
        $this->pack_start($sep, false, false);
        
        $this->searchFrame = new ProductSearchFrame();
        $this->searchFrame->set_border_width(0);
        $this->searchFrame->listview->get_column(5)->set_visible(false);
        
        $this->pack_start($this->searchFrame);
        $this->searchFrame->connect_simple('search', array($this, 'search'));
        $this->searchFrame->connect_simple('activated', array($this, 'view'));
        $this->searchFrame->connect('view-right-click', array($this, 'onViewRightClick'));
    }
    
    public function onViewRightClick($view, $event)
    {

        $menu = new GtkMenu();
        $menuitem[0] = new GtkMenuItem('Ver detalles');
        $menuitem[0]->connect_simple('activate', array($this, 'view'));
        
        $menuitem[1] = new GtkMenuItem('Editar');
        $menuitem[1]->connect_simple('activate', array($this, 'edit'));
        
        $menuitem[2] = new GtkMenuItem('Eliminar');
        $menuitem[2]->connect_simple('activate', array($this, 'delete'));

        foreach ($menuitem as $item){
            $menu->append($item);
        }

        $menu->show_all();
        $menu->popup();
        return false;
        
    }
    
    public function search()
    {
        $search = $this->searchFrame->getSearch();
        $dbm = THSModel::singleton();
        $results = $dbm->searchProduct($search, $this->searchFrame->compatibility->getActiveFilter());
        $stocks = $dbm->getProductStockList();
        $this->searchFrame->clear();
        foreach ($results as $product){
            $product->stock = $stocks[$product->id];
            $this->searchFrame->appendResult($product);
        }
    }
    
    public function edit()
    {
        $product = $this->searchFrame->listview->getSelected();
        $editWindow = new ProductWindow($product->id, true);
        $editWindow->set_icon_from_file(THS_LOGO_PATH);
        $editWindow->show_all(); 
    }
    
    public function delete()
    {
        
    }
    
    public function view()
    {
        $product = $this->searchFrame->listview->getSelected();
        $viewWindow = new ProductWindow($product->id);
        $viewWindow->set_icon_from_file(THS_LOGO_PATH);
        $viewWindow->show_all();
    }
    
    public function create()
    {
        $createWindow = new ProductWindow(null, true);
        $createWindow->set_title('Crear Producto');
        $createWindow->set_icon_from_file(THS_LOGO_PATH);
        $createWindow->show_all();
    }
}
