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
    'username' => 'MpeRaltars',
    'password' => 'ertb2942',
    'uapassword' => '123456',
  ],
];

foreach ($major_markets as $major_market  => $connection_data) {
  $login = $connection_data['login'];
  $username = $connection_data['username'];
  $password = $connection_data['password'];

  // start rets connection
  $rets = new phRETS;

  $rets->AddHeader("User-Agent", 'RETS-Connector/1.2');

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
    //if($major_market == 'las_vegas' || $major_market == 'hawaii') { continue; }
    echo "  + Connected\n";
  } else {
    echo "  + Not connected:\n";
    var_dump($rets->Error());
    continue;
  }

  $meta_data_types = $rets->GetMetadataTypes();
  echo 'aa';
  exit;

  //// filter out everything other than property
  //foreach ($meta_data_types as $data_type) {
    //if $data_type[
  //}



  $property_types = [
    "single_family" => 1,
  ];

  foreach($property_types as $property_type_name => $property_type_id) {
    generate_csv($rets, $major_market, $property_type_name, $property_type_id);
  }

  function generate_csv($rets, $major_market, $type_name, $type_id) {

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
      //"Limit" => "NONE",
      "Limit" => 2,
      "Select" => "sysid",
      //"Select" => "StreetNumber,StreetName,ListPrice,City,StateOrProvince,PostalCode",
    );

      //var_dump($rets->GetMetadataTypes()); exit;
      //print_r($rets->GetMetadataClasses("Property")); exit;
      //var_dump($rets->GetMetadataTable("Property", 4)); exit;

    // time the search query
    $time_start = microtime(true);

    // select all properties
    $search = $rets->SearchQuery("Property", $type_id, "(sysid=0+)", $search_options);

    $time_end = microtime(true);
    $total_time = $time_end - $time_start;

    echo "  + Search took $total_time seconds and found {$rets->TotalRecordsFound()} records\n";


    $file_name = "$type_name.csv";
    $fh = fopen("data/$major_market/".$file_name, "w+");

    echo 'Displaying results...';
    $counter = 1;
    while($listing = $rets->FetchRow($search)) {


      $search2 = $rets->SearchQuery("Property", 1, "(sysid={$listing['sysid']})", ["Limit" => 1]);

      if($counter == 1) {
        // print headers
        $fields = $rets->SearchGetFields($search2);
        fputcsv($fh, $fields);
      }

      $property = $rets->FetchRow($search2);
      fputcsv($fh, $property);

      $counter++;
      flush();
    }

    $rets->FreeResult($search);
    $rets->FreeResult($search2);

  }

  echo "+ Disconnecting\n";
  $rets->Disconnect();
}
?>
