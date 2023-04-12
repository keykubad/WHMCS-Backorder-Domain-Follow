<?php
//Backorder Domain Takip
//Kodlayan :Gurkan Ersan
//Keykubad
if (!defined("WHMCS")) die("This file cannot be accessed directly");


function Backorder_Domain_Takip_config() {
    $configarray = array(
    "name" => "Backorder Domain Takip Yazılımı",
    "description" => "Bu eklenti süresi geçmiş alan adlarınızı takip edip düştüğünde otomatik satın almaktadır.",
    "version" => "Final",
    "author" => "Hostgrup",
    "fields" => array(
        "backorder_apiuser" => array ("FriendlyName" => "Whmcs Api User", "Type" => "text", "Size" => "25",
                              "Description" => "WHMCS Api kullanıcınızı yazınız", "Default" => "Example", ),
        "backorder_apisifre" => array ("FriendlyName" => "Whmcs Api Şifre", "Type" => "password", "Size" => "25",
                              "Description" => "WHMCS Api Secret yazınız.", ),
		"backorder_urunid" => array ("FriendlyName" => "WHMCS Ürün İD", "Type" => "text", "Size" => "25",
                              "Description" => "Whmcs panelden açmış olduğunuz backorder ürün id numarası", "Default" => "143", ),
    ));
    return $configarray;
}

function Backorder_Domain_Takip_output($vars) {
	
	
		echo '<div class="alert alert-info" role="alert">Bu kısım Backorder Domain Takip yazılımı el ile çalıştırmak için kullanılmaktadır.</div>
<form method="post">
<div class="row">
                        <div class="col-md-12">
						<div class="form-group">
						

 
 </div>
 
 </div>
 <p><center><button type="submit" name="baslat" class="btn btn-primary">İşlemi Başlat</button></center></p>
  </form>
 </div>
 </div>';
	if (isset($_POST['baslat'])){

    $modulelink = $vars['modulelink'];
    $version = $vars['version'];
    $backorder_apiuser = $vars['backorder_apiuser'];
    $backorder_apisifre = $vars['backorder_apisifre'];
    $backorder_urunid = $vars['backorder_urunid'];
	//config kısmında yazılan degerleri alıyoruz
	$url	= mysql_fetch_array(mysql_query("SELECT * FROM  tblconfiguration where setting='Domain'"));
	$domain_cek=$url['value'];
    $LANG = $vars['_lang'];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, ''.$domain_cek.'/includes/api.php');
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
            // See https://developers.whmcs.com/api/authentication
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

curl_close($ch);
$sure_bitimi = microtime(true);
$sure = $sure_bitimi - $sure_baslangici;
echo "<br>Bekleme süresi: $sure saniye.\n";
 
//PHP kodlarına ayrılan belleğin miktarını bayt cinsinden döndürür.
echo 'Hafıza kullanımı: ',round(memory_get_peak_usage()/1048576, 2), 'MB';
}
}

?>