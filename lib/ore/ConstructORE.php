<?php
/**
 * Construct and serialize OAI-ORE ResourceMaps
 * PHP version 5
 * @Package ECO4R
 * @author Jochen Schirrwagen <jochen.schirrwagen@uni-bielefeld.de>
 * @copyright Bielefeld University Library, 2011
 * @license http://www.dipp.nrw.de/lizenzen/dfsl/ 
 * @version 1.0
 */

# (c) 2011 Jochen Schirrwagen / Bielefeld University Library
# Version 0.1

class ConstructORE 
{



  /**
    * Constructor
    * inits namespace array (dcterms, dc, foaf, fabio, ore); array of triples; ARC2 RDF-XML Serializer
    */
  function __construct( $namespaces = array() ) {
  
    $ns_conf = array('ns' => array('dcterms' => 'http://purl.org/dc/terms/',
                                   'dc'      => 'http://purl.org/dc/elements/1.1/',
                                   'foaf'    => 'http://xmlns.com/foaf/0.1/',
                                   'fabio'   => 'http://purl.org/spar/fabio/',
                                   'ore'     => 'http://www.openarchives.org/ore/terms/'));
               
    $this->triples = array();
    $this->rdfXmlSerializer = ARC2::getRDFXMLSerializer($ns_conf);

  }


  function addTriple($spo) {
      array_push($this->triples, $spo);
  }

  /**
   *  create Resource Map
   *
   * @param $uri the URI of the Resource Map
   * @param $aggr_resource the URI of the Aggregation described by this Resource Map
   * @param $agent the Agent who created the Resource Map
   * @param $modified
   */
  function setAgent($uri, $aggr_resource, $agent, $modified_date) {

    $spo = array();
    // subject
    $spo['s'] = $uri;
    $spo['s_type'] = 'uri';
    // predicate
    $spo['p'] = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';
    // object
    $spo['o'] = 'http://www.openarchives.org/ore/terms/ResourceMap';  
    $spo['o_type'] = 'resource';
    $this->addTriple( $spo );

    $spo = array();
    // subject
    $spo['s'] = $uri;
    $spo['s_type'] = 'uri';
    // predicate
    $spo['p'] = 'http://www.openarchives.org/ore/terms/describes';
    // object
    $spo['o'] = $aggr_resource;  
    $spo['o_type'] = 'resource';
    $this->addTriple( $spo );

    $foaf_spo = array();
    $foaf_spo['s'] = '_:agent';
    $foaf_spo['s_type'] = 'bnode';
    $foaf_spo['p'] = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';
    $foaf_spo['o'] = 'http://xmlns.com/foaf/0.1/Organization';
    $foaf_spo['o_type'] = 'resource';
    $this->addTriple( $foaf_spo );

    $foaf_spo = array();
    $foaf_spo['s'] = '_:agent';
    $foaf_spo['s_type'] = 'bnode';
    $foaf_spo['p'] = 'http://xmlns.com/foaf/0.1/name';
    $foaf_spo['o'] = $agent['agent_name'];
    $foaf_spo['o_type'] = 'literal';
    $this->addTriple( $foaf_spo );

    $foaf_spo = array();
    $foaf_spo['s'] = '_:agent';
    $foaf_spo['s_type'] = 'bnode';
    $foaf_spo['p'] = 'http://xmlns.com/foaf/0.1/page';
    $foaf_spo['o'] = $agent['agent_url'];
    $foaf_spo['o_type'] = 'resource';
    $this->addTriple( $foaf_spo );


    $spo = array();
    // subject
    $spo['s'] = $uri;
    $spo['s_type'] = 'uri';
    // predicate
    $spo['p'] = 'http://purl.org/dc/terms/creator';
    // object
    $spo['o'] = '_:agent';  
    $spo['o_type'] = 'bnode';
#    $spo['o'] = $creator_resource;  
#    $spo['o_type'] = 'resource';
    $this->addTriple( $spo );




    $spo = array();
    // subject
    $spo['s'] = $uri;
    $spo['s_type'] = 'uri';
    // predicate
    $spo['p'] = 'http://purl.org/dc/terms/modified';
    // object
    $spo['o'] = $modified_date;  
    $spo['o_type'] = 'literal';
    $spo['o_datatype'] = 'http://www.w3.org/2001/XMLSchema#date';
    $this->addTriple( $spo );
    
  }

  /**
   *
   */
  function addAggregatedResource($aggr_resources){
    forEach( $aggr_resources as $ar ){
      forEach( $ar['types'] as $type ){
        $spo = array();
        // subject
        $spo['s'] = $ar['resourceDescrURI'];
        $spo['s_type'] = 'uri';
        // predicate
        $spo['p'] = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';
        // object
        $spo['o'] = $type;
        $spo['o_type'] = 'resource';
        $this->addTriple( $spo );
      }
      // aggregatedBy
      $spo = array();
      // subject
      $spo['s'] = $ar['resourceDescrURI'];
      $spo['s_type'] = 'uri';
      // predicate
      $spo['p'] = 'http://www.openarchives.org/ore/terms/isAggregatedBy';
      // object
      $spo['o'] = $ar['aggregatedBy'];  
      $spo['o_type'] = 'resource';
      $this->addTriple( $spo );
      // described by if exist
      if ( isset($ar['describedBy']) ){
        $spo = array();
        $spo['s'] = $ar['resourceDescrURI'];
        $spo['s_type'] = 'uri';
        // predicate
        $spo['p'] = 'http://www.openarchives.org/ore/terms/isDescribedBy';
        // object
        $spo['o'] = $ar['describedBy'];  
        $spo['o_type'] = 'resource';
        $this->addTriple( $spo );
      }
      // aggregated resources if any
      if ( isset($ar['aggregatedResources']) ){

        forEach( $ar['aggregatedResources'] as $subAr ){
          $spo = array();
          // subject
          $spo['s'] = $ar['resourceDescrURI'];
          $spo['s_type'] = 'uri';
          // predicate
          $spo['p'] = 'http://www.openarchives.org/ore/terms/aggregates';
          // object
          $spo['o'] = $subAr;  
          $spo['o_type'] = 'resource';
          $this->addTriple( $spo );
        }

      }

    }
  }
  
  /**
    *
   */
  function addOreAggregates($uri, $descr_resource, $aggr_resource, $dc_metadata, $similarUri=null) {
  	// triple of the resource that describes this aggregation (resource map)
    $spo = array();

    // subject
    $spo['s'] = $uri;
    $spo['s_type'] = 'uri';
    // predicate
    $spo['p'] = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';
    // object
    $spo['o'] = 'http://www.openarchives.org/ore/terms/Aggregation';  
    $spo['o_type'] = 'resource';
    $this->addTriple( $spo );

    // subject
    $spo['s'] = $uri;
    $spo['s_type'] = 'uri';
    // predicate
    $spo['p'] = 'http://www.openarchives.org/ore/terms/isDescribedBy';
    // object
    $spo['o'] = $descr_resource;  
    $spo['o_type'] = 'resource';
    $this->addTriple( $spo );
    
    // triple of the aggregated resource
    foreach( $aggr_resource as $ar){
      // an aggregation cannot be aggregated by itself
      if ($uri != $ar['resourceDescrURI'])
        $this->addAR($uri, $ar['resourceDescrURI']);
    }

    // if non-protocol based URI exist -> add similarTo
    if ($similarUri != null){
      $spo = array();
      // subject
      $spo['s'] = $uri;
      $spo['s_type'] = 'uri';
      // predicate
      $spo['p'] = 'http://www.openarchives.org/ore/terms/similarTo';
      $spo['o'] = $similarUri;
      $spo['o_type'] = 'resource';
      $this->addTriple( $spo );
    }

    // add metadata
    foreach( $dc_metadata as $key => $value )
    {
      if (isset($value['nodevalues'])){
        for($i=0, $i_max = count($value['nodevalues']); $i < $i_max; $i++){
           $nodeId = $value['value'] . $i;
           $spo = array();
           // subject
           $spo['s'] = $uri;
           $spo['s_type'] = 'uri';
           // predicate
           $spo['p'] = $key;
           // object
           $spo['o'] = $nodeId;  
           $spo['o_type'] = $value['type'];
           $this->addTriple( $spo );

           $nodetype_spo = array();
           // subject
           $nodetype_spo['s'] = $nodeId;
           $nodetype_spo['s_type'] = $value['type'];
           // predicate
           $nodetype_spo['p'] = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';
           // object
           $nodetype_spo['o'] = $value['noderdftype'];  
           $nodetype_spo['o_type'] = 'resource';
           $this->addTriple( $nodetype_spo );

           $node_spo = array();
           // subject
           $node_spo['s'] = $nodeId;
           $node_spo['s_type'] = $value['type'];
           // predicate
           $node_spo['p'] = $value['noderdfterm'];
           // object
           $node_spo['o'] = $value['nodevalues'][$i];  
           $node_spo['o_type'] = 'literal';
           $this->addTriple( $node_spo );

        }


      }else{
        $spo = array();
        // subject
        $spo['s'] = $uri;
        $spo['s_type'] = 'uri';
        // predicate
        $spo['p'] = $key;
        // object
        $spo['o'] = $value['value'];  
        $spo['o_type'] = $value['type'];
        $this->addTriple( $spo );
      }
      //$spo['o_datatype'] = 'http://www.w3.org/2001/XMLSchema#date';
      
    }
  }

  function addAR($subjectUri, $objectUri){
      $spo = array();
      // subject
      $spo['s'] = $subjectUri;
      $spo['s_type'] = 'uri';
      // predicate
      $spo['p'] = 'http://www.openarchives.org/ore/terms/aggregates';
      // object
      $spo['o'] = $objectUri;
      $spo['o_type'] = 'resource';
      $this->addTriple( $spo );
  }

  /**
   *
   */
  function serialize() {
  
    return $this->rdfXmlSerializer->getSerializedTriples($this->triples);
  }

  function dump() {
    return print $this->serialize() . "\n";
  }
}
?>
