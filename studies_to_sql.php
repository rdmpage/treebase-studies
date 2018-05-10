<?php

// Export studies to SQL


$basedir = 'studies';

$files = scandir($basedir);

// Debugging
//$files=array('S18025.xml');
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

			$nodeCollection = $xpath->query ('dcterms:bibliographicCitation', $record);
			foreach($nodeCollection as $node)
			{
				$reference->bibliographicCitation = $node->firstChild->nodeValue;
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
				
		//print_r($reference);
		
		$keys = array();
		$values = array();
		
		foreach ($reference as $k => $v)
		{
			switch ($k)
			{
				case 'title':
				case 'journal':
				case 'volume':
				case 'issue':
				case 'spage':
				case 'epage':
				case 'year':
				case 'bibliographicCitation':
				case 'doi':
					$keys[] = $k;
					$values[] = '"' . addcslashes($v, '"') . '"';
					break;
					
				case 'publisher_id':
					$keys[] = 'id';
					$values[] = '"' . addcslashes($v, '"') . '"';
					break;

				case 'authors':
					$keys[] = 'authors';
					$values[] = '"' . addcslashes(join(';', $v), '"') . '"';
					break;
					
				default:
					break;
			}
		}
		
		//print_r($keys);
		//print_r($values);
		
		echo 'REPLACE INTO studies(' . join(',', $keys) . ') VALUES(' . join (',', $values) . ');' . "\n";
	
	}
}


?>
