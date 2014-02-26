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
        $doc->SetFont('Arial', 'B', 16);
        $doc->cell(0, 15, iconv('UTF-8', 'windows-1252', 'Cotización'), 1,  1, 'C', false);
        $doc->Ln();
        $doc->SetFont('Arial', '', 12);
        
        foreach ($products as $product){
            $doc->cell(15, 8, iconv('UTF-8', 'windows-1252', $product->id), 1, 0, 'L');
            $doc->cell(40, 8, iconv('UTF-8', 'windows-1252', $product->partnumber), 1, 0, 'L');
            $doc->cell(40, 8, iconv('UTF-8', 'windows-1252', $product->description), 1, 0, 'L');
            $doc->cell(40, 10, iconv('UTF-8', 'windows-1252', $product->price), 1, 0, 'L');

        }
        
        $doc->Output('../test.pdf', 'F');
    }
    
    public static function generateSale($products, $branch, $total=null)
    {
        
    }
    
    public static function generateStockList($branch, $path)
    {
        
    }
}
