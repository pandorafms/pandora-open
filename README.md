![logo Pandora-Open](https://pandoraopen.io/wp-content/uploads/2025/12/Pandora-Open-mini.png)

## Pandora Open: Open Source Monitoring Without Limits

Pandora Open is a high-performance, open-source monitoring ecosystem designed for administrators who need total control over their infrastructure. Since its inception in 2004, it has remained committed to a single goal: providing a modular, scalable, and truly open platform to monitor everything—from low-level hardware to complex network topologies.

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

- Pure 100% opensource. No vendor lockings, no hidden agenda.
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

## How to build the Pandora Open Windows Agent

- Install GNU Make.
- Install the MinGW-w64 toolchain.
- Clone the repository.
- From the root of the repository, run `make agent_windows`.

## Support

For community support you can visit our forums at https://pandoraopen.io/

Pandora Open has a "commercial" solution, with different features, oriented to companies that do not want to spend time using open source solutions, but closed packaged products, with periodic updates and professional support. Its name is Pandora Open, and you can find more information about it at https://pandoraopen.io.

## Setting the Version

To update the version across all relevant files, use the `make version` command with the `VERSION` variable. This command will update the version in the following files:
- `pandora_agent/unix/pandora_agent`
- `pandora_agent/win32/pandora.cc`
- `pandora_server/lib/PandoraOpen/Config.pm`

### Example

To set the version to `1.2.3`, run:

```bash
make version VERSION=1.2.3
```
