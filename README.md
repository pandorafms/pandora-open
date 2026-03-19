![logo Pandora-Open](https://pandoraopen.io/wp-content/uploads/2025/12/Pandora-Open-mini.png)

## What is Pandora Open?

Pandora Open is the direct open-source continuation of Pandora FMS, the monitoring platform that has been powering enterprise infrastructure since 2004. After more than a decade of commercial dual-licensing, Pandora Open represents a clean break: a fully community-driven release that strips away proprietary layers and returns the project to its roots.

If you are running Pandora FMS 777, Pandora Open 1.0 is your natural upgrade path. It is built on the same proven architecture — same agents, same database schema, same server engine — but released entirely under the GPL2 licence with no enterprise paywall, no feature gating, and no vendor lock-in.

The project is maintained by some of the original core engineering team and welcomes community contributions and leadership. Whether you are migrating from 777 or starting fresh, Pandora Open is designed to be the monitoring platform you actually own.

Explore the project at `https://pandoraopen.io`

## One tool. Every layer.

Pandora Open doesn't care about your vendor or your platform. It’s built to be the Swiss Army knife of monitoring, combining multiple data sources into a single, powerful core:

- Infrastructure & Servers: Deep monitoring via lightweight agents for Linux, Windows, Solaris, AIX, BSD, and more.
- Network (NMS): Full SNMP support (V1, V2, V3), ICMP, and TCP checks for routers, switches, and firewalls.
- Advanced Data Sources: Built-in support for WMI, Netflow, and SNMP Traps.
- SSH/WMI remote monitoring.
- Graphical reporting, based on SQL backend
- SLA, and ITIL KPI metrics on reporting
- Status & Performance monitoring
- GIS tracking and viewing
- Netflow support
- User defined visual console screens and Dashboards WYSIWYG
- Very high capacity (Thousands of devices)
- Multitenant, several levels of ACL management.

## Why use Pandora Open?

- Pure 100% freesoftware, behind the opensource, GNU Spirit. No vendor lockings, no hidden agenda.
- Truly Scalable: Designed to grow from a small home lab to massive distributed environments without losing performance.
- Modular & Open: Its architecture is based on a high-performance MySQL backend, making it easy to extend, script, and customize to your specific needs.
- Remote & Agent-based: Choose between zero-footprint remote polling or deep-dive monitoring with local agents.
- History & Reliability: Two decades of community-driven evolution. We don't just measure if a system is "up"; we store and quantify complex data for long-term analysis.
- GPL2 licence.

## How to install Pandora Open

### From the repository

- Clone the repository.
- Install GNU Make.
- From the root of the repository, run `make && sudo make install`.

### From packages

- Download the packages (link).
- Download the deploy script (link) to the same directory.
- Run `sudo bash pandora_deploy.sh`.

Check the [manual installation on our wiki]([https://github.com/pandorafms/pandora-open/wiki/Build](https://github.com/pandorafms/pandora-open/wiki/Install))

## How to upgrade from Pandora FMS 777 to Pandora OPEN 1.0

Check the [migration guide](https://github.com/pandorafms/pandora-open/wiki/Upgrade)

## How to build the Pandora Open 

Check the [build section on our wiki](https://github.com/pandorafms/pandora-open/wiki/Build)

## More documentation

Visit our [wiki](https://github.com/pandorafms/pandora-open/wiki)
