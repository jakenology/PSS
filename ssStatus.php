<?php
// V.A.F.E
$conf = '/etc/dnsmasq.d/05-restrict.conf';
$log = '/var/log/pihole.log';
$providers = [
    'Bing' => 'strict.bing.com',
    'DuckDuckGo' => 'safe.duckduckgo.com',
    'Google' => 'forcesafesearch.google.com',
    'Pixabay' => 'safesearch.pixabay.com',
    'YouTube' => 'restrict.youtube.com',
];

function find_match($file, $string) {
    $search = &$string; // Unecessary Assignment? 
    $lines = file($file);

    // Look for a match in each line
    foreach($lines as $line) {
        if(strpos($line, $search) !== false) {
            return true;
        } 
    }
}

function getStatus($provider) {
    // Properly Scope Variables
    global $conf, $providers;
    $hr_prefix = 'host-record=';

    // Check if key is valid
    if(array_key_exists($provider,$providers)) {
        // Continue 
        $host_record = $hr_prefix.$providers[$provider];
        if(find_match($conf, $host_record) !== null) {
            echo '<span class="enabled">'.'Enabled'.'</span>';
        } else {
            echo '<span class="disabled">'.'Disabled'.'</span>';
        }
    } else {
        echo '<span class="error">'.'Unsupported Provider'.'</span>';
    }
}

function getStats($provider) {
    // Properly Scope Variables
    $count = 0;
    global $log, $providers; //, $search, $count;
    
    // Check if key is valid
    if(array_key_exists($provider,$providers)) {
        $search = $providers[$provider];

       // Fork the command instead
        $count = shell_exec("fsearch $log $search");

        if($count !== 0) {
            echo number_format($count);
        } else {
            echo '<span class="null">'.'None'.'</span>';
        }
    } else {
        echo '<span class="error">'.'Unsupported Provider'.'</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SafeSearch Status</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Londrina+Solid" rel="stylesheet">
    <style>
        body {
            margin: 50px;
            background-color: gray;
            font-family: 'Arial';
            color: white;
        }
        .header {
            text-align: center;
            font-family: 'Londrina Solid', cursive;
        }
        .header_img {
            display: block;
            margin-left: auto;
            margin-right: auto;
            width: 150px;
        }
        .enabled {color: lime;}
        .disabled {color: red;}
        .error {color: hotpink;}
        .null {color: hotpink;}
        .warning {
            text-transform: upper;
            color: orange;
        }
    </style>
</head>
<body>
    <h1 class="header">SafeSearch Status</h1>
    <img src="http://www.abccarolinas.org/portals/40/Images/Logos/Safety%20First.jpg" alt="Paris" class="header_img">

    <p>Bing: <?php getStatus('Bing');?></p>
    <p>DuckDuckGo: <?php getStatus('DuckDuckGo');?></p>
    <p>Google: <?php getStatus('Google');?></p>
    <p>Pixabay: <?php getStatus('Pixabay');?></p>
    <p>YouTube: <?php getStatus('YouTube');?></p>

    <h1 class="header">Stats</h1>
    <h3 class="warning">Note: Counts are reset at 12:00 AM every day.</h3>
        <p><strong>Bing</strong> Safe Requests: <?php getStats('Bing');?></p>
        <p><strong>DuckDuckGo</strong> Safe Requests: <?php getStats('DuckDuckGo');?></p>
        <p><strong>Google</strong> Safe Requests: <?php getStats('Google');?></p>
        <p><strong>Pixabay</strong> Safe Requests: <?php getStats('Pixabay');?></p>
        <p><strong>YouTube</strong> Safe Requests: <?php getStats('YouTube');?></p>
</body>
</html>
