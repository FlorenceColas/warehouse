<html>
<title>Read Barcodes</title>
<body>
<p><img src="Images/saaspose.png" width="369" height="80" /></p>
<h2>The easiest way to Create, Convert &amp;  Automate Documents in the cloud. </h2>
<br>
 <strong>Read barcode(s) Using SDK</strong> <br />
 <br />
 <br>
<form id="form1" name="form1" method="post" action="ReadBarcodeUsingSDK.php" enctype="multipart/form-data">
  <p><strong>Please Enter App SID and App Key before using Saaspose SDK.</strong><br />
 <br />
  </p>
  <table >
            <tr>
                <td align="right" width="100">
                    App SID:</td>
                <td>
                <input name="AppSID" type="text" size="50" /></td>
            </tr>
            <tr>
                <td align="right">
                    App Key:</td>
                <td>
                <input name="AppKey" type="text" size="50" /></td>
            </tr>
    </table>
  <br />
  <br />
    <table >
        <tr>
            <td align="right" width="100">
                Symbology:</td>
            <td>
                <input name="Symbology" type="text" />Note: Enter symbology to extract specific barcodes or leave empty to extract all barcodes</td>
        </tr>
        <tr>
            <td align="right">
                Upload File:</td>
            <td>
                <input type="file" name="file" id="file" /></td>
        </tr>
		<tr>
            <td align="right">
			
                </td>
            <td>
    <input type="submit" name="Submit" value="Read Barcode(s)" /></td>
        </tr>
    </table>
</form>

</body>
 </html>