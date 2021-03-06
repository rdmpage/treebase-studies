# treebase-studies
Adding identifiers to TreeBASE studies

## OAI-PMH

Can fetch complete list of TreeBASE studies via OAI-PMH: https://treebase.org/treebase-web/top/oai?verb=ListRecords&metadataPrefix=oai_dc

For more details on this service see the [TreeBASE wiki](https://github.com/TreeBASE/treebase/wiki/OAI-PMH). Unfortunately this feed doesn't include DOIs as a separate field, so we then need to harvest each record individually :(

Fetch the OAI-PMH list of studies:

```
curl 'https://treebase.org/treebase-web/top/oai?verb=ListRecords&metadataPrefix=oai_dc' > oai.xml
```

## Harvest individual study metadata

Run fetch_studies.php to parse the OAI-PMH output to extract TreeBASE study identifiers and then grab the RSS file for each study using the URL http://purl.org/phylo/treebase/phylows/study/TB2: + <study id>. Cache the RSS in the “studies” folder.

## Mendeley

I set up a public group for TreeBASE papers on Mendeley https://www.mendeley.com/community/treebase/ but apparently this now requires you to log in to access it. For many of the papers I added DOIs and/or other links. The RIS file for this the 2604 papers in this group is available in this repository as treebase.ris.

## Add DOIs

Run parse-studies.php to read the RSS files for each study, convert to RIS, and try and add missing DOIs via CrossRef.

## Generate SQL files

To explore the data I generated an SQL dump of the metadata for the studies, with a separate column for the DOIs I added to help with debugging. Dumps of the data are in the data folder.


