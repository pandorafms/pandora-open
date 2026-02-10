![logo Pandora-Open](https://pandoraopen.io/wp-content/uploads/2025/12/Pandora-Open-mini.png)

With more than 50,000 customer installations across the five continents, Pandora Open is an out-of-the-box monitoring solution: profitable and scalable, covering most infrastructure deployment options.

Pandora Open gives you the agility to find and solve problems quickly, scaling them so they can be derived from any source, on-premise, multi cloud or both of them mixed. Now you have that capability across your entire IT stack and analytics to find any problem, even the ones that are hard to find.

## What is Pandora Open?

Pandora Open is an open source monitoring application whose origin dates back to 2004. It integrates in the same application the monitoring of different infrastructure elements: networks, applications, servers, web, and other specific data sources such as logs, WMI, Netflow or SNMP traps.

It allows you to supervise systems and applications of all types, through remote monitoring or with software agents installed on the equipment to be monitored.

Pandora Open monitors your hardware, software, your multilayer system and, of course, your operating system. Pandora Open can detect if a network interface is down or the movement of the market value of any new NASDAQ technology. If desired, Pandora Open can send an SMS message when your system or application fails or when the value of Tesla's stock drops below \$180. Pandora Open will adapt to your systems and requirements, because it has been designed to be open, modular, multiplatform and easy to customize.

Pandora Open can be deployed over any OS, with specific agents for each platform. There are agents for Windows (2000, XP, 2003), GNU/Linux, Solaris, HP-UX, BSD, AIX, IPSO and OpenWRT. Pandora Open not only gathers information through its agents, but it can also monitor any hardware system with TCP/IP connectivity, such as load balancing systems, routers, switches or printers, through SNMP and TCP/ICMP checks.

Pandora Open is a monitoring tool that not only measures if a parameter is right or wrong. Pandora Open can quantify the state (right or wrong), or store a value (numeric or alphanumeric) for months if necessary. Pandora Open can measure performances, compare values among different systems and set alarms over thresholds. Pandora Open works against a MySQL Database so it can generate reports, statistics, SLA and measure anything.

## Main features

- Network monitoring
- Server monitoring (using agents for Windows, Linux, Mac, BSD and legacy Unix)
- SSH/WMI remote monitorin.
- Graphical reporting, based on SQL backend
- SLA, and ITIL KPI metrics on reporting
- Status & Performance monitoring
- GIS tracking and viewing
- Inventory management (Local and remote)
- Netflow support
- Centralized log collection.
- User defined visual console screens and Dashboards WYSIWYG
- Very high capacity (Thousands of devices)
- Multitenant, several levels of ACL management.


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

Pandora Open has a "commercial" solution, with different features, oriented to companies that do not want to spend time using open source solutions, but closed packaged products, with periodic updates and professional support. Its name is Pandora Open Enterprise, and you can find more information about it at https://pandoraopen.io.

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
