<?php
/**
 * User: FlorenceColas
 * Date: 08/01/2017
 * Version: 1.00
 * PDF: extends the FDPF class which manage pdf generation. It contains the following functions:
 *      - Header: override FPDF function
 *      - Footer: override FPDF function
 *      - GetWrapText: return an array of the text in parameter wrap depending of the element type
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\FPDF;

use Warehouse\Enum\EnumRecipePDFLineSize;

class PDF extends FPDF
{
    // Page header
    function Header()
    {
        // Logo
/*        $this->Image('logo.png',10,6,30);
        // Arial bold 15
        $this->SetFont('Arial','B',15);
        // Move to the right
        $this->Cell(80);
        // Title
        $this->Cell(30,10,'Title',1,0,'C');
        // Line break
        $this->Ln(20);
*/
    }

// Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        $this->AddFont('Times New Roman','I','Times New Roman Italic.php');
        $this->SetFont('Times New Roman','I',8);
        // Page number
        $this->Cell(0,10,utf8_decode('Copyright ©').date('Y').' The Nostradomus Development Team recipes',0,0,'C');
    }

    /**
     * Return an array with the text wrap depending of the string size
     * @param $type
     * @param $text
     * @return array
     */
    public function GetWrapText($type,$text){
        switch ($type){
            case 'TITLE':
                $maxSize = EnumRecipePDFLineSize::TITLE_WIDTH;
                break;
            case 'INGREDIENT':
                $maxSize = EnumRecipePDFLineSize::INGREDIENT_WIDTH;
                break;
            case 'PREPARATION':
                $maxSize = EnumRecipePDFLineSize::PREPARATION_WIDTH;
                break;
            case 'NOTE':
                $maxSize = EnumRecipePDFLineSize::NOTE_WIDTH;
                break;
        }

        $textArray = array();
        $size = $this->GetStringWidth($text);
        if ($size <= $maxSize) {
            array_push($textArray, $text);
        } else {
            $textTab = explode(' ',$text);
            $newText = '';
            for ($i=0;$i<count($textTab);$i++) {
                if ($newText != '')
                    $tempText = $newText . ' '.$textTab[$i];
                else $tempText = $textTab[$i];
                $tempSize = $this->GetStringWidth($tempText);
                if ($tempSize >= $maxSize) {
                    array_push($textArray, $newText);
                    $newText = $textTab[$i];
                } else {
                    if ($newText != '') $newText = $newText.' '.$textTab[$i];
                    else $newText = $textTab[$i];
                }
            }
            array_push($textArray, $newText);
        }
        return $textArray;
    }
}