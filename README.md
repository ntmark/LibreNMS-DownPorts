# LibreNMS plugin to show port Down/Up time
Search by device Name, and number of days port is in state (either up or down)
Options for a few other things, like excluding shut ports

# Barely working prototype.
2024/02/14
ToDo's
1. add selector to select either down or up ports (radio button, default to down) - 2024/02/15 done.
2. remove other types of ports Ap1/0/1, Trunk ports ??? - 2024/02/15 removed Te/Twe/Ap
3. query by devices.hostname or devices.sysName - 2024/02/15 added.
4. sql add % before hostname - 2024/02/15 added.
5. fix if "0" is entered in port state, page crashes for some reason.
6. Default select Down port - made radio instead of selector 2024/03/01
7. Default check SHUTDOWN ports 2024/03/01
8. 
