<?php

require_once(dirname(__FILE__) . '/lib.php');

$filename = 'oai.xml';

$xml = file_get_contents($filename);

$xml = str_replace("\n", '', $xml);
$xml = preg_replace('/<OAI-PMH(.*)>/Uu', '<OAI-PMH>', $xml);

$dom = new DOMDocument;
$dom->loadXML($xml);
$xpath = new DOMXPath($dom);

$xpath->registerNamespace("dc", "http://purl.org/dc/elements/1.1/");	
$xpath->registerNamespace("oaidc", "http://www.openarchives.org/OAI/2.0/oai_dc/");	
$xpath->registerNamespace("xsi", "http://www.w3.org/2001/XMLSchema-instance");	


/*
<record> 
<header>
<identifier>purl.org/phylo/treebase/phylows/study/TB2:S2377</identifier> 
<datestamp>2010-03-14</datestamp>
</header>
<metadata>
<oai_dc:dc 
	xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" 
	xmlns:dc="http://purl.org/dc/elements/1.1/" 
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
	xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ 
	http://www.openarchives.org/OAI/2.0/oai_dc.xsd">       
<dc:title>Molecular phylogenetics of Maxillaria and related genera (Orchidaceae: Cymbidieae) based upon combined molecular data sets</dc:title>
<dc:creator>Blanco, Mario A.</dc:creator>
<dc:creator>Carnevali, German</dc:creator>
<dc:creator>Endara, Lorena C.</dc:creator>
<dc:creator>Koehler, Samantha</dc:creator>
<dc:creator>Neubig, Kurt M.</dc:creator>
<dc:creator>Singer, Rodrigo B.</dc:creator>
<dc:creator>Whitten, William Mark</dc:creator>
<dc:creator>Williams, Norris H.</dc:creator>

<dc:publisher>American Journal of Botany</dc:publisher> 
<dc:date>2007</dc:date>

<dc:identifier>purl.org/phylo/treebase/phylows/study/TB2:S2377</dc:identifier>
			
</oai_dc:dc>
</metadata>
</record>

*/


$records = $xpath->query ('//ListRecords/record');
foreach($records as $record)
{
	$url = '';
	
	$study_id = '';
	
	$nodeCollection = $xpath->query ('header/identifier', $record);
	foreach($nodeCollection as $node)
	{
		$url = 'http://' . $node->firstChild->nodeValue;
		
		$study_id = str_replace('http://purl.org/phylo/treebase/phylows/study/TB2:', '', $url);
	}
	
	echo $study_id;
	$filename = 'studies/' . $study_id . '.xml';
	
	if (file_exists($filename))
	{
		echo "...have!\n";		
	}
	else
	{
		echo "...fetching\n";
		
		$rss = get($url);
		if ($rss == '')
		{
			echo "RSS is empty\n";
			exit();
		}
		file_put_contents($filename, $rss);
	
	}

}

?>
