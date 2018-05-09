<?php

require_once(dirname(__FILE__) . '/lib.php');

//----------------------------------------------------------------------------------------
function reference_to_ris($reference)
{
	$field_to_ris_key = array(
		'title' 	=> 'TI',
		'journal' 	=> 'JO',
		'secondary_title' 	=> 'JO',
		'book' 		=> 'T2',
		'issn' 		=> 'SN',
		'volume' 	=> 'VL',
		'issue' 	=> 'IS',
		'spage' 	=> 'SP',
		'epage' 	=> 'EP',
		'year' 		=> 'Y1',
		'data'		=> 'PY',
		'abstract'	=> 'N2',
		'url'		=> 'UR',
		'pdf'		=> 'L1',
		'doi'		=> 'DO',
		'notes'		=> 'N1',
		'oai'		=> 'ID',

		'publisher'	=> 'PB',
		'publoc'	=> 'PP',
		
		'publisher_id' => 'ID'
		
		// correspondence
		
		);
		
	$ris = '';
	
	switch ($reference->genre)
	{
		case 'article':
			$ris .= "TY  - JOUR\n";
			break;

		case 'chapter':
			$ris .= "TY  - CHAP\n";
			break;

		case 'book':
			$ris .= "TY  - BOOK\n";
			break;

		default:
			$ris .= "TY  - GEN\n";
			break;
	}

	//$ris .= "ID  - " . $result->fields['guid'] . "\n";
	
	// Need journal to be output early as some pasring routines that egnerate BibJson
	// assume journal alreday defined by the time we read pages, etc.
	if (isset($reference->journal))
	{
		$ris .= 'JO  - ' . $reference->journal . "\n";
	}

	foreach ($reference as $k => $v)
	{
		switch ($k)
		{
			// eat this
			case 'journal':
				break;
				
			case 'authors':
				foreach ($v as $a)
				{
					if ($a != '')
					{
						$a = str_replace('*', '', $a);
						$a = trim(preg_replace('/\s\s+/u', ' ', $a));						
						$ris .= "AU  - " . $a ."\n";
					}
				}
				break;
				
			case 'editors':
				foreach ($v as $a)
				{
					if ($a != '')
					{
						$ris .= "ED  - " . $a ."\n";
					}
				}
				break;				
				
			case 'date':
				//echo "|$v|\n";
				if (preg_match("/^(?<year>[0-9]{4})\-(?<month>[0-9]{2})\-(?<day>[0-9]{2})$/", $v, $matches))
				{
					//print_r($matches);
					$ris .= "PY  - " . $matches['year'] . "/" . $matches['month'] . "/" . $matches['day']  . "/" . "\n";
					$ris .= "Y1  - " . $matches['year'] . "\n";
				}
				else
				{
					$ris .= "Y1  - " . $v . "\n";
				}		
				break;
				
			default:
				if ($v != '')
				{
					if (isset($field_to_ris_key[$k]))
					{
						$ris .= $field_to_ris_key[$k] . "  - " . $v . "\n";
					}
				}
				break;
		}
	}
	
	$ris .= "ER  - \n";
	$ris .= "\n";
	
	return $ris;
}


$basedir = 'studies';

$files = scandir($basedir);

// Debugging
//$files=array('S17444.xml');
//$files=array('S936.xml');

foreach ($files as $filename)
{
	if (preg_match('/\.xml/', $filename))
	{
		$xml = file_get_contents($basedir . '/' . $filename);
		
		//echo $xml;
		
		$dom = new DOMDocument;
		$dom->loadXML($xml);
		$xpath = new DOMXPath($dom);

		$xpath->registerNamespace("rss", "http://purl.org/rss/1.0/");	
		$xpath->registerNamespace("prism", "http://prismstandard.org/namespaces/1.2/basic/");	
		$xpath->registerNamespace("rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");	
		$xpath->registerNamespace("dc", "http://purl.org/dc/elements/1.1/");	
		$xpath->registerNamespace("dcterms", "http://purl.org/dc/terms/");	
		$xpath->registerNamespace("tb", "http://purl.org/phylo/treebase/2.0/terms#");	
		
		
		$records = $xpath->query ('//rss:channel');
		foreach($records as $record)
		{
			$reference = new stdclass;
			$reference->authors = array();
			
			$nodeCollection = $xpath->query ('tb:identifier.study', $record);
			foreach($nodeCollection as $node)
			{
				$reference->publisher_id = 'S' . $node->firstChild->nodeValue;
			}
	
			$nodeCollection = $xpath->query ('dc:title', $record);
			foreach($nodeCollection as $node)
			{
				$reference->title = $node->firstChild->nodeValue;
			}
			
			$nodeCollection = $xpath->query ('prism:doi', $record);
			foreach($nodeCollection as $node)
			{
				if ( $node->firstChild->nodeValue != '')
				{
					$reference->doi = $node->firstChild->nodeValue;
				}
			}
			
			$nodeCollection = $xpath->query ('prism:publicationDate', $record);
			foreach($nodeCollection as $node)
			{
				if ( $node->firstChild->nodeValue != '')
				{
					$reference->year = $node->firstChild->nodeValue;
				}
			}
			
			$nodeCollection = $xpath->query ('prism:publicationName', $record);
			foreach($nodeCollection as $node)
			{
				if ( $node->firstChild->nodeValue != '')
				{
					$reference->journal = $node->firstChild->nodeValue;
				}
			}
						
			$nodeCollection = $xpath->query ('dc:contributor', $record);
			foreach($nodeCollection as $node)
			{
				if ( $node->firstChild->nodeValue != '')
				{
					$reference->authors[] = $node->firstChild->nodeValue;
				}
			}
			$nodeCollection = $xpath->query ('prism:publicationName', $record);
			foreach($nodeCollection as $node)
			{
				if ( $node->firstChild->nodeValue != '')
				{
					$reference->journal = $node->firstChild->nodeValue;
					$reference->genre = 'article';
				}
			}
			$nodeCollection = $xpath->query ('prism:volume', $record);
			foreach($nodeCollection as $node)
			{
				if ( $node->firstChild->nodeValue != '')
				{
					$reference->volume = $node->firstChild->nodeValue;
				}
			}
			$nodeCollection = $xpath->query ('prism:number', $record);
			foreach($nodeCollection as $node)
			{
				if ( $node->firstChild->nodeValue != '')
				{
					$reference->issue = $node->firstChild->nodeValue;
				}
			}
			$nodeCollection = $xpath->query ('prism:startingPage', $record);
			foreach($nodeCollection as $node)
			{
				if ( $node->firstChild->nodeValue != '')
				{
					$reference->spage = $node->firstChild->nodeValue;
				}
			}
			$nodeCollection = $xpath->query ('prism:endingPage', $record);
			foreach($nodeCollection as $node)
			{
				if ( $node->firstChild->nodeValue != '')
				{
					$reference->epage = $node->firstChild->nodeValue;
				}
			}
		
		}
		
		if (count($reference->authors) == 0)
		{
			unset($reference->authors);
		}
		
		// do we have a DOI?
		if (!isset($reference->doi))
		{
			$url = 'https://mesquite-tongue.glitch.me/search?q=' . urlencode($reference->title);
			$json = get($url);
			if ($json != '')
			{
				$obj = json_decode($json);
				if (isset($obj[0]))
				{
					if ($obj[0]->match)
					{
						$reference->doi = $obj[0]->id;
					}
				}
			}
		}
		
		//print_r($reference);	
		
		echo reference_to_ris($reference);	
	
	}
}


?>
