<?php

require_once '../../lib/export.class.php';

/**
  * 
  * PHP version 5
  * @Package ECO4R
  * @author  Jochen Schirrwagen <jochen.schirrwagen@uni-bielefeld.de>
  * @copyright Bielefeld University Library, 2011
  * @license http://www.dipp.nrw.de/lizenzen/dfsl/
  * @version 1.0
 */
class OaiOreExport extends Export{

  var $_pubTypeMapping = array();

  /**
    *
   */
  function OaiOreExport(){
        $this->_version = '1.0';
        $this->_fileExtension = 'rdf';
        $this->_charset = 'UTF-8';
        $this->_mimeType = 'application/rdf+xml';
        $this->_pubTypeMapping = parse_ini_file("pubtype_mapping.conf");
        $this->_entity = '';
  }

  /**
    *
   */
  function setEntity($entity){
    if ($entity != '')
      $this->_entity = '/' . $entity;
  }

  /**
    *
   */
  function export(&$metadata, $prependXmlDecl=true){
    // define dc metadata used in the REM
    $dc_title = $metadata->getTitle();
    $dc_ddc = $metadata->getGroupDdc();
    // we could be more granular here if we want
    $authors = array();
    foreach ($metadata->getAuthors() as $author){
      array_push($authors, $author->getSurname() . ", " . $author->getForename());
    }

    $opusRecordResourcePath = "/ore/" . $metadata->getSourceOpus();
    $range = &$metadata->getRange(); # assignment reference
    $fileUrlStack = array();
    $fulltextItems = array();
    $supplementItems = array();
    
    
    foreach ($metadata->getFiles() as $file) {
        # constructing the file-url
        $fileParentPath = basename(dirname($file->_path));
        error_log("file-path:" . $file->_path);
        # temporary, not correct, since it doesn't check mimetype
        array_push($fulltextItems, $range->getFulltextUrl() . '/' . $metadata->getDateCreated('%Y') . '/' . $metadata->getSourceOpus() . '/' . $fileParentPath . '/' . $file->getName()); 
    }
    // add scholarlyWork
    if ($this->_entity == '')
      array_push($fileUrlStack, array(
        'resourceDescrURI' => $range->getFulltextUrl() . '/' . $metadata->getDateCreated('%Y') . '/' . $metadata->getSourceOpus() . '/',
        'types' => array('http://purl.org/spar/fabio/WebPage'),
        'aggregatedBy' =>  OPUS_HOME . $opusRecordResourcePath . '#aggregation'
      )); 

    if ($this->_entity == '' || $this->_entity == '/text'){
      array_push($fileUrlStack, array(
        'resourceDescrURI' => OPUS_HOME . $opusRecordResourcePath . '/text#aggregation',
        'types' => array( 'http://purl.org/eprint/type/ScholarlyText', 'http://www.openarchives.org/ore/terms/Aggregation' ),
        'aggregatedBy' => OPUS_HOME . $opusRecordResourcePath . '#aggregation',
        'describedBy' => OPUS_HOME . $opusRecordResourcePath . '/text',
        'aggregatedResources' => $fulltextItems));
      $pubType = $metadata->getPublicationType(); #get the pubType from the metadata 
      $dc_description = '';
      if ($metadata->getDescription())
         $dc_description = $metadata->getDescription();
      else if ( $metadata->getAltDescription() )
         $dc_description = $metadata->getAltDescription();
      $dc_metadata = array('http://purl.org/dc/terms/title' => array( 'type' => 'literal', 'value' => $dc_title),
                           'http://purl.org/dc/terms/dateAccepted' => array( 'type' => 'literal', 'value' => $metadata->getDateYear() . '-01-01' ),
                           'http://purl.org/dc/terms/description' => array( 'type' => 'literal', 'value' => $dc_description),
                           'http://purl.org/dc/terms/rights' => array( 'type' => 'resource', 'value' => $metadata->getLicense()),
                           'http://purl.org/dc/elements/1.1/subject' => array( 'type' => 'resource', 'value' => 'http://dewey.info/class/' . $dc_ddc . '/'),
                           'http://purl.org/dc/elements/1.1/creator' => array( 'type' => 'bnode', 'value' => '_:creator', 'nodevalues' => $authors, 
                                                                               'noderdftype' => 'http://xmlns.com/foaf/0.1/Person', 'noderdfterm' => 'http://xmlns.com/foaf/0.1/name'),
                           'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' => array( 'type' => 'resource', 'value' => $this->_pubTypeMapping[$pubType]));
    }
    // add metadata
    if ($this->_entity == '' || $this->_entity == '/metadata'){
      array_push($fileUrlStack, array(
        'resourceDescrURI' => OPUS_HOME . $opusRecordResourcePath . '/metadata#aggregation',
        'types' => array( 'http://purl.org/spar/fabio/Metadata', 'http://www.openarchives.org/ore/terms/Aggregation' ),
        'aggregatedBy' => OPUS_HOME . $opusRecordResourcePath . '#aggregation',
        'describedBy' => OPUS_HOME . $opusRecordResourcePath . '/metadata',
        'aggregatedResources' => array(OAI_URL . "?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai:" . REPOSITORY_IDENTIFIER . ":" . $metadata->getSourceOpus())
      ));
    }
    if ( $this->_entity == '/metadata' )
      $dc_metadata = array();

  

    // this format "%Y-%m-%d %H:%M:%S" is not valid in rdf
    if (strcmp($metadata->getDateModified(), '01/01/70') == 0) {
       $datestamp = $metadata->getDateCreated("%Y-%m-%d");
    } else {
       $datestamp = $metadata->getDateModified("%Y-%m-%d");
    }

    // construct ORE-REM
    $rem = new ConstructORE();
    $rem->setAgent(OPUS_HOME . $opusRecordResourcePath . $this->_entity,
                   OPUS_HOME . $opusRecordResourcePath . $this->_entity . '#aggregation',
                   array('agent_name' => REPOSITORY_NAME, 'agent_url' => OPUS_HOME),
                   $datestamp
    );
    $rem->addOreAggregates(OPUS_HOME . $opusRecordResourcePath . $this->_entity . '#aggregation',
                           OPUS_HOME . $opusRecordResourcePath . $this->_entity,
                           $fileUrlStack,
                           $dc_metadata,
                           $metadata->getUrn()
    );

    # TODO missing ReM if aggregated resources are also aggregation
    $rem->addAggregatedResource($fileUrlStack);

    //arc2 adds the xml declaration, which needs to be removed in the oai-record metadata section, since it leads to an xml processing array
    if ($prependXmlDecl)
      return preg_replace('/<\?xml version=\"1.0\" encoding=\"UTF-8\"\?>/i', '', $rem->serialize(), 1);
    else
      return $rem->serialize();
  }
}
?>
