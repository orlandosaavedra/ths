<?php

require_once 'fpdf17/fpdf.php';
require_once 'THSModel.class.php';
require_once 'Branch.class.php';
/*
THSModel::$dbname = 'The_Honda_Store';
THSModel::$host = 'thehondastore.no-ip.biz';
THSModel::$username = 'thehondastore';
THSModel::$password = 'UK5fJ2LX9dwbybuj';
*/
/**
 * Description of DocumentFactory
 *
 * @author orlando
 */
class DocumentFactory
{
    public static function generateQuote($cart, $path)
    {
        $products = $cart->getProducts();
        $doc = new FPDF('P', 'mm', 'Letter');
        $doc->AddPage();
        $doc->SetFont('Arial', '', 13);
        $doc->Image(THS_LOGO_FILENAME, 12, 12, 30, 30);
        //$doc->Image(THS_LOGO_FILENAME, 180, 5, 30, 30);
        
        $doc->Cell(0, 10,'THE HONDA STORE', 0,1,'C',false);
        $doc->SetFont('Arial', '', 12);
        $doc->Cell(0, 5, 'http://www.desarmahonda.cl',0,1,'C');
        $doc->Ln();
        $dbm = THSModel::singleton();
        $branches = $dbm->getBranches();
        
        $doc->Cell(40,5);
        $doc->Cell(2,5, 'Sucursales:',0,0);
        $doc->Cell(28,5);
        
        foreach ($branches as $branch){
            $text = $branch->address . ' Tel:'.$branch->phone;
            if ($branch->id != $branches[0]->id){
                $doc->Cell(70,5, '',0,0);
            }
            $doc->Cell(30,5,$text, 0,1,'L');
        }      
        
        
        $doc->SetFont('Arial', '', 13);
        $doc->Ln();
        $doc->cell(0, 10, iconv('UTF-8', 'windows-1252', 'COTIZACIÓN'), 0,  1, 'C', false);
        $doc->SetFont('Arial', '', 10);        
        
        $doc->cell(15, 8, 'Codigo', 1, 0, 'L');
        $doc->cell(40, 8, iconv('UTF-8', 'windows-1252', 'N° Parte'), 1, 0, 'C');
        $doc->cell(90, 8, iconv('UTF-8', 'windows-1252', 'Descripción'), 1, 0, 'C');
        $doc->cell(20, 8, 'Precio', 1, 0, 'C');
        $doc->cell(10, 8, 'Cant.', 1, 0, 'C');
        $doc->Cell(20,8, 'Total', 1, 1, 'C');
        
        foreach ($products as $product){
            $doc->cell(15, 5, $product->id, 1, 0, 'L');
            $doc->cell(40, 5, iconv('UTF-8', 'windows-1252', $product->partnumber), 1, 0, 'L');
            $doc->cell(90, 5, iconv('UTF-8', 'windows-1252', $product->description), 1, 0, 'L');
            $price = '$'.number_format($product->price, 0, '.', ',');
            $doc->cell(20, 5, $price, 1, 0, 'R');
            $doc->cell(10, 5, $product->qty, 1, 0, 'C');
            $total = $product->price*$product->qty; 
            $total = '$'.number_format($total);
            $doc->Cell(20,5, $total, 1, 1, 'R');
        }
        
        $totals = $cart->getTotals();
        
        foreach ($totals as &$number){
            $number = number_format((double)$number, 0, '.',',');
        }
        
        $subtotal = $totals[SalesCartFrame::TOTALS_SUBTOTAL];
        $pdiscount = $totals[SalesCartFrame::TOTALS_PDISCOUNT];
        $discount = $totals[SalesCartFrame::TOTALS_DISCOUNT];
        $net = $totals[SalesCartFrame::TOTALS_NET];
        $tax = $totals[SalesCartFrame::TOTALS_TAX];
        $total = $totals[SalesCartFrame::TOTALS_TOTAL];
        
        $doc->Cell(15+40+90+20+10+20, 5, '', 1,1);
        $doc->Cell(15+40+90+20+10, 5, 'Subtotal:', 1, 0, 'R');
        $doc->Cell(20,5, '$'.$subtotal, 1, 1, 'R');
        $doc->Cell(15+40+90+20+10, 5, 'Descuento:', 1, 0, 'R');
        $doc->Cell(20, 5, '('.$pdiscount.'%) $'.$discount, 1, 1, 'R');
        $doc->Cell(15+40+90+20+10, 5, 'Neto:', 1, 0, 'R');
        $doc->Cell(20, 5, $net, 1, 1, 'R');
        $doc->Cell(15+40+90+20+10, 5, 'IVA:', 1, 0, 'R');
        $doc->Cell(20, 5, $tax, 1, 1, 'R');
        $doc->Cell(15+40+90+20+10, 5, 'Total:', 1, 0, 'R');
        $doc->Cell(20, 5, $total, 1, 1, 'R');
        $doc->Ln();
        $doc->Cell(0,5, '*Precios no incluyen IVA', 0,0,'L');
        
        $doc->Output($path, 'F');
    }
    
    public static function generateSale($products, $branch, $total=null)
    {
        
    }
    
    public static function generateAvailableStockList($plist, $branch, $path)
    {
        $fpdf = new fpdf('P', 'mm', 'Letter');
        $fpdf->setFont('Arial', '', 10);
        $fpdf->AddPage();
        $branchdesc = 'Sucursal: ('.$branch->id.')'.$branch->name;
        $fpdf->Cell(0, 10, 'Listado de stock disponible', 0, 1, 'C');
        $fpdf->Cell(20, 10, $branchdesc, 0, 1, 'L');
        $fpdf->Cell(100, 10, $branch->address, 0, 1, 'L');
        $fpdf->Line(10, 42, 205, 42);
        $fpdf->Ln();
        $fpdf->Cell(20, 8, 'Codigo', 1, 0, 'C');
        $fpdf->Cell(30, 8, 'Numero de Parte', 1, 0, 'C');
        $fpdf->Cell(80, 8, iconv('UTF8', 'cp1252','Descripción'), 1, 0, 'C');
        $fpdf->Cell(20, 8, 'Estado', 1, 0, 'C');
        $fpdf->Cell(20, 8, 'Precio', 1, 0, 'C');
        $fpdf->Cell(15, 8, 'Stock', 1, 0, 'C');
        $fpdf->Cell(15, 8, 'Real', 1, 1);
        
        foreach ($plist as $pid){
            $product = Product::getFromId($pid);
            $fpdf->Cell(20, 8, $product->id, 1, 0, 'C');
            $fpdf->Cell(30, 8, $product->partnumber, 1, 0, 'C');
            $fpdf->Cell(80, 8, iconv('UTF8', 'cp1252',$product->description), 1, 0, 'L');
            $state = ($product->state==Product::STATE_NEW)? 'Nuevo':'Usado';
            $fpdf->Cell(20, 8, $state, 1, 0, 'C');
            $fpdf->Cell(20, 8, $product->price, 1, 0, 'R');
            $fpdf->Cell(15, 8, $product->stock[$branch->id], 1, 0, 'C');
            $fpdf->Cell(15, 8, '', 1, 1, 'C');
        }
        
        $fpdf->Output($path, 'F');
    }
}
/*
$arr = array();

for ($i=0;$i<10;++$i){
    $arr[$i] = new stdClass();
    $arr[$i]->id = rand(1000, 99999);
    $arr[$i]->partnumber = substr(sha1($i), 0, 20);
    $arr[$i]->description = 'ESTA ES UNA DESCRIPCION DEL PRODUCTO '.$i;
    $arr[$i]->state = 'Nuevo';
    $arr[$i]->price = rand(1000, 1000000);
    $arr[$i]->stock = array(rand(0,50), rand(0,50));
    $arr[$i]->qty = rand(1,8);
}

define('THS_LOGO_FILENAME', '../img/logo.png');
DocumentFactory::generateQuote($arr, '../test.pdf');
 * 
 */