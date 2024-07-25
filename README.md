# WARNING
There is a new beta version of Pi-hole which may interfere with the capabilites of this script. I will check into this and see if PSS will work! Until then, keep posted!

# Special Thanks To

Barry Quiel for fixing the Python version. This may be the future of PSS!

# SafeSearch for Pi-hole

![Pi-hole Logo](https://i0.wp.com/pi-hole.net/wp-content/uploads/2017/06/Vortex-r.png?resize=100%2C100&ssl=1)

# Supported Search Engines

<img src="https://www.festisite.com/static/partylogo/img/logos/google.png" alt="Google Logo" width="225" height="86">
<img src="https://dwglogo.com/wp-content/uploads/2016/01/DuckDuckGo_logo_004.svg" alt="DuckDuckGo Logo" width="275" height="154.75">
<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e9/Bing_logo.svg/2000px-Bing_logo.svg.png" alt="Bing Logo" width="200" height="89.2">

# Installation

To install Pi-hole SafeSearch (PSS), simply run the following commands in your pi-hole terminal (log into pi with ssh; pi hole should be set up and running).

1. Download the Pi-hole SafeSearch script:
  
    `wget https://raw.githubusercontent.com/jakenology/PSS/master/Pi-hole_SafeSearch.sh`

2. Move the script.
  
    `sudo mv ./Pi-hole_SafeSearch.sh /usr/local/bin/`

3. Make the script executable.
  
    `sudo chmod a+x /usr/local/bin/Pi-hole_SafeSearch.sh`

4. Enable Pi-hole SafeSearch:
  
    `sudo Pi-hole_SafeSearch.sh -e`


# How to enable SafeSearch via the Pi-hole Admin Interface (v5.0+)
1. Click on the "Custom DNS Tab"
2. For every provider you want, type in their domain name and SAFE IP addresses. 
- Google is tricky because they have some 300 top-level domains, so users could bypass SafeSearch by going to google.ca in the us, etc. 
- Bing, DuckDuckGo, and other providers should be fine. You want to make sure to add the www, as well as non-www variants for those search providers. Duck.com also redirects to duckduckgo.com right now as well...

# Uninstallation

If you wish to not enforce SafeSearch on your network anymore, please follow the steps below to completely uninstall PSS
1. `Pi-hole_SafeSearch.sh --disable`
2. PSS creates the following files:
  - `/var/log/pss.log`
  - `/etc/pss.ack`
  - `/tmp/safesearch.txt`
  - `/etc/dnsmasq.d/05-restrict.conf` (Version < 2.0)
  - `/etc/dnsmasq.d/SafeSearch.conf`
3. Version 2.1 will have the --uninstall option
  - User has to type y/n to confirm
  - All files will be deleted, including the script
  
# API Draft
- GET 
  * /getStatus
  * /getStatus/{provider}
  * /getStatistics
  * /getStatistics/{provider}
- POST
  * /enable/{provider}&auth={KEY}
  * /disable/{provider}&auth={KEY}
