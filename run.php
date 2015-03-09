<?php

require_once('phrets.php');

$login = 'http://sef.rets.interealty.com/Login.asmx/Login';
$username = 'Mperaltars';
$password = 'ertb2942';

$login = 'http://glvar.apps.retsiq.com/rets/login';
$username = 'koc';
$password = 'rets';


// start rets connection
$rets = new phRETS;

// Uncomment and change the following if you're connecting to a server that supports a version other than RETS 1.5
//$rets->AddHeader("RETS-Version", "RETS/1.7.2");

echo "+ Connecting to {$login} as {$username}<br>\n";
$connect = $rets->Connect($login, $username, $password);

// check for errors
if ($connect) {
        echo "  + Connected<br>\n";
}
else {
        echo "  + Not connected:<br>\n";
        var_dump($rets->Error());
        exit;
}

$types = $rets->GetMetadataTypes();

// check for errors
if (!$types) {
        print_r($rets->Error());
}
else {
  echo "Running Search ... \n";
  //var_dump($types);
  $search_options = array(
    "Limit" => "2",
    "Select" => "sysid",
    //"Select" => "StreetNumber,StreetName,ListPrice,City,StateOrProvince,PostalCode",
  );

  //var_dump($rets->GetMetadataTypes()); exit;
  //print_r($rets->GetMetadataClasses("Property")); exit;
  //var_dump($rets->GetMetadataTable("Property", 4)); exit;
  $limit = 100;
  $offset = 1;
  while ( $limit >= $offset ) {
    echo "Paginating... \n";
    $search = $rets->SearchQuery("Property", 1, "(104=2000-01-01+)", $search_options);
    var_dump($rets->TotalRecordsFound()); exit;

    var_dump($rets->Error());
    while($listing = $rets->FetchRow($search)) {
      echo "Address: {$listing['StreetNumber']} {$listing['StreetName']}, ";
      echo "{$listing['City']}, ";
      echo "{$listing['StateOrProvince']} {$listing['PostalCode']} listed for ";
      echo "\$".number_format($listing['ListPrice'])."\n";
    }

    $offset = $offset + $limit;
  }
  $rets->FreeResult($search);
}

echo "+ Disconnecting<br>\n";
$rets->Disconnect();
?>
