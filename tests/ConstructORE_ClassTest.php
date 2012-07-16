<?php
require_once 'PHPUnit/Framework/TestCase.php';
require_once '../lib/arc2/ARC2.php';
require_once '../lib/ore/ConstructORE.php';

class ConstructORE_ClassTest extends PHPUnit_Framework_TestCase {

  public function setUp(){
    $this->myORE = new ConstructORE();
    $this->parser = ARC2::getRDFXMLParser();
  }

  public function testSomething(){
    $this->myORE->setAgent("http://www.example.com/ore/123",
                           "http://www.example.com/ore/123"."#aggregation", 
                           array('agent_name' => 'Agents Name', 'agent_url' => 'http://www.example.org'), 
                           "2011-05-09");
    $rdfxmlData = $this->myORE->serialize();
    $base = 'http://example.com';
    $this->parser->parse($base, $rdfxmlData); 
    $triples = $this->parser->getTriples();
    print(sizeof($triples));
    print_r($triples);
  }
}

$dc_title = 'some Title';
$dc_ddc = 'ddc:001';
$dc_creator = array('Firstname1 Lastname1', 'Firstname2 Lastname2');
$dc_metadata = array('http://purl.org/dc/terms/title' => array( 'type' => 'literal', 'value' => $dc_title),
                     'http://purl.org/dc/elements/1.1/subject' => array( 'type' => 'resource', 'value' => 'http://dewey.info/class/' . $dc_ddc . '/'),
                     'http://purl.org/dc/elements/1.1/creator' => array( 'type' => 'bnode', 'value' => '_:creator', 'nodevalues' => $dc_creator, 'noderdftype' => 'http://xmlns.com/foaf/0.1/Person', 'noderdfterm' => 'http://xmlns.com/foaf/0.1/name'),
'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' => array( 'type' => 'resource', 'value' => 'http://purl.org/spar/fabio/DoctoralThesis')
);

$myORE = new ConstructORE();
$myORE->setAgent("http://www.example.com/ore/123",
                 "http://www.example.com/ore/123"."#aggregation", 
                 array('agent_name' => 'Agents Name', 'agent_url' => 'http://www.example.org'), 
                 "2011-05-09");

// create the URIs for
// * the 'main' aggregation
// * the metadata aggregation
// * the scholarly work aggregation
// * the supplementary files aggregation


$aggr_resources = array();
array_push($aggr_resources, array(
'resourceDescrURI' => 'http://link.to.splash',
'types' => array('http://purl.org/spar/fabio/webPage'),
'aggregatedBy' => 'http://www.example.com/ore/123#aggregation'
)
);
array_push($aggr_resources, array(
'resourceDescrURI' => 'http://www.example.com/ore/123#metadata',
'types' => array('http://purl.org/spar/fabio/Metadata', 'http://www.openarchives.org/ore/terms/Aggregation'),
'aggregatedBy' => 'http://www.example.com/ore/123#aggregation',
'aggregatedResources' => array('http://link.to.metadata'),
'describedBy' => 'http://www.example.com/ore/123/metadata'
)
);



$myORE->addOreAggregates("http://www.example.com/ore/123"."#aggregation",
                         "http://www.example.com/ore/123", 
                         $aggr_resources, 
                         $dc_metadata, $similarUri="urn:nbn:123");
$myORE->addAggregatedResource($aggr_resources);
$myORE->dump();
?>
