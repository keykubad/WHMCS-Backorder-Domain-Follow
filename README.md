### [WHMCS-Backorder-Domain-Follow](https://github.com/keykubad/WHMCS-Backorder-Domain-Follow)

Kurulum sonrası ayarlar yapıldıktan sonra Cron Jobs işlerine aşağıdaki kodu ekleyiniz. Kod kısmında;
ea-php71 yazan kısım php surumunuze göre değişiniz. 

Genel Email Template Kısmından "backorder" adında boş bir mail template oluşturursanız mail ile bilgi sistemi problemsiz çalışacaktır.


    ea-php71 -q /home/xxxx/public_html/modules/addons/Backorder_Domain_Takip/cron.php

> Plesk için

    /opt/plesk/php/7.4/bin/php -q /var/www/vhosts/ekonomikhost.net/httpdocs/crons/cron.php all --force -v

Web Sitem : www.keykubad.com
