<?php
// API Version
$version = '1.0';

$funcs = [
    // Key      Value
    'stats' => ['provider', 'reset'],
    'status' => 'provider',
];

$search_providers = [
    'bing' => 'Bing',
    'duckduckgo' => 'DuckDuckGo',
    'google' => 'Google',
    'pixabay' => 'Pixabay',
    'youtube' => 'YouTube',
];

// Multidimensional Array
$arr = $funcs['demo'];
$ct = count($arr);
$end = sizeof($arr);
if($ct !== 1) {
    foreach(range(0,$end) as $index) {
        echo $arr[$index]."\n";
    } 
} else {
    echo $arr;
}

function throwException($code, $message) {
    // Set the HTTP Response Code
    http_response_code($code);

    // Print the fatal message
    die($message);
}

function api() {
    // Scope Variables
    global $funcs, $search_providers;

    // Define the API trigger
    $trigger = 'r';
    $reqest = $_GET[$trigger];

    // Check if trigger is present
    if(isset($reqest)) {
        // Convert to lowercase
        $request = strtolower($reqest);
        
        // Validate the request
        if(array_key_exists($request,$funcs)) {
            switch($reqest) {
                case 'stats':

                case 'status':

                default: 
                    break;
            }
        } else {
            throwException(400, 'Illegal Request!');
        }
    } else {
        // No 
        echo 'Welcome to the API!';
    }
}

// Run the api
api();
/*
function api() 
{
    // Scope Variables
    global $providers;
    // Check if any parameters were passed
    if(isset($_GET['r'])) { 
        global $req;
        $req = strtolower($_GET['r']);

        // Switcher
        switch($req) {
            case 'getstats':
                $query = strtolower($_GET['provider']);
                if(isset($query)) {
                    switch($query) {
                        case 'bing':
                            echo "Provider: $query";
                            break;
                        case 'duckduckgo':
                            echo "Provider: $query";
                            break;
                        case 'google':
                            echo "Provider: $query";
                            break;
                        case 'pixabay':
                            echo "Provider: $query";
                            break;
                        default:
                            http_response_code(400);
                            echo 'Unsupported Provider!';
                            break;
                    }
            } else {
                http_response_code(400);
                echo 'Illegal Request!';
                break;
            }
            break;
            default:
                echo 'Illegal Request!';
                break;
        }
    } else {
        http_response_code(400);
        echo 'Illegal Request!';
    }
}

// Run the api...
api();
*/
?>
