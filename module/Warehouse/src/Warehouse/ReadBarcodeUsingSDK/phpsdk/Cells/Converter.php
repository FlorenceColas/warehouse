<?php
/*
* converts pages or document into different formats
*/
class CellsConverter
{
	public $FileName = "";
	public $WorksheetName = "";
		
	public function CellsConverter($fileName, $worksheetName)
	{
		//set default values
		$this->FileName = $fileName;

		$this->WorksheetName =  $worksheetName;
	}

	/*
    * converts a document to outputFormat
	* @param string $outputFormat
	*/
	public function Save($outputFormat){
		try{
			//check whether file is set or not
			if ($this->FileName == "")
				throw new Exception("No file name specified");
			
			//Build URI
			$strURI = Product::$BaseProductUri . "/cells/" . $this->FileName . "?format=" . $outputFormat;
			
			//Sign URI
			$signedURI = Utils::Sign($strURI);

			//Send request and receive response stream
			$responseStream = Utils::processCommand($signedURI, "GET", "", "");

			//Validate output
			$v_output = Utils::ValidateOutput($responseStream);
 
			if ($v_output === "") {
				//Save ouput file
				$outputPath = SaasposeApp::$OutPutLocation . Utils::getFileName($this->FileName). "." . $outputFormat;
				Utils::saveFile($responseStream, $outputPath);
				return $outputPath;
			} 
			else 
				return $v_output;
		} catch (Exception $e){
			throw new Exception($e->getMessage());
		}  
	}

	/*
    * converts a sheet to image
	* @param string $imageFormat
	*/
	public function WorksheetToImage($imageFormat){
		try{
			//check whether file and sheet is set or not
			if ($this->FileName == "")
				throw new Exception("No file name specified");
			if ($this->WorksheetName == "")
				throw new Exception("No worksheet specified");
			
			//Build URI
			$strURI = Product::$BaseProductUri . "/cells/" . $this->FileName . "/worksheets/" . 
			          $this->WorksheetName . "?format=" . $imageFormat;
			
			//Sign URI
			$signedURI = Utils::Sign($strURI);

			//Send request and receive response stream
			$responseStream = Utils::processCommand($signedURI, "GET", "", "");

			//Validate output
			$v_output = Utils::ValidateOutput($responseStream);
 
			if ($v_output === "") {
				//Save ouput file
				$outputPath = SaasposeApp::$OutPutLocation . Utils::getFileName($this->FileName). 
				"_" . $this->WorksheetName . "." . $imageFormat;
				Utils::saveFile($responseStream, $outputPath);
				return $outputPath;
			} 
			else 
				return $v_output;
		} catch (Exception $e){
			throw new Exception($e->getMessage());
		}  
	}

	/*
    * saves a specific picture from a specific sheet as image
	* @param $pictureIndex
	* @param $imageFormat
	*/
	public function PictureToImage($pictureIndex, $imageFormat){
		try{
			//check whether file and sheet is set or not
			if ($this->FileName == "")
				throw new Exception("No file name specified");
			if ($this->WorksheetName == "")
				throw new Exception("No worksheet specified");
			
			//Build URI
			$strURI = Product::$BaseProductUri . "/cells/" . $this->FileName . "/worksheets/" . 
			          $this->WorksheetName . "/pictures/" . $pictureIndex . "?format=" . $imageFormat;
			
			//Sign URI
			$signedURI = Utils::Sign($strURI);

			//Send request and receive response stream
			$responseStream = Utils::processCommand($signedURI, "GET", "", "");

			//Validate output
			$v_output = Utils::ValidateOutput($responseStream);
 
			if ($v_output === "") {
				//Save ouput file
				$outputPath = SaasposeApp::$OutPutLocation . Utils::getFileName($this->FileName). 
				"_" . $this->WorksheetName . "." . $imageFormat;
				Utils::saveFile($responseStream, $outputPath);
				return $outputPath;
			} 
			else 
				return $v_output;
		} catch (Exception $e){
			throw new Exception($e->getMessage());
		}  
	}

	/*
    * saves a specific OleObject from a specific sheet as image
	* @param $objectIndex
	* @param $imageFormat
	*/
	public function OleObjectToImage($objectIndex, $imageFormat){
		try{
			//check whether file and sheet is set or not
			if ($this->FileName == "")
				throw new Exception("No file name specified");
			if ($this->WorksheetName == "")
				throw new Exception("No worksheet specified");
			
			//Build URI
			$strURI = Product::$BaseProductUri . "/cells/" . $this->FileName . "/worksheets/" . 
			          $this->WorksheetName . "/oleobjects/" . $objectIndex . "?format=" . $imageFormat;
			
			//Sign URI
			$signedURI = Utils::Sign($strURI);

			//Send request and receive response stream
			$responseStream = Utils::processCommand($signedURI, "GET", "", "");

			//Validate output
			$v_output = Utils::ValidateOutput($responseStream);
 
			if ($v_output === "") {
				//Save ouput file
				$outputPath = SaasposeApp::$OutPutLocation . Utils::getFileName($this->FileName). 
				"_" . $this->WorksheetName . "." . $imageFormat;
				Utils::saveFile($responseStream, $outputPath);
				return $outputPath;
			} 
			else 
				return $v_output;
		} catch (Exception $e){
			throw new Exception($e->getMessage());
		}  
	}	
	
	/*
    * saves a specific chart from a specific sheet as image
	* @param $chartIndex
	* @param $imageFormat
	*/
	public function ChartToImage($chartIndex, $imageFormat){
		try{
			//check whether file and sheet is set or not
			if ($this->FileName == "")
				throw new Exception("No file name specified");
			if ($this->WorksheetName == "")
				throw new Exception("No worksheet specified");
			
			//Build URI
			$strURI = Product::$BaseProductUri . "/cells/" . $this->FileName . "/worksheets/" . 
			          $this->WorksheetName . "/charts/" . $chartIndex . "?format=" . $imageFormat;
			
			//Sign URI
			$signedURI = Utils::Sign($strURI);

			//Send request and receive response stream
			$responseStream = Utils::processCommand($signedURI, "GET", "", "");

			//Validate output
			$v_output = Utils::ValidateOutput($responseStream);
 
			if ($v_output === "") {
				//Save ouput file
				$outputPath = SaasposeApp::$OutPutLocation . Utils::getFileName($this->FileName). 
				"_" . $this->WorksheetName . "." . $imageFormat;
				Utils::saveFile($responseStream, $outputPath);
				return $outputPath;
			} 
			else 
				return $v_output;
		} catch (Exception $e){
			throw new Exception($e->getMessage());
		}  
	}
	
	/*
    * saves a specific auto-shape from a specific sheet as image
	* @param $shapeIndex
	* @param $imageFormat
	*/
	public function AutoShapeToImage($shapeIndex, $imageFormat){
		try{
			//check whether file and sheet is set or not
			if ($this->FileName == "")
				throw new Exception("No file name specified");
			if ($this->WorksheetName == "")
				throw new Exception("No worksheet specified");
			
			//Build URI
			$strURI = Product::$BaseProductUri . "/cells/" . $this->FileName . "/worksheets/" . 
			          $this->WorksheetName . "/autoshapes/" . $shapeIndex . "?format=" . $imageFormat;
			
			//Sign URI
			$signedURI = Utils::Sign($strURI);

			//Send request and receive response stream
			$responseStream = Utils::processCommand($signedURI, "GET", "", "");

			//Validate output
			$v_output = Utils::ValidateOutput($responseStream);
 
			if ($v_output === "") {
				//Save ouput file
				$outputPath = SaasposeApp::$OutPutLocation . Utils::getFileName($this->FileName). 
				"_" . $this->WorksheetName . "." . $imageFormat;
				Utils::saveFile($responseStream, $outputPath);
				return $outputPath;
			} 
			else 
				return $v_output;
		} catch (Exception $e){
			throw new Exception($e->getMessage());
		}  
	}
}
?>