#!/usr/bin/env python3
"""Control configuration of safe site redirects for Pi-hole."""
import os
import sys
import argparse
import logging

__version__ = "2.0.0"

# Configure the logger
# Level is set after CLI args are parsed
_LOGGER = logging.getLogger()


DEFAULT_OUTPUT_PATH = "/etc/dnsmasq.d/"
DEFAULT_CONFIG_FILE = DEFAULT_OUTPUT_PATH + "config.yaml"


def load_config(config_file):
    """Return config from file."""
    _LOGGER.info("Starting load of config file %s", config_file.name)
    conf = None
    try:
        import yaml
    except ModuleNotFoundError:
        _LOGGER.exception(
            "Yaml module not found. Cannot parse config file."
            " Install PyYaml with pip."
        )
        return conf

    try:
        _LOGGER.debug("Calling yaml.load for file %s", config_file.name)
        conf = yaml.load(config_file, Loader=yaml.FullLoader)
    except Exception:
        _LOGGER.exception("Unable to parse config file:")
    finally:
        _LOGGER.info("Completed loading of config.")
        return conf


def enable_safe(key, conf, output):
    """Create safe browsing config for specified site."""
    record_types = ["A", "AAAA"]
    _LOGGER.info("Starting enable of safe site %s", key)
    if key not in conf.keys():
        _LOGGER.warn("Record %s not found in config...skipping", key)
        return

    # Build host-record
    if "safe_site" in conf[key].keys() and len(conf[key]["safe_site"]):
        safe_site = conf[key]["safe_site"]
    else:
        _LOGGER.warn("safe_site key does not exist for %s...skipping", key)
        return

    host_record_list = ["host-record=" + safe_site]
    # DNS lookup for safe_site.  Attempts both IPv4 and IPv6.
    # Needs to use external DNS and not local.  If Pi-Hole
    # is already configured for safe site gettaddrinfo will return
    # the already known IP and not catch a change of DNS upstream.
    try:
        _LOGGER.info("Attempting to resolve DNS for site %s", safe_site)
        import dns.resolver

        # Config resolver to use external DNS
        resolver = dns.resolver.Resolver(configure=False)
        resolver.nameservers = ["1.1.1.1"]
    except ModuleNotFoundError:
        _LOGGER.exception(
            "DNSPython module not found. Cannot perform DNS lookup."
            " Install dnspython with pip"
        )
        return
    for record_type in record_types:
        try:
            _LOGGER.debug("Resolving %s record for site %s", record_type, safe_site)
            a_record = resolver.query(safe_site, record_type)
        except dns.resolver.NoAnswer:
            _LOGGER.info("Unable to find %s record for %s", record_type, safe_site)
        except:
            _LOGGER.exception("There was an unhandled error during DNS resolution")
        else:
            _LOGGER.debug("DNS %s lookup for %s was successful", record_type, safe_site)
            host_record_list.append(str(a_record[0]))
    # len should be at least 2 if we were able to resolve an address.
    if len(host_record_list) < 2:
        _LOGGER.warn("Unable to resolve an address for %s...skipping", safe_site)
        return

    # Build a list of raw sites to that need to be redirected to the safe site
    # if url is specified fetch that list.  Otherwise grab redirect_site list
    # from configuration file
    raw_redirects = []
    if "redirect_fetch" in conf[key].keys():
        _LOGGER.debug(
            "Using URL %s to fetch redirect list for %s",
            conf[key]["redirect_fetch"],
            safe_site,
        )
        try:
            import requests

            req = requests.get(conf[key]["redirect_fetch"], timeout=1)
            req.raise_for_status()
        except ModuleNotFoundError:
            _LOGGER.exception("Requests module not found. Install PyYaml with pip.")
            return
        except requests.exceptions.HTTPError:
            _LOGGER.error(
                "Unable to pull URL list %s for site %s...skipping",
                conf[key]["redirect_fetch"],
                key,
            )
            return
        else:
            # Need to go through and clean up the get data.  There aren't a lot
            # of lists out there so I can only do clean up for the one I know about.
            _LOGGER.debug(
                "Fetched %s sites to redirect to %s",
                len(req.text.split("\n")),
                safe_site,
            )
            for url in req.text.split("\n"):
                # Cleanup specific to google list.  It starts with a "."
                if url[0:1] == ".":
                    raw_redirects.append(url[1:])
    elif "redirect_site" in conf[key].keys():
        _LOGGER.debug("Using list of sites to redirect from config for %s", safe_site)
        raw_redirects = conf[key]["redirect_site"]
    else:
        _LOGGER.error("No list of sites to redirect provided for %s...skipping", key)

    # Now clean up the list.  Some URL may not be WWW prepended.
    # add that if necessary. Basically check for more than one "."
    cname_redirects = []
    for raw_redirect in raw_redirects:
        if raw_redirect[0:3] == "www":
            cname_redirects.append("cname={},{}".format(raw_redirect, safe_site))
        else:
            cname_redirects.append(
                "cname=www.{0},{0},{1}".format(raw_redirect, safe_site)
            )

    if type(output) is not str:
        _LOGGER.debug(
            "Sending results to stdout because no output directory was provided"
        )
        output.write(",".join(host_record_list) + "\n")
        output.write("\n".join(cname_redirects) + "\n")
    else:
        if output[-1:] != "/":
            output += "/"
        file_name = "{}05-{}.conf".format(output, key)
        try:
            _LOGGER.debug("Attempting to open file %s", file_name)
            fd = open(file_name, "w")
        except PermissionError:
            _LOGGER.exception("Unable to open file %s...skipping", file_name)
            return
        _LOGGER.info("Writting to file %s", fd.name)
        fd.write(",".join(host_record_list) + "\n")
        fd.write("\n".join(cname_redirects) + "\n")
        fd.close

    _LOGGER.info("Completed enable of safe browsing config for site %s", key)


def disable_safe(safe_site, output):
    """Delete safe search config for site."""
    if output[-1] != "/":
        output += "/"
    file_path = "{}05-{}.conf".format(output, safe_site)
    try:
        _LOGGER.info("Attempting to delete file %s", file_path)
        os.remove(file_path)
    except PermissionError:
        _LOGGER.exception("Insufficent permissions to delete %s...skipping", file_path)
    except FileNotFoundError:
        _LOGGER.debug("Config file for site %s was not found. Nothing to do", safe_site)
        pass
    _LOGGER.debug("Delete of file %s complete", file_path)
    return


def main():
    """Execute the script."""
    # Build arg list
    cli_args = argparse.ArgumentParser()
    cli_args.add_argument(
        "-c",
        "--config",
        type=argparse.FileType("r"),
        default=DEFAULT_CONFIG_FILE,
        required=True,
        help="{} configuration file".format(os.path.basename(__file__)),
    )
    cli_args.add_argument(
        "-o",
        "--output",
        default=sys.stdout,
        help="Specify directory to ouput conf to.",
        metavar="PATH",
    )
    cli_args.add_argument(
        "-e",
        "--enable",
        nargs="*",
        default=argparse.SUPPRESS,
        help="List of sites to enable. Empty list defaults to all",
        metavar="SITE",
    )
    cli_args.add_argument(
        "-d",
        "--disable",
        nargs="*",
        default=argparse.SUPPRESS,
        help="List of sites to disable. Empty list defaults to all",
        metavar="SITE",
    )
    cli_args.add_argument(
        "-v",
        "--verbose",
        type=str.upper,
        choices=["CRITICAL", "ERROR", "WARNING", "INFO", "DEBUG"],
        default="WARNING",
        help="Set the verbose level using standard python logger levels,"
        " WARNING is default",
        metavar="LEVEL",
    )

    # Get CLI args
    args = cli_args.parse_args()
    logging.basicConfig(
        format="%(asctime)s:%(levelname)s: %(message)s",
        datefmt="%d-%b-%y %H:%M:%S",
        level=args.verbose,
    )

    # Parse the config
    conf = load_config(args.config)

    if conf is None:
        exit(1)

    # Deal with some corner cases because enable and disable aren't mutually exclusive
    # case of cli like --enable --disable
    # becuase the default if none are specified do all
    if (
        hasattr(args, "enable")
        and hasattr(args, "disable")
        and not len(args.enable)
        and not len(args.disable)
    ):
        exit(0)

    sites_enable = []
    sites_disable = []
    # Get all sites to enable safe search.  Default is all if none listed in --enable
    if hasattr(args, "enable"):
        sites_enable = conf.keys()
    if hasattr(args, "enable") and len(args.enable):
        sites_enable = args.enable
    _LOGGER.debug("Raw list of sites to enable: %s", sites_enable)

    # Get all sites to enable safe search.  Default is all if none listed in --disable
    if hasattr(args, "disable"):
        sites_disable = conf.keys()
    if hasattr(args, "disable") and len(args.disable):
        sites_disable = args.disable
    # --disable without list was spec nothing to enable since all is default
    # clear enable list
    elif hasattr(args, "disable") and not len(args.disable):
        sites_enable = []
    _LOGGER.debug("Raw list of sites to disable: %s", sites_disable)

    # Find result of sites to enable and disable
    # handle case of same list for --enable --disable
    if set(sites_enable) == set(sites_disable):
        exit(0)
    enable_results = list(set(sites_enable) - set(sites_disable))

    _LOGGER.info("Sites to be enabled: %s", enable_results)
    _LOGGER.info("Sites to be disabled: %s", sites_disable)

    for site in enable_results:
        enable_safe(site, conf, args.output)
    if type(args.output) is str:
        for site in sites_disable:
            disable_safe(site, args.output)


# Start the program
if __name__ == "__main__":
    main()
