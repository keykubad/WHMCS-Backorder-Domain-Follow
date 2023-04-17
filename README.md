### [WHMCS-Backorder-Domain-Follow](https://github.com/keykubad/WHMCS-Backorder-Domain-Follow)

After the post-installation settings are made, add the following code to the Cron Jobs jobs. In the code part;
Change the part that says ea-php71 according to your php version.

If you create an empty mail template named "backorder" from the General Email Template Section, the mail information system will work without any problems.


    ea-php71 -q /home/xxxx/public_html/modules/addons/Backorder_Domain_Tracking/cron.php

> for Plesk

    /opt/plesk/php/7.4/bin/php -q /var/www/vhosts/ Ekonomihost.net/httpdocs/crons/cron.php all --force -v
    
Activate the add-on from the Addon Module section.
Create full authorization api

https://docs.whmcs.com/Manage_API_Credentials
ident and secret


Create a product named Backorder from the Product & Services section and enter the id number in the plugin.

For API access, enter your server IP address in the General setting security section, API IP Access Restriction.

My Website: www.keykubad.com
