<?php
/**
 * Exposing metadata records as OAI-ORE rdf
 * PHP version 5
 * @package ECO4R
 * @author    Jochen Schirrwagen <jochen.schirrwagen@uni-bielefeld.de>
 * @copyright Bielefeld University Library, 2011
 * @license http://www.dipp.nrw.de/lizenzen/dfsl/ 
 * @version 1.0
 */
// OPUS dependencies
require_once '../../lib/opus.class.php';
require_once '../../lib/opusrecord.class.php';

// RDF and ORE dependencies
require_once ('../../lib/arc/ARC2.php');
require_once ('../../lib/ore/ConstructORE.php');
require_once ('../../lib/ore/oaioreexport.class.php');

// create opus and db objects
$opus = new OPUS('../../lib/opus.conf');

// use some config properties
if (!defined('OPUS_HOME'))
  define('OPUS_HOME', $opus->value('url'));
if (!defined('REPOSITORY_NAME'))
  define('REPOSITORY_NAME', $opus->value('repository_name'));
if (!defined('REPOSITORY_IDENTIFIER'))
  define('REPOSITORY_IDENTIFIER', $opus->value('repository_identifier'));
if (!defined('OAI_URL'))
  define('OAI_URL', $opus->value('oai_url'));
if (!defined('OPUS_TABLE'))
  define('OPUS_TABLE', $opus->value('opus_table'));
if (!defined('OPUS_LANGUAGE'))
  define('OPUS_LANGUAGE', $opus->value('la'));

$dsn = $opus->value('dbms') . '://' . $opus->value('db_user') . ':' 
     . $opus->value('db_passwd') . '@' . $opus->value('db_host') . '/' . $opus->value('db');
$connection =& DB::connect($dsn);
if (PEAR::isError($connection)) {
    die($connection->getMessage());
}
$connection->setOption('autofree', true);
$connection->setFetchMode(DB_FETCHMODE_ASSOC);
$connection->query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");
$metadata = &OpusRecord::find($connection, $id);

if (PEAR::isError($metadata)) {
    die($metadata->getMessage());
}
//////////////////// move to new class
$oreExport = new OaiOreExport();
$output .= '<metadata>';
//arc2 adds the xml declaration, which needs to be removed in the oai-record metadata section, since it leads to an xml processing array
$output .= $oreExport->export($metadata, true);
$output .= '</metadata>';

?>
