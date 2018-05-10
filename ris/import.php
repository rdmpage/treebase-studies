<?php

// Parse TreeBASE RIS export
require_once(dirname(__FILE__) . '/ris.php');


function treebase($reference)
{
	//print_r($reference);
	
	if (isset($reference->doi))
	{
		echo 'UPDATE studies SET rdmp_doi="' . $reference->doi . '" WHERE id="' . $reference->publisher_id . '" AND doi IS NULL;' . "\n";
	}


	
}


$filename = '';
if ($argc < 2)
{
	echo "Usage: import.php <RIS file> <mode>\n";
	exit(1);
}
else
{
	$filename = $argv[1];
}


$file = @fopen($filename, "r") or die("couldn't open $filename");
fclose($file);

import_ris_file($filename, 'treebase');



?>