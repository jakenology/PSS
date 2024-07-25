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
            echo '<span class="enabled fas fa-check fa-2x"></span>';
        } else {
            echo '<span class="disabled fas fa-times-circle fa-2x"></span>';
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
        // Propietary... $count = shell_exec("fsearch $log $search");
        //Recommended (≈2 seconds)
        $count = shell_exec("grep -c $search $log");

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
<html lang="EN">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SafeSearch Status</title>
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Londrina+Solid|Germania+One|Josefin+Sans" rel="stylesheet">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
        <style>
            body {
                background: url(https://shrinky.link/P9TU);
                background-size: 100%;
            }
            #header {
                text-align: center;
                font-family: 'Londrina Solid', cursive;
                color: white;
                text-shadow: 3px 3px black;

            }
            table {
                width: 85%;
                border-collapse: collapse;
            }
            table.center {
                margin-left: auto;
                margin-right: auto;
            }
            th, td {
                padding: 10px;
            }
            tr:nth-child(odd){background-color: lightgrey}
            tr:nth-child(even){background-color: #f2f2f2}
            th {
                background-color: lightgrey;
                font-family: 'Germania One', cursive;
                font-size: 20px;
                color: black;
            }
            td {font-family: 'Josefin Sans', sans-serif;}
            td img {
                display: block;
                margin-left: auto;
                margin-right: auto;
            }
            .enabled {
                color: lime;
                text-shadow: 2px 2px 4px green;
            }
            .disabled {
                color: red;
                text-shadow: 2px 2px 4px pink;
            }
            .error {color: hotpink;}
            .null {color: hotpink;}
            td.num {
                text-align: right;
                font-size: 25px;
            }
            #fm {
                text-align: center;
                font-family: 'Londrina Solid', cursive;
            }
        </style>
    </head>
    <body>
        <h1 id="header">SafeSearch Status</h1>
        <table class="center">
            <tr>
                <th>Provider</th>
                <th>Status</th> 
                <th>Safe Queries</th>
            </tr>
            <tr>
                <td>
                    <a href="https://downdetector.com/status/bing">
                        <img src="https://cdn4.iconfinder.com/data/icons/social-media-logos-6/512/125-bing-512.png" width="50" alt="Bing Logo">
                    </a>
                </td>
                <td align="center"><?php getStatus('Bing');?></td> 
                <td class="num"><?php getStats('Bing');?></td>
            </tr>
            <tr>
                <td>
                    <a href="https://downdetector.com/status/duckduckgo">
                        <img src="https://cdn3.iconfinder.com/data/icons/social-media-special/256/duckduckgo-256.png" width="50" alt="DuckDuckGo Logo">
                    </a>
                </td>
                <td align="center"><?php getStatus('DuckDuckGo');?></span></td>
                <td class="num"><?php getStats('DuckDuckGo');?></td>
            </tr>
            <tr>
                <td>
                    <a href="https://downdetector.com/status/google">
                        <img src="https://cdn2.iconfinder.com/data/icons/social-icons-33/128/Google-256.png" width="50" alt="Google Logo">
                    </a>
                </td>
                <td align="center"><?php getStatus('Google');?></td> 
                <td class="num"><?php getStats('Google');?></td>
            </tr>
            <tr>
                <td>
                    <a href="http://currentlydown.com/pixabay.com">
                        <img src="https://cdn.pixabay.com/photo/2017/01/17/14/41/pixabay-1987080_960_720.png" width="50" alt="Pixabay Logo">
                    </a>
                </td>
                <td align="center"><?php getStatus('Pixabay');?></td>
                <td class="num"><?php getStats('Pixabay');?></td>
            </tr>
            <tr>
                <td>
                    <a href="https://downdetector.com/status/youtube">
                        <img src="https://cdn1.iconfinder.com/data/icons/logotypes/32/youtube-256.png" width="50" alt="YouTube Logo">
                    </a>
                </td>
                <td align="center"><?php getStatus('YouTube');?></td>
                <td class="num"><?php getStats('YouTube');?></td>
            </tr>
        </table>
        <!-- Automatic Page Refresh (Will be replaced by AJAX) -->
        <script>
            function timedRefresh(timeoutPeriod) {
	            setTimeout("location.reload(true);",timeoutPeriod);
            }
            window.onload = timedRefresh(2000);
        </script>
    </body>
</html>
