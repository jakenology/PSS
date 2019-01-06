#!/usr/bin/env python
import distutils.spawn
import urllib
import os

## Important Variables
version="1.0"

class bcolors:
    OKBLUE = '\033[94m'
    OKGREEN = '\033[92m'
    INFO = "i"
    CHECKMARK = u'\u2713'
    WARNING = '\033[93m'
    FAIL = '\033[91m'
    X = u'\u2717'
    ENDC = '\033[0m'

requiredFiles = ["/etc/dnsmasq.d/05-restrict.conf", "/etc/hosts"]
requiredCommands = ["dig", "pihole"]

hosts = [
    "forcesafesearch.google.com",
	"restrictmoderate.youtube.com",
	"restrict.youtube.com",
	"strict.bing.com",
	"safe.duckduckgo.com"
]

host_records=[]
cnames=[]
def messenger(msg,info=False,err=False):
    end = bcolors.ENDC
    if info:
        start=bcolors.OKBLUE
        char = bcolors.INFO
    elif err:
        start = bcolors.FAIL
        char = bcolors.X
    else:
        start = bcolors.OKGREEN
        char = bcolors.CHECKMARK
    print start+'['+char+']',msg,end

def updateCheck():
    
def runCheck():
    # Check for required commands
    for cmd in requiredCommands:
        if not distutils.spawn.find_executable(cmd):
            messenger(("Command " + "\""+cmd+"\"" + " not found"),err=True)
        else:
            messenger(("Command " + "\""+cmd+"\"" + " found" ))
    # Check for Required Files
    for file in requiredFiles:
        if not os.path.exists(file):
            messenger(("File: " + file + " does not exist"), err=True)
        else:
            messenger(("Found file: " + file))

def getSupportedDomains():
    messenger("Downloading TLD List from Google",info=True)
    supported_doms = urllib.urlopen("https://www.google.com/supported_domains")
    supported_doms = supported_doms.read()
    supported_doms = supported_doms.splitlines()
    messenger(("Google has " + str(len(supported_doms)) + " top level domains"),info=True)
    for domain in supported_doms:
        dom = domain[1:]
        www = "www" + domain

        # Create the CNAME record
        target = "forcesafesearch.google.com"
        cname = createCNAME((dom+','+www),target)
        print(cname)

def createHostRecord(host, ip):
    string = 'host-record='+host+','+ip
    return string

def createCNAME(source, target):
    string = 'cname='+source+','+target
    return string

def main():
    runCheck()
    getSupportedDomains()

    demo = createCNAME(("google.com,www.google.com"),"forcesafesearch.google.com")
    print(demo)

    demo = createHostRecord("myplanefinder.net", "10.1.11.17")
    print(demo)

## Start the program
if __name__ == "__main__":
    main()
