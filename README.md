# treebase-studies
Adding identifiers to TreeBASE studies

## OAI-PMH

Can fetch complete list of TreeBASE studies via OAI-PMH: https://treebase.org/treebase-web/top/oai?verb=ListRecords&metadataPrefix=oai_dc

Unfortunately this feed doesn't include DOIs as a separate field, so we then need to harvest each record individually :(

Fetch the OAI-PMH list of studies:

```
curl 'https://treebase.org/treebase-web/top/oai?verb=ListRecords&metadataPrefix=oai_dc' > oai.xml
```

