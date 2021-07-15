#!/bin/bash
# SafeSearch List Generator for Pi-hole 4.0 and above
# Created by Jayke Peters
## Define Global Variables
## ENABLE IN PIHOLE?

me=`basename "$0"`
VERSION="1.6.1" # Fixed IP Address for Duckduckgo. Added SafeSearch for pixabay..., also fixed spelling error 
file="/tmp/safesearch.txt"
conf="/etc/dnsmasq.d/05-restrict.conf"
url="https://www.google.com/supported_domains"

## Logging Variables
log="/var/log/${me}.log"
maxRuns=10



## Arrays
# Host Records!!!
hostRecords=(
    "forcesafesearch.google.com"
    "restrictmoderate.youtube.com"
    "safe.duckduckgo.com"
    "strict.bing.com"
    "safesearch.pixabay.com"
    "safeapi.qwant.com"
)
yt-strictSS=(
   "cname=www.youtube.com,restrict.youtube.com"
   "cname=m.youtube.com,restrict.youtube.com"
   "cname=youtubei.googleapis.com,restrict.youtube.com"
   "cname=youtube.googleapis.com,restrict.youtube.com"
   "cname=www.youtube-nocookie.com,restrict.youtube.com"
)
yt-moderateSS=(
   "cname=www.youtube.com,restrictmoderate.youtube.com"
   "cname=m.youtube.com,restrictmoderate.youtube.com"
   "cname=youtubei.googleapis.com,restrictmoderate.youtube.com"
   "cname=youtube.googleapis.com,restrictmoderate.youtube.com"
   "cname=www.youtube-nocookie.com,restrictmoderate.youtube.com"
)
bingSS=(
    "cname=bing.com,www.bing.com,strict.bing.com"
)
badEXACT=(
    "www.ecosia.org"
    "images.search.yahoo.com"
    "video.search.yahoo.com"
    "search.aol.com"
    "gibiru.com"
    "www.startpage.com"
)
duckduckgoSS=(
    "cname=duckduckgo.com,www.duckduckgo.com,start.duckduckgo.com,safe.duckduckgo.com"
    "cname=duck.com,www.duck.com,safe.duckduckgo.com"
)
pixabaySS=(
    "cname=pixabay.com,safesearch.pixabay.com"
)
qwantSS=(
    "cname=qwant.com,www.qwant.com,api.qwant.com,safeapi.qwant.com"
    "cname=s1.qwant.com,s2.qwant.com,safeapi.qwant.com"
)
REGEX=(
    "(^|\.).+xxx$"
    "(^|\.).+sexy$"
    "(^|\.).+webcam$"
    "(^|\.).+sex$"
    "(^|\.).+porn$"
    "(^|\.).+tube$"
    "(^|\.).+cam$"
    "(^|\.).+adult$"
)

## Setup Logging
exec 2>>$log
logger() {
    write() {
        echo [`date '+%Y-%m-%d %H:%M:%S:%3N'`]: "$*" >> $log
    }
    print() {
        echo [`date '+%Y-%m-%d %H:%M:%S:%3N'`]: "$*"
    }
    all() {
        write "$*" 
        print "$*"
    }
    pass() {
        echo "$*"
    }
    error() {
        write "$*"
        pass "$*"
    }
    begin() {
        # Enforce Run Count
        runNum=$(cat $log | grep 'STARTED' | wc -l)
        if [ $runNum == $maxRuns ]; then
            print FLUSHING LOG
            rm -rf $log
        fi
        write STARTED 
    }
    end() {
        write STOPPED
        # https://wtanaka.com/node/7719
        end=$(cat $log|awk '{print length}'|sort -nr|head -1)
        # https://stackoverflow.com/questions/5349718/how-can-i-repeat-a-character-in-bash
        line=$(for ((i=1; i<=$end; i++)); do echo -n =; done)
        pass $line >> $log
    }
    # Take Input
    "$@"
}
## Check Sudo
if [ "$EUID" -ne 0 ];then 
    echo "Please run this script with sudo!"
    exit 1
fi

## START LOGGING EVERYTHING
logger begin

silently() {
    "$@" &>/dev/null
}

preCheck() {
    # Check for sudo rights
    checkSudo
    
    # Is there an old file?
    if [ -f "$file" ]; then
        logger all Removing "$file"
        rm "$file"
    fi
}

generate() {
    # Download List into an Array
    logger all Retrieving List from Google
    domains=($(curl $url 2>/dev/null))

    # Append File Header
    echo "# $file generated on $(date '+%m/%d/%Y %H:%M') by $(hostname)" >> "${file}"
    echo "# Google SafeSearch Implementation" >> "${file}" 

    # Add IP's and host records
    for domain in "${hostRecords[@]}"; do
        ips="$(nslookup $domain | grep "Address" | grep -oE "\b((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b" | sed -n 2p)"
        if [ "$domain" = "forcesafesearch.google.com" ]; then
            printf 'host-record=%s,restrict.youtube.com,%s,::ffff:%s \n' $domain $ips $ips >> "${file}"
        else
            printf 'host-record=%s,%s,::ffff:%s \n' $domain $ips $ips >> "${file}"
        fi
    done
    # Add host records
    #for line in "${hostRecords[@]}"; do
    #    echo "$line" >> "${file}"
    #done

    # Generate list of domains
    for domain in "${domains[@]}"; do
        dom=$(echo $domain | cut -c 2-)
        #echo cname=$dom,"www""$domain",forcesafesearch.google.com >> "${file}"
        #you only want the www variant of google because using google.com blocks android push notifications. 
        echo cname="www""$domain",forcesafesearch.google.com >> "${file}"
    done

    # Get the number of domains
    count=$(cat $file | grep 'forcesafesearch.google.com' | wc -l)
    total=$(($count * 2))
    logger all ''$count' TLDs'
    logger all ''$total' Domains'

    # YouTube SafeSearch 
    if [ "$YOUTUBE" == "Strict" ]; then
        for line in "${yt-strictSS[@]}"
            do echo "$line"  >> "${file}"
        done
    elif [ "$YOUTUBE" == "Moderate" ]; then
        for line in "${yt-moderateSS[@]}"
            do echo "$line"  >> "${file}"
        done 
    fi
    
    # DuckDuckGo SafeSearch
    for line in "${duckduckgoSS[@]}"
        do echo "$line" >> "${file}"
    done
    
    # Bing Strict Setting
    for line in "${bingSS[@]}"
        do echo "$line"  >> "${file}"
    done
    
    # Pixabay
    for line in "${pixabaySS[@]}"
        do echo "$line"  >> "${file}"
    done
    
    # Qwant
    for line in "${qwantSS[@]}"
        do echo "$line"  >> "${file}"
    done
    
    # Enable In Hosts and Pi-hole
    if [ "$ENABLE" == "True" ]; then
        logger all 'ENABLING SAFESEARCH FOR PI-HOLE'
        if [ -f "$conf" ]; then
            rm -Rf "$conf"
            cp -R "$file" "$conf"
        else
            cp -R "$file" "$conf"
        fi
        for host in "${ssHosts[@]}"; do 
            if ! grep -Fxq "$host" "$hosts"; then
                echo "$host" >> "$hosts"
            fi
        done
        # Extra Blocking
        logger all 'BLOCKING OTHER BAD SITES'
        silently pihole -b "${badEXACT[@]}"
        silently pihole --regex "${REGEX[@]}"
        # We do not need to do "pihole restartdns"
        # The above commands reload it every time...
    fi   
}

main() {
    preCheck
    generate
}

quiet() {
    silently main
}

web() {
    silently main
    logger pass $file
}

help() {
    # Log Invalid Arguments
    if [ ! -z "$*" ]; then
        # https://linuxconfig.org/how-do-i-print-all-arguments-submitted-on-a-command-line-from-a-bash-script
        args=("$@") 
        logger error INVALID ARGUMENT: "${args[@]}" 
        sleep 1
        clear
    fi
    # Print Usage Information
    clear
    logger pass "$me version $version
    Usage: $me [options]
    Example: '$me --web'
    Youtube Strict-Example: '$me --enable --yt-strict' or '$me --e --yt-s'
    Youtube Moderate-Example: '$me --enable --yt-moderate' or '$me --e --yt-m'
    -e, --enable  Enable SafeSearch            
    -d, --disable Disable SafeSearch
    -w, --web     For use with PHP Script
    -s, --silent  Execute Script Silently
    -v, --version Display the Script's Version
    -h, --help    Display this help message"
}

enable() {
    ENABLE=True
    main
}

disable() {
    logger all 'Removing Temp File'
    rm -rf "$file"
    logger all 'Removing Config File'
    rm -rf "$conf"
    logger all 'Unblocking Domains and TLDs'
    silently pihole regex --delmode "${REGEX[@]}"
    silently pihole blacklist --delmode "${badEXACT[@]}"
    logger all 'Restarting DNS'
    silently pihole restartdns
    logger all 'SafeSearch is Disabled!'

}
## Check for user input
case "${@}" in
    *yt-s | *yt-strict   ) YOUTUBE=Strict;;
    *yt-m | *yt-moderate ) YOUTUBE=Moderate;;
    *                    ) YOUTUBE=False;;
esac
if [[ $# = 0 ]]; then
    main
else
    logger write "ARGUMENTS: $1"
    case "${1}" in
        *e | *enable ) enable;;
        *d | *disable) disable;;
        *w | *web     ) web;;
        *s | *silent* ) quiet;;
        *v | *version) echo -e 'Current Version:\t' "$VERSION";;
        *h | *help    ) help;;
        *             ) help "$@";;
    esac
fi

## STOP LOGGING EVERYTHING
logger end
