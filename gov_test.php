<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>gov_test</title>
</head>
<h1>Test der GOB Web-Service-Schnittstelle (nur lesend)</h1>
<h2>Alle Gemeinden und Städte des Kreises Rendsburg-Eckernförde (Schleswig-Holstein) ausgeben</h2>
<?php
require_once('GovTools.php');
$client = new SoapClient('http://gov.genealogy.net/services/ComplexService?wsdl');

$county = 'adm_131058'; // Rendsburg-Eckernförde (Schleswig-Holstein)
$today = unixtojd();  // Julianisches Datum
 
// 18 = Gemeinde, 95 = kreisfreie Stadt, 150 = Stadt, 145 = Markt
$list = $client->searchDescendantsByTypeAtDate($county,'18,95,150,145',$today);

$countyname = $client->getNameAtDate($county, $today, 'deu');
 
$names = array();
foreach($list->object as $place) {
    $names[$place->id] = GovTools::getName($place, $today, 'deu');
}
 
asort ($names);
foreach ($names as $id => $name) {
    echo "[$id|$name] ";
}
?>
<h2>Name und Lage ermitteln / Suche nach GOV-id</h2>
<?php
$place = $client->getObject('SCHERGJO54EJ');
 
$name = $place->name->value;
$latitude = $place->position->lat;
$longitude = $place->position->lon;
 
echo $name ." liegt bei ".$longitude."°O ".$latitude."°N.\n";
?>
<h2>GOV-Kennung auf Gültigkeit prüfen</h2>
<?php
$id = 'SCHERGJO54EJ';
 
$checkedId = $client->checkObjectId($id);
 
if( $id == $checkedId ) {
        echo "$id is valid.\n";
} else if( $checkedId == '' ) {
        echo "$id is invalid.\n";
} else {
        echo "$id has been replaced with $checkedId.\n";
}
?>
<h2>Suche nach Heidkate</h2>
<?php
$list = $client->searchByName(utf8_encode('Heidkate'));
/* Ausgabe des Arrays */
echo "<pre>";
print_r($list);
echo "</pre>";
?>
<body>
</body>
</html>