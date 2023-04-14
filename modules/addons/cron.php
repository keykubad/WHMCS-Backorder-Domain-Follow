<?php
if(isset($_SERVER['HTTP_USER_AGENT']))
    die('Direk calismaz :)');
define('DS', DIRECTORY_SEPARATOR); 
define('WHMCS_MAIN_DIR', substr(dirname(__FILE__),0, strpos(dirname(__FILE__),'modules'.DS.'addons')));  

if(file_exists(WHMCS_MAIN_DIR.DS.'init.php')) // 
{
    require_once WHMCS_MAIN_DIR.DS.'init.php';
}
else // Older than 5.2.2
{
    require_once WHMCS_MAIN_DIR.DS."configuration.php";
    require_once WHMCS_MAIN_DIR.DS."includes".DS."functions.php";
}

$backorder_apiuser_c	= mysql_fetch_array(mysql_query("SELECT * FROM  tbladdonmodules where setting='backorder_apiuser'"));
	$backorder_apiuser=$backorder_apiuser_c['value'];
	$backorder_apisifre_c	= mysql_fetch_array(mysql_query("SELECT * FROM  tbladdonmodules where setting='backorder_apisifre'"));
	$backorder_apisifre=$backorder_apisifre_c['value'];
	$backorder_urunid_c	= mysql_fetch_array(mysql_query("SELECT * FROM  tbladdonmodules where setting='backorder_urunid'"));
	$backorder_urunid = $backorder_urunid_c['value'];


 
 
	//config kısmında yazılan degerleri alıyoruz
	$url	= mysql_fetch_array(mysql_query("SELECT * FROM  tblconfiguration where setting='Domain'"));
	$domain_cek=$url['value'];


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, ''.$domain_cek.'includes/api.php');
		curl_setopt($ch, CURLOPT_POST, 1);

//---- burada sabit girilmesi gereken ayarlarımız var

//order numaraları çekiyoruz ve backorder yazanları alıyoruz.
$urunler = mysql_query("SELECT * FROM tblhosting WHERE packageid=".$backorder_urunid." AND domainstatus='Active'");
while ($row_siparis = mysql_fetch_array($urunler)){
		$alanadi	= $row_siparis["domain"];

		//whois sorgusuna backorder yapılan alan adlarını veriyoruz sonuca göre kontrol sağlıyoruz.
curl_setopt($ch, CURLOPT_POSTFIELDS,
    http_build_query(
        array(
            'action' => 'DomainWhois',
            'username' => $backorder_apiuser,
            'password' => $backorder_apisifre,
            'domain' => $alanadi,
            'responsetype' => 'json',
        )
    )
);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);

$jsonData = json_decode($response, true);
		$sorguladomain=$jsonData['status'];
		$domainkontrolu=$jsonData['whois'];//whois sorgusuna buradan bakabiliriz
		//durumuna bakıp eğer alınabilir ise kayıt ediyoruz
			if(!stristr($sorguladomain,"unavailable")){
				
					$alanadibul = mysql_query("SELECT * FROM tbldomains WHERE domain LIKE '%$alanadi%'");
					$sonucdomain=  mysql_fetch_array($alanadibul);	
					$alanadiid=$sonucdomain["id"];
					$alanadidurum=$sonucdomain["status"];
					//alan adı durumu aktif değilse kayıt tamamlanmadı ise buraya sokup kayıt yapıp mail attırıyoruz.
					if($alanadidurum!="Active"){
					//domain kaydı için apiden istek yapıyoruz
					curl_setopt($ch, CURLOPT_POSTFIELDS,
						http_build_query(
							array(
								'action' => 'DomainRegister',
								// See https://developers.whmcs.com/api/authentication
								'username' => $backorder_apiuser,
								'password' => $backorder_apisifre,
								'domainid' => $alanadiid,
								'responsetype' => 'json',
							)
						)
					);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					$kayit = curl_exec($ch);
					$domainkaydi = json_decode($kayit, true);
					$kayitedildi	=$domainkaydi['result'];
					$mesaj	=	$domainkaydi['message'];
					if($kayitedildi=="success"){ $kayitedildi="<br>Alan Adı Backorder Kaydı Tamamlandı<br><br>"; }
					if($mesaj==""){ $mesaj="<b>Alan Adı Durumu Aktif Duruma Alındı. Next Due Date(Bir Sonraki Ödeme) Güncelleyiniz.</b>"; }
					$domainler=	$alanadi.$kayitedildi.$mesaj;
					//eğer domainler kontrolu geçtiyse kayıtları mail atıyoruz
					curl_setopt($ch, CURLOPT_POSTFIELDS,
						http_build_query(
							array(
								'action' => 'SendAdminEmail',
								// See https://developers.whmcs.com/api/authentication
								'username' => $backorder_apiuser,
								'password' => $backorder_apisifre,
								'messagename' => 'backorder',
								'custommessage' => $domainler,
								'customsubject' =>'Backorder Domain Kayıt Durumları',
								'responsetype' => 'json',
							)
						)
					);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					$mailat = curl_exec($ch);
					$mailgonder = json_decode($mailat, true);
						
					}
						
											
			}




}


//döngüyü bitriyoruz


?>