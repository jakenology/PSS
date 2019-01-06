#!/bin/bash
# Safe Search Engines for Pi-hole Installation Script
# Check if PIP is installed
function verifyInstallation() {
    for var in "$@"; do
        if ! which $var &> /dev/null; then
            echo -e "\"$var\" is not installed!"
            echo "Installing PIP"
            apt-get install -y pip &>/dev/null
            echo "PIP Successfully Installed"
        else
            echo "PIP Is Already Installed"
        fi
    done
}

function installPKGs() {
    pkg=(
        "dnspython"
    )

    for pkg in "${pkg[@]}"; do
        echo -e "Installing PIP PKG: \"$pkg\""
        pip install $pkg &>/dev/null
    done
}

function installTool() {
    url="https://raw.githubusercontent.com/jaykepeters/Pi-hole_SafeSearch/master/Pi-hole_SafeSearch"
    toolName="Pi-hole_SafeSearch"
    dest="/usr/local/bin"
    path="$dest""/$toolName"
    echo "Downloading from GitHub"
    curl -s -L "$url" --output "$path"
    echo "Ensuring Proper File Permissions"
    chmod +x "$path"
    echo "Successfully Installed!"
}

## Main Function
function main() {
    verifyInstallation pip
    installPKGs
    installTool
}
main
