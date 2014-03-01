<?php

require_once 'fpdf17/fpdf.php';

/**
 * Description of DocumentFactory
 *
 * @author orlando
 */
class DocumentFactory
{
    public static function generateQuote($products, $path)
    {
        $doc = new FPDF('P', 'mm', 'Letter');
        $doc->AddPage();
        $doc->SetFont('Arial', '', 10);
        $doc->Image(THS_LOGO_FILENAME, 5, 5, 30, 30);
        $doc->Image(THS_LOGO_FILENAME, 180, 5, 30, 30);
        $doc->Cell(0, 10,'THE HONDA STORE', 0,1,'C',false);
        $doc->cell(0, 10, iconv('UTF-8', 'windows-1252', 'Cotización'), 0,  1, 'C', false);
        $doc->Ln();
        $doc->SetFont('Arial', '', 12);
        
        foreach ($products as $product){
            $doc->cell(15, 8, $product->id, 1, 0, 'L');
            $doc->cell(40, 8, iconv('UTF-8', 'windows-1252', $product->partnumber), 1, 0, 'L');
            $doc->cell(80, 8, iconv('UTF-8', 'windows-1252', $product->description), 1, 0, 'L');
            $doc->cell(20, 8, $product->price, 1, 0, 'L');
            $doc->cell(20, 8, $product->qty, 1, 0, 'C');
            $doc->Cell(20,8, $product->price*$product->qty, 1, 1, 'R');
        }
        
        $doc->Output($path, 'F');
    }
    
    public static function generateSale($products, $branch, $total=null)
    {
        
    }
    
    public static function generateStockList($branch, $path)
    {
        $fpdf = new fpdf('P', 'mm', 'Letter');
        $fpdf->setFont('Arial', '', 12);
        $fpdf->AddPage();
        $branchdesc = 'Sucursal: ('.$branch->id.')'.$branch->name;
        $fpdf->Cell(0, 10, 'Listado de stock disponible', 0, 1, 'C');
        $fpdf->Cell(20, 10, $branchdesc, 0, 1, 'L');
        $fpdf->Cell(100, 10, $branch->address, 0, 1, 'L');
        $fpdf->Line(10, 42, 205, 42);
        $fpdf->Ln();
        $fpdf->Cell(20, 8, 'Codigo', 1, 0, 'C');
        $fpdf->Cell(40, 8, 'Numero de Parte', 1, 0, 'C');
        $fpdf->Cell(80, 8, iconv('UTF8', 'cp1252','Descripción'), 1, 0, 'C');
        $fpdf->Cell(20, 8, 'Estado', 1, 0, 'C');
        $fpdf->Cell(20, 8, 'Precio', 1, 0, 'C');
        $fpdf->Cell(15, 8, 'Stock', 1, 1, 'C');
        
        foreach ($plist as $pid){
            $product = Product::getFromId($pid);
            $fpdf->Cell(20, 8, $product->id, 1, 0, 'C');
            $fpdf->Cell(50, 8, $product->partnumber, 1, 0, 'C');
            $fpdf->Cell(80, 8, $product->description, 1, 0, 'L');
            $state = ($product->state==Product::STATE_NEW)? 'Nuevo':'Usado';
            $fpdf->Cell(20, 8, $state, 1, 0, 'C');
            $fpdf->Cell(20, 8, $product->price, 1, 0, 'R');
            $fpdf->Cell(15, 8, $product->stock[$branch->id], 1, 1, 'C');
        }
        
        $fpdf->Output('/tmp/export.pdf', 'F');
    }
}

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