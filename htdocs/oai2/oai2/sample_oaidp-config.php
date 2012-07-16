<?php
/**
 * Configuration of the OAI Data Provider
 *
 * This file is part of OPUS. The software OPUS has been developed at the
 * University of Stuttgart with funding from the German Research Net
 * (Deutsches Forschungsnetz), the Federal Department of Higher Education and
 * Research (Bundesministerium fuer Bildung und Forschung) and The Ministry of
 * Science, Research and the Arts of the State of Baden-Wuerttemberg
 * (Ministerium fuer Wissenschaft, Forschung und Kunst des Landes
 * Baden-Wuerttemberg).
 *
 * PHP versions 4 and 5
 *
 * OPUS is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * OPUS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @package     OPUS
 * @author      Heinrich Stamerjohanns <stamer@uni-oldenburg.de>
 * @author      U. Mueller
 * @author      Annette Seiler <seiler@hbz-nrw.de>
 * @author      Annette Maile <maile@ub.uni-stuttgart.de>
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Heinrich Stamerjohanns, 2002-2003
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Id: oaidp-config.php 202 2007-05-07 08:32:47Z freudenberg $
 */

// Dies ist die Konfigurationsdatei fuer den PHP OAI Data-Provider.
// Bitte lesen Sie die ganze Datei, denn es gibt einige Dinge, die
// angepasst werden muessen, vor allem bei der DB-Anbindung
// wir benutzen PEAR-Klassen. PEAR muss auf dem Rechner installiert werden
// Hier wird der Pfad zu den PEAR-Klassen angegeben
// ini_set('include_path',
// '.:/opt/php/bin/pear:/opt/php/lib/php:/usr/bin/pear:/usr/share/php/:/usr/local/lib/php');
//ini_set('include_path', '.:/usr/local/lib/php');
// Wenn es Probleme mit "unknown numrows" gibt, muss eine neuere Version
// von PEAR installiert werden.
require_once ('DB.php');
require_once ('Archive/Tar.php');
// Verknuepfung mit der OPUS-Configdatei, aus der auch einige Daten genommen werden sollen
// Achtung: der relative Pfad ../../lib/ bezieht sich auf das aufrufenden Skript
// oai2.php, das sich eine Ebene hoeher befindet!
include_once ("../../lib/opus.class.php");
$opus = new OPUS('../../lib/opus.conf');
$db = $opus->value("db");
$opus_home = $opus->value("url");
$oai_url = $opus->value("oai_url");
$repository_name = $opus->value("repository_name");
$repository_identifier = $opus->value("repository_identifier");
$db_user = $opus->value("db_user");
$db_passwd = $opus->value("db_passwd");
$email = $opus->value("email");
$la = $opus->value("la");
// bitte nicht veraendern
$MY_URI = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
// MUST (only one)
// Wird jetzt aus der opus.conf gelesen.
$repositoryName = $repository_name;
// XMetaDiss-Originale: Sollen Originale der Dissertationen (sofern in einem archivierungswuerdigen
// Format vorliegend) mit an die DDB uebermittelt werden?
// anerkannte Formate fuer das Quellformat sind XML, SGML, HTML, TEX, TXT u.a. Klartextformate
// true: Originale werden uebermittelt, false: Originale werden nicht uebermittelt
$xmetadiss_orig = false;
// $baseURL			  = $MY_URI;
// Man kann auch einen statischen URI benutzen.
$baseURL = $oai_url;
// bitte nicht veraendern
$protocolVersion = '2.0';
// Opus gibt keine Information zu geloeschten Dateien. Bitte nicht veraendern
$deletedRecord = 'no';
// Die Datestamps in Opus haben eine Granularitaet von Sekunden
$granularity = 'YYYY-MM-DDThh:mm:ssZ';
// MUST (only one)
// Bitte anpassen. Das Datum an dem der erste Datensatz im Archiv aufgenommen wurde
// (muss nicht genau sein. Das Datum muss nur vor dem ersten Datum liegen
$earliestDatestamp = '1999-01-01';
// Dies wird hinzugefuegt wenn die Granularitaet in Sekunden ist
// Bitte nicht veraendern
if ($granularity == 'YYYY-MM-DDThh:mm:ssZ') {
    $earliestDatestamp.= 'T00:00:00Z';
}
// MUST (multiple)
// Wird jetzt aus der opus.conf gelesen.
$adminEmail = array('mailto:' . $email);
// MUST (only one)
// Bitte nicht veraendern
$delimiter = ':';
// MUST (only one)
// Man darf irgendeinen Namen benutzen, aber wenn man dem OAI-Format fuer
// unique identifiers entsprechen moechte, siehe
// see: http://www.openarchives.org/OAI/2.0/guidelines-oai-identifier.htm
// Basically use domainname-word.domainname
// Bitte anpassen
$repositoryIdentifier = $repository_identifier;
// Beschreibung des Repositories wird in identify.php gemacht
$show_identifier = true;
// Man kann Details ueber sein Community oder Freunde (andere Dataprovider)
// einschliessen. Siehe identify.php fuer moegliche Container in der
// Identify-Abfrage
// Maximum Anzahl Datensaetze, die geliefert werden sollen (Verb: ListRecords)
// Wenn es mehr Datensaetze gibt, wird ein Resumption Token generiert
$MAXRECORDS = 50;
// Maximum Anzahl Identifiers, die geliefert werden sollen (Verb: ListIdentifiers)
// Wenn es mehr Identifiers gibt, wird ein Resumption Token generiert
$MAXIDS = 1000;
// Nach 24 Stunden werden die Resumption Tokens ungueltig:
$tokenValid = 24*3600;
$expirationdatetime = gmstrftime('%Y-%m-%dT%TZ', time() +$tokenValid);
// Benutze dies zum testen
//$CONTENT_TYPE = 'Content-Type: text/plain';
// Benutze dies im Echtbetrieb
$CONTENT_TYPE = 'Content-Type: text/xml';
// Sets werden automatisch aus den Tabellen resource_type_$la und sachgruppe_ddc_$la generiert.
// Sollten aus anderen Tabellen Sets generiert werden, muss die Datei
// /phpoai/oai2/listsets.php sowie die Funktion setQuery spaeter in diesem Dokument
// entsprechend angepasst werden
// Hier werden die Spaltennamen aus der Tabelle opus gegeben, aus denen
// Sets generiert werden
$SETS = array("sachgruppe_ddc", "type", "source_swb");
// Definiere alle unterstuetzten Metadatenformate
//
// myhandler ist der Dateinamen der Datei, die Anfragen in einem bestimmten
// Metadatenformat handled
// [record_prefix] beschreibt den optionalen Prefix fuer die Metadaten
// [record_namespace] beschreibt den Namespace fuer diesen Prefix
// FIXME Schluessel des Arrays ist der metadataPrefix.
$METADATAFORMATS = array('oai_dc' => array('metadataPrefix' => 'oai_dc', 'schema' => 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd', 'metadataNamespace' => 'http://www.openarchives.org/OAI/2.0/oai_dc/', 'myhandler' => 'record_dc.php', 'record_prefix' => 'dc', 'record_namespace' => 'http://purl.org/dc/elements/1.1/'), 
'epicur' => array('metadataPrefix' => 'epicur', 'schema' => 'http://www.persistent-identifier.de/xepicur/version1.0/xepicur.xsd', 'metadataNamespace' => 'urn:nbn:de:1111-2004033116', 'myhandler' => 'record_epicur.php', 'other_namespace' => 'xmlns="urn:nbn:de:1111-2004033116"'),
// Spezifikation fuer Print-on-Demand mit ProPrint
'oai_pp' => array('metadataPrefix' => 'oai_pp', 'schema' => 'http://www.proprint-service.de/xml/schemes/v1/PROPRINT_METADATA_SET.xsd', 'metadataNamespace' => 'http://www.proprint-service.de/xml/schemes/v1/', 'myhandler' => 'record_proprint.php'),
// XMetaDiss 1.3
'xMetaDiss' => array('metadataPrefix' => 'xmetadiss', 'schema' => 'http://www.d-nb.de/standards/xmetadiss/xmetadiss.xsd', 'metadataNamespace' => 'http://www.d-nb.de/standards/xMetaDiss/', 'myhandler' => 'record_xmetadiss.php', 'record_prefix' => 'xMetaDiss', 'other_namespace' => 'xmlns:cc="http://www.d-nb.de/standards/cc/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:pc="http://www.d-nb.de/standards/pc/" xmlns:urn="http://www.d-nb.de/standards/urn/" xmlns:thesis="http://www.ndltd.org/standards/metadata/etdms/1.0/" xmlns:ddb="http://www.d-nb.de/standards/ddb/" xmlns="http://www.d-nb.de/standards/subject/"'),
// XMetaDissPlus 1.3
'XMetaDissPlus' => array('metadataPrefix' => 'xmetadissplus', 'schema' => 'http://www.bsz-bw.de/xmetadissplus/1.3/xmetadissplus.xsd', 'metadataNamespace' => 'http://www.bsz-bw.de/xmetadissplus/1.3', 'myhandler' => 'record_xmetadissplus.php'),
// OAI-ORE v1.0
'oai_ore' => array('metadataPrefix' => 'ore', 
'schema' => 'http://www.openarchives.org/OAI/2.0/rdf.xsd', 
'metadataNamespace' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 
'myhandler' => 'record_ore.php') //,
//array('metadataPrefix'=>'olac',
//	'schema'=>'http://www.language-archives.org/OLAC/olac-2.0.xsd',
//	'metadataNamespace'=>'http://www.openarchives.org/OLAC/0.2/',
//	'handler'=>'record_olac.php'
//)
);
// Wenn im Container <dc:rights> eine allgemeine Botschaft stehen soll, so kann
// sie hier gegeben werden:
//$dc_rights = "Copyright der Metadaten: Universitaetsbibliothek Stuttgart";
//
// DATABASE SETUP
//
// entsprechend dem lokalen Datenbanksetup anpassen.
// DB_USER, DB_PASSWD, DB_NAME werden aus der opus.conf gelesen.
$DB_HOST = 'localhost';
$DB_USER = $db_user;
$DB_PASSWD = $db_passwd;
$DB_NAME = $db;
// Data Source Name: Dies ist der universelle Verbindungsstring
// zur Datenbank. Wenn Sie eine andere Datenbank benutzen, bitte
// entsprechend aendern
// Beispiel fuer MySQL
$DSN = "mysql://$DB_USER:$DB_PASSWD@$DB_HOST/$DB_NAME";
// Beispiel fuer MSQL
//$DSN = "msql://$DB_USER:$DB_PASSWD@$DB_HOST/$DB_NAME";
// Beispiel fuer Oracle
// $DSN = "oci8://$DB_USER:$DB_PASSWD@$DB_NAME";
// Der Charakterset in dem die Metadaten in der Datenbank gespeichert werden
// Es wird nur utf-8 oder iso8859-1 unterstuetzt
//$charset = "iso8859-1";
$charset = "UTF-8";
// Wenn Entities wie < > ' " in den Metadaten schon escaped wurde, dies auf
// 'true' setzen. In Opus werden Sonderzeichen nicht escaped.
$xmlescaped = false;
// Ab hier gibt es, ausser in den Funktionen setQuery (wenn man andere Sets ausser
// DDC-Sachgruppen geben moechte) oder getClass (wenn man andere
// Klassifiaktionsschemata als die in Opus benutzten hat), keine Veraenderungen mehr.
// Schlagwoerter werden in einer Zelle gespeichert und mit
// Leerzeichen-Komma-Leerzeichen getrennt.
$SQL['split'] = ' , ';
// Der Name der Tabelle in der die Hauptmetadaten gespeichert werden
$SQL['table'] = 'opus';
// Der Name der Spalte, in der die laufenden IDs der Datensaetze gespeichert
// werden
$SQL['id_column'] = 'source_opus';
$SQL['identifier'] = 'source_opus';
// Wenn der der Identifier in irgendeiner Art einen Prefix hat:
$idPrefix = '';
// Dies wird der externe (OAI) Identifier fuer einen Datensatz sein.
// Sollte nicht geaendert werden.
$oaiprefix = "oai" . $delimiter . $repositoryIdentifier . $delimiter . $idPrefix;
// Beispiel-Identifier
$sampleIdentifier = $oaiprefix . '165';
// Der Name der Spalte, in der das Erzeugungsdatum gespeichert wird
$SQL['datestamp'] = 'date_creation';
// Der Name der Spalte, in der das Aenderungsdatum gespeichert wird
$SQL['date_modified'] = 'date_modified';
// Der Name der Spalte in der Information ueber geloeschte Datensaetze
// stehen. So lassen - Opus zeigt kein geloeschten Datensaetze an
$SQL['deleted'] = 'deleted';
// Nicht veraendern
$SQL['set'] = 'oai_set';
// Als naechstes ein paar Datenbankabfragen. Sie wurden fuer Opus angepasst.
// Diese Funktion generiert eine Abfrage fuer die Tabelle opus
function selectallQuery($id = '') {
    global $SQL, $la;
    $query = 'SELECT o.source_opus, o.title, o.creator_corporate, o.subject_swd,
        o.description, o.publisher_university, o.contributors_name, o.contributors_corporate,
        o.date_year, o.type, o.source_title, o.language, o.subject_uncontrolled_german,
        o.subject_uncontrolled_english, o.title_en, o.description2, s.sachgruppe_en,
        o.urn, o.date_creation, o.date_modified, o.subject_type, o.sachgruppe_ddc, o.bereich_id, o.lic
        FROM ' . $SQL['table'] . ' AS o, resource_type_' . $la . ' AS r, sachgruppe_ddc_' . $la . ' AS s
        WHERE ';
    if ($id == '') {
        $query.= $SQL['id_column'] . ' = ' . $SQL['id_column'];
    } else {
        $query.= $SQL['identifier'] . " ='$id'";
    }
    $query.= " AND (r.typeid = o.type AND s.nr = o.sachgruppe_ddc)";
    return $query;
}
// Holt die Daten aus opus fuer oai_pp (Print-on-Demand mit Proprint)
function oaippQuery($id = '') {
    global $SQL, $la;
    $query = 'SELECT o.source_opus, o.title, o.creator_corporate, o.subject_swd, 
	o.description, o.publisher_university, o.contributors_name, o.contributors_corporate, 
	o.date_year, o.type, o.source_title, o.language, o.subject_uncontrolled_german, 
	o.subject_uncontrolled_english, o.title_en, o.description2, s.sachgruppe_en, 
	o.urn, o.date_creation, o.date_modified, o.subject_type, o.sachgruppe_ddc, o.bereich_id
	FROM ' . $SQL['table'] . ' AS o, resource_type_' . $la . ' AS r, sachgruppe_ddc_' . $la . ' AS s, license_' . $la . ' AS l 
	WHERE ';
    if ($id == '') {
        $query.= $SQL['id_column'] . ' = ' . $SQL['id_column'];
    } else {
        $query.= $SQL['identifier'] . " ='$id'";
    }
    $query.= " AND (r.typeid = o.type AND s.nr = o.sachgruppe_ddc AND l.shortname = o.lic)";
    return $query;
}
function xmetadissQuery($id = '') {
    global $SQL, $la;
    $query = 'SELECT o.source_opus, o.title, o.creator_corporate, o.subject_swd, 
	o.description, o.publisher_university, o.contributors_name, o.contributors_corporate, 
	o.date_year, o.type, o.source_title, o.language, o.subject_uncontrolled_german, 
	o.subject_uncontrolled_english, o.title_en, o.description2, s.sachgruppe_en, 
	o.urn, o.date_creation, o.date_modified, o.subject_type, o.sachgruppe_ddc, d.title_de, 
	a.creator_name, o.verification, d.advisor, d.date_accepted, o.date_creation, o.description_lang, 
	o.description2_lang, o.bereich_id, o.lic, d.publisher_faculty
	FROM ' . $SQL['table'] . ' AS o, ' . $SQL['table'] . '_diss AS d, ' . $SQL['table'] . '_autor AS a, sachgruppe_ddc_' . $la . ' AS s
	WHERE ';
    if ($id == '') {
        $query.= 'o.' . $SQL['id_column'] . ' = d.' . $SQL['id_column'] . ' AND o.' . $SQL['id_column'] . ' = a.' . $SQL['id_column'];
    } else {
        $query.= 'o.' . $SQL['id_column'] . ' = d.' . $SQL['id_column'] . ' AND o.' . $SQL['id_column'] . ' = a.' . $SQL['id_column'] . ' AND o.' . $SQL['identifier'] . " ='$id'";
    }
    $query.= " AND s.nr = o.sachgruppe_ddc AND urn is not null";
    return $query;
}
# , institute AS inst, '.$SQL['table'].'_inst AS i     .' AND o.'.$SQL['id_column'].' = i.'.$SQL['id_column']
// Funktion gibt zu einem Dateityp den passenden MIME-Typ zurueck
function conv_type($typ) {
    if ($typ == "html" || $typ == "htm") return "text/html";
    if ($typ == "txt") return "text/plain";
    if ($typ == "sgml" || $typ == "sgm") return "text/sgml";
    if ($typ == "xml") return "text/xml";
    if ($typ == "rtf") return "application/rtf";
    if ($typ == "css") return "text/css";
    if ($typ == "pdf") return "application/pdf";
    if ($typ == "doc" || $typ == "dot") return "application/msword";
    if ($typ == "sxw" || $typ == "stw") return "application/vnd.sun.xml.writer";
    if ($typ == "sxc") return "application/vnd.sun.xml.calc";
    if ($typ == "sxd") return "application/vnd.sun.xml.draw";
    if ($typ == "sxi") return "application/vnd.sun.xml.impress";
    if ($typ == "sxg") return "application/vnd.sun.xml.writer.global";
    if ($typ == "sxm") return "application/vnd.sun.xml.math";
    if ($typ == "ps") return "application/postscript";
    if ($typ == "tex") return "application/tex";
    if ($typ == "latex") return "application/latex";
    if ($typ == "dvi") return "application/dvi";
    if ($typ == "ppt" || $typ == "ppz" || $typ == "pps" || $typ == "pot") return "application/powerpoint";
    if ($typ == "arc" || $typ == "arj" || $typ == "lha" || $typ == "lhz") return "application/octet-stream1";
    if ($typ == "exe" || $typ == "com") return "application/octet-stream2";
    if ($typ == "zip") return "application/zip";
    if ($typ == "tar") return "application/x-tar";
    if ($typ == "gz" || $typ == "z" || $typ == "tgz" || $typ == "taz") return "application/x-compressed";
    if ($typ == "xls") return "application/vnd.ms-excel";
    if ($typ == "dtd") return "application/xml-dtd";
    if ($typ == "jpg" || $typ == "jpe" || $typ == "jpeg") return "image/jpeg";
    if ($typ == "gif") return "image/gif";
    if ($typ == "tiff" || $typ == "tif") return "image/tiff";
    if ($typ == "png") return "image/png";
    if ($typ == "mp3") return "audio/mpeg";
    if ($typ == "mpg" || $typ == "mpeg") return "video/mpeg";
    if ($typ == "vrml" || $typ == "vrm" || $typ == "vrl") return "model/vrml";
    return "unknown";
}
// Autoren sind bei Opus in einer separaten Datenbank und muessen daher
// separat aufgerufen werden
function selectAuthor($id = '') {
    $query = 'SELECT creator_name FROM opus_autor WHERE ';
    if ($id == '') {
        $query.= $SQL['id_column'] . ' = ' . $SQL['id_column'];
    } else {
        $query.= "source_opus = '$id'";
    }
    $query.= " ORDER BY reihenfolge";
    return $query;
}
// Diese Funktion gibt Identifier und Datumsstempel fuer alle Datensaetze zurueck
function idQuery($id = '') {
    global $SQL;
    $query = 'select ' . $SQL['identifier'] . ',' . $SQL['datestamp'] . ',' . $SQL['date_modified'] . ',sachgruppe_ddc,type,source_swb FROM ' . $SQL['table'] . ' WHERE ';
    if ($id == '') {
        $query.= $SQL['id_column'] . ' = ' . $SQL['id_column'];
    } else {
        $query.= $SQL['identifier'] . " = '$id'";
    }
    return $query;
}
// Die naechste Funktion ermittelt Institut und Fakultaet des Datensatzes
function facinstQuery($id) {
    global $la;
    $query = 'SELECT i.name, f.fakultaet 
	FROM institute_' . $la . ' AS i, faculty_' . $la . ' AS f, opus_inst AS o
	WHERE o.source_opus = ' . $id . ' AND (i.nr = o.inst_nr AND f.nr = i.fakultaet)';
    return $query;
}
// Filter fuer until
function untilQuery($until) {
    global $SQL;
    // Datum muss in Unixform gebracht werden
    $until = unixTime($until);
    return $SQL['datestamp'] . " <= '$until'";
}
// Filter fuer until_modified
function until_modifiedQuery($until) {
    global $SQL;
    // Datum muss in Unixform gebracht werden
    $until = unixTime($until);
    return $SQL['date_modified'] . " <= '$until'";
}
// Filter fuer from
function fromQuery($from) {
    global $SQL;
    // Datum muss in Unixform gebracht werden
    $from = unixTime($from);
    return $SQL['datestamp'] . " >= '$from'";
}
// Filter fuer from_modified
function from_modifiedQuery($from) {
    global $SQL;
    // Datum muss in Unixform gebracht werden
    $from = unixTime($from);
    return $SQL['date_modified'] . " >= '$from'";
}
// Filter fuer Sets
function setQuery($set, $verb) {
    global $SQL;
    global $SETS;
    $teile = explode(':', $set);
    if ($teile[0] == 'ddc') {
        if ($verb == 'listrecords') {
            $string = ' and o.' . $SETS[0] . ' = ' . $teile[1];
        } elseif ($verb == 'listidentifiers') {
            $string = ' and ' . $SETS[0] . ' = ' . $teile[1];
        }
    } elseif ($teile[0] == 'pub-type') {
        if ($verb == 'listrecords') {
            $string = ' and o.' . $SETS[1] . ' = ' . $teile[1];
        } elseif ($verb == 'listidentifiers') {
            $string = ' and ' . $SETS[1] . ' = ' . $teile[1];
        }
    } elseif ($teile[0] == 'has-source-swb') {
        if ($verb == 'listrecords') {
            $string = ' and ASCII(o.' . $SETS[2] . ')';
        } elseif ($verb == 'listidentifiers') {
            $string = ' and ASCII(' . $SETS[2] . ')';
        }
        if ($teile[1] == 'true') {
            $string.= ' <> 0';
        } else {
            $string.= ' = 0';
        }
    }
    return $string;
}
// Die folgende Funktion stellt die URL zum Volltext zusammen (wird dc:identifier)
function getURL($source_opus, $jahr) {
    global $volltext_url;
    $url = $volltext_url . '/' . $jahr . '/' . $source_opus . '/';
    return $url;
}
// die folgende Funktion holt Klassifikationsinformation
// anpassen, wenn es noch andere Klassifikationen gibt
// $subject_type ist Inhalt der Spalte opus.subject_type
// $klassifikation ist Name der Tabelle mit den Klassifikationsdaten
function getClass($source_opus, $subject_type) {
    if ($subject_type = "ccs") {
        $klassifikation = "ccs98";
    } elseif ($subject_type = "msc") {
        $klassifikation = "msc2000";
    } elseif ($subject_type = "pacs") {
        $klassifikation = "pacs2003";
    }
    $query = 'SELECT k.bez 
	FROM ' . $klassifikation . ' AS k, opus AS o, opus_' . $subject_type . ' AS v 
	WHERE o.source_opus = ' . $source_opus . ' AND (v.source_opus = o.source_opus AND k.class = v.class)';
    return $query;
}
// Ab hier muss nichts geaendert werden
// Jetzt-Zeit
// $datetime = strftime('%Y-%m-%dT%T'); # Origional wird hier gmstrftime benutzt
$datetime = strftime('%Y-%m-%dT%T');
$responseDate = $datetime . 'Z';
// bitte nicht aendern
# encoding mit UTF-8 moeglich? Standard ist eigentlich ISO8859-1
$XMLHEADER = '<?xml version="1.0" encoding="' . $charset . '"?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
         http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">' . "\n";
$xmlheader.= $XMLHEADER . ' <responseDate>' . $responseDate . "</responseDate>\n";
// Der XML Schema Namensraum. Bitte nicht aendern
$XMLSCHEMA = 'http://www.w3.org/2001/XMLSchema-instance';
?>
