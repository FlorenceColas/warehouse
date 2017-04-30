 <?php
	require_once('phpsdk/saaspose.php');
	if ($_FILES["file"]["error"] > 0)
	{
		echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
	}
	else
	{
		move_uploaded_file($_FILES["file"]["tmp_name"],	"Temp/".$_FILES["file"]["name"]);
	} 
 
	$filename = $_FILES["file"]["name"];
	
	
	//set application information
	SaasposeApp::$AppSID = $_REQUEST['AppSID'];
	SaasposeApp::$AppKey = $_REQUEST['AppKey'];
	
	//specify product URI
	Product::$BaseProductUri = "http://api.saaspose.com/v1.0";
	
	$symbology = $_REQUEST['Symbology'];
	
	//upload input file
	echo "Uploading file...<br />";
	$folder = new Folder();
	$folder->UploadFile(realpath("") . "/Temp/" . $filename, "");
	echo "File uploaded <br />";
	echo "Reading barcode(s) <br />";
    //create BarcodeReader object
    $reader = new BarcodeReader($filename);
            
    //read barcode image and get a list of barcodes
    $barcodes = $reader->Read($symbology);
			
	foreach ($barcodes as $barcode) {
				 
		echo "Type: " . $barcode->BarcodeType . "&emsp;Text: " . $barcode->BarcodeValue . "<br />";
	}
?>