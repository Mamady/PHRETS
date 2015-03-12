<?php

require_once('phrets.php');

// define connections for each major market
$major_markets = [
  'hawaii' => [
    'login'    => 'http://matrixrets.hicentralmls.com/rets/login.ashx',
    'username' => 'JtsmitH02',
    'password' => 'X8y8H65X',
  ],

  'las_vegas' => [
    'login'    => 'http://glvar.apps.retsiq.com/rets/login',
    'username' => 'koc',
    'password' => 'rets',
  ],

  'miami' => [
    'login'    => 'http://sef.rets.interealty.com/Login.asmx/Login',
    'username' => 'mpealtrs',
    'password' => 'ertb2942',
    'uapassword' => 'fghi4921',
  ],
];



function download_images($rets, $major_market, $type_name, $type_id) {

  //// check for errors
  //if (!$types) {
    //print_r($rets->Error());
  //}
    //var_dump($types);
  echo "Running Search ... ";
  //$searching = true;
  //while ($searching) {
  //}
  //echo "Running Search ... ";
  $search_options = array(
    "Limit" => "NONE",
    //"Limit" => 2,
    "Select" => "sysid,157",
    //"Select" => "StreetNumber,StreetName,ListPrice,City,StateOrProvince,PostalCode",
  );

    //var_dump($rets->GetMetadataTypes()); exit;
    //print_r($rets->GetMetadataClasses("Property")); exit;
    //var_dump($rets->GetMetadataTable("Property", 1)); exit;

  // time the search query
  $time_start = microtime(true);

  // select all properties
  $search = $rets->SearchQuery("Property", $type_id, "(sysid=0+)", $search_options);

  $time_end = microtime(true);
  $total_time = $time_end - $time_start;

  $total_records = $rets->NumRows();
  echo "  + Search took $total_time seconds and found $total_records records\n";
  //echo "  + Search took $total_time seconds and found {$rets->TotalRecordsFound()} records\n";

  $image_path = "$major_market/$type_id";
  if(!is_dir($image_path)) {
    mkdir($image_path, 0777, true);
  }

  $counter = 1;
  while($listing = $rets->FetchRow($search)) {
    $i = 1;
    $ml_id = $listing[157];
    $photos = $rets->GetObject("Property", "Photo", $listing['sysid']);

    if(is_array($photos)) {
      foreach($photos as $photo) {
        file_put_contents($image_path."/Photo$ml_id-$i.jpeg", $photo['Data']);
        //echo 'saved '.$image_path."/Photo$ml_id-$i.jpeg\n";
        $i++;
      }
    }
    if($counter%20 == 1) {
      echo round($counter*100/$total_records)."% complete\n";
    }
    flush();
    $counter++;
  }

  $rets->FreeResult($search);
}

foreach ($major_markets as $major_market  => $connection_data) {

  if($major_market == 'las_vegas' || $major_market == 'hawaii') { continue; }

  $login = $connection_data['login'];
  $username = $connection_data['username'];
  $password = $connection_data['password'];

  // start rets connection
  $rets = new phRETS;

  $rets->AddHeader("User-Agent", 'PHRETS/1.0');

  // Uncomment and change the following if you're connecting to a server that supports a version other than RETS 1.5
  //$rets->AddHeader("RETS-Version", "RETS/1.7.2");

  echo "+ Connecting to {$login} as {$username}\n";
  if (array_key_exists('uapassword', $connection_data)) {
    $connection = $rets->Connect($login, $username, $password, $connection_data['uapassword']);
  } else {
    $connection = $rets->Connect($login, $username, $password);
  }

  // check for errors
  if ($connection) {
    echo "  + Connected\n";
  } else {
    echo "  + Not connected:\n";
    var_dump($rets->Error());
    continue;
  }

  $meta_data_types = $rets->GetMetadataTypes();

  //var_dump($meta_data_types);exit;

  // filter out everything other than property
  $property_types = [];
  foreach ($meta_data_types as $data_type) {
    if($data_type['Resource'] == 'Property') {
      foreach($data_type['Data'] as $property_type) {
        // remove anything after a space
        $name = strtolower(preg_replace("/[^A-Za-z]/", '_', $property_type['Description']));
        $name = explode('__', $name);

        $class_id = $property_type['ClassName'];
        $property_types[$name[0]] = $class_id;
      }
    }
  }

  foreach($property_types as $property_type_name => $property_type_id) {
    download_images($rets, $major_market, $property_type_name, $property_type_id);
  }

  echo "+ Disconnecting\n";
  $rets->Disconnect();
}
?>
