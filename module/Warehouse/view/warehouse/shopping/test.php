<?php

$image = file_get_contents('php://input');
if(!$image)
{
    print "ERROR: Failed to read the uploaded image data.\n";
    exit();
}

$name = date('YmdHis');
$newname= 'images/'.$name.'.jpg';
  $file = file_put_contents($newname, $image);
  if(!$file)
  {
      print "ERROR: Failed to write data to $filename, check permissions.\n";
      exit();
  }

  $url = 'http://'. $_SERVER['HTTP_HOST']. dirname($_SERVER['REQUEST_URI']). ''/''.$newname;
  print "$url\n";

 ?>
