### [WHMCS-Backorder-Domain-Follow](https://github.com/keykubad/WHMCS-Backorder-Domain-Follow)

After the post-installation settings are made, add the following code to the Cron Jobs jobs. In the code part;
Change the part that says ea-php71 according to your php version.

If you create an empty mail template named "backorder" from the General Email Template Section, the mail information system will work without any problems.


    ea-php71 -q /home/xxxx/public_html/modules/addons/Backorder_Domain_Tracking/cron.php

> for Plesk

    /opt/plesk/php/7.4/bin/php -q /var/www/vhosts/ Ekonomihost.net/httpdocs/crons/cron.php all --force -v

My Website: www.keykubad.com
