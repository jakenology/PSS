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
            font-family: 'Arial';
        }
        .header {
            text-align: center;
            font-family: 'Londrina Solid', cursive;
        }
        .enabled {color: lime;}
        .disabled {color: red;}
        .error {color: hotpink;}
        .null {color: hotpink;}
    </style>
</head>
<body>
    <h1 class="header">SafeSearch Status</h1>
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

        // Expanding on find_match()
        $lines = file($log);

        foreach($lines as $line) {
            if(strpos($line, $search) !== false) {
                $count++;
            } 
        }

        if($count !== 0) {
            echo $count;
        } else {
            echo '<span class="null">'.'None'.'</span>';
        }
    } else {
        echo '<span class="error">'.'Unsupported Provider'.'</span>';
    }
}
?>
    <p>Bing: <?php getStatus('Bing');?></p>
    <p>DuckDuckGo: <?php getStatus('DuckDuckGo');?></p>
    <p>Google: <?php getStatus('Google');?></p>
    <p>Pixabay: <?php getStatus('Pixabay');?></p>
    <p>YouTube: <?php getStatus('YouTube');?></p>

    <h1 class="header">Stats</h1>
        <p><strong>Bing</strong> Safe Requests: <?php getStats('Bing');?></p>
        <p><strong>DuckDuckGo</strong> Safe Requests: <?php getStats('DuckDuckGo');?></p>
        <p><strong>Google</strong> Safe Requests: <?php getStats('Google');?></p>
        <p><strong>Pixabay</strong> Safe Requests: <?php getStats('Pixabay');?></p>
        <p><strong>YouTube</strong> Safe Requests: <?php getStats('YouTube');?></p>
</body>
</html>
