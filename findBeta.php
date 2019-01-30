<!DOCTYPE html>
<html lang="en">
<head>
    <title>SafeSearch Status</title>
    <style>
        body {
            font-family: 'Arial';
        }
        #header {
            text-align: center;
            color: lime;
        }
    </style>
</head>
<body>
    <h1 id="header">SafeSearch <span style="color: black;">Status</span></h1>
<?php
// Global Scope Variables
$config = '/etc/dnsmasq.d/05-restrict.conf';
$color = 'purple';

// Find Matches in the file
function find_match($file, $string) {
    // What to look for
    $search = &$string;

    // Read from file
    $lines = file($file);

    foreach($lines as $line) {
        $matched = false;
        if(strpos($line, $search) !== false) {
            $matched == true;
            // echo "Matched: " . $line;
            return true;
        } else {
            $matched == false;
        }
    }
}
// What we're looking for
$hr = 'host-record=';

$ss = [
    "Bing" => $hr . "strict.bing.com",
    "DuckDuckGo" => $hr . "safe.duckduckgo.com",
    "Google" => $hr . "forcesafesearch.google.com",
    "Pixabay" => $hr . "safesearch.pixabay.com",
];

function getStatus($provider) {
    $host_record = $ss[$provider];
    echo $host_record;
    $result = find_match($config,$host_record);
    if($result !== null) {
        echo 'Enabled';
        $color = 'lime';
    } else {
        echo 'Disabled';
        $color = 'red';
    }
}
/*foreach ($ss as $provider => $host_record) {
    $result = find_match($config,$host_record);
    if($result !== null) {
        echo ucfirst($provider) . " is enabled\n";
    } else {
        echo ucfirst($provider) . " is not enabled\n";
    }
} */
?>
    <p>Bing: <span style="color: <?php echo $color;?>;"><?php getStatus('Bing');?></span></p>
    <p>DuckDuckGo: <span style="color: <?php echo $color;?>;"><?php getStatus('DuckDuckGo');?></span></p>
    <p>Google: <span style="color: <?php echo $color;?>;"><?php getStatus('Google');?></span></p>
    <p>Pixabay: <span style="color: <?php echo $color;?>;"><?php getStatus('Pixabay');?></span></p>

    <h1>Beta</h1>
    <?php
        echo $config;
        echo $ss['DuckDuckGo'];
    ?>
</body>
