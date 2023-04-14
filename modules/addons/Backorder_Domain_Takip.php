<?php
//Backorder Domain Takip
//Kodlayan :Gurkan Ersan
//Keykubad
if (!defined("WHMCS")) die("This file cannot be accessed directly");


function Backorder_Domain_Takip_config() {
    $configarray = array(
    "name" => "Backorder Domain Follow",
    "description" => "This login includes automatically buying when you track your expired domains and go.",
    "version" => "Final",
    "author" => "Hostgrup",
    "fields" => array(
        "backorder_apiuser" => array ("FriendlyName" => "Whmcs API User", "Type" => "text", "Size" => "25",
                              "Description" => "Enter your WHMCS API user", "Default" => "Example", ),
        "backorder_apisifre" => array ("FriendlyName" => "Whmcs Api Password", "Type" => "password", "Size" => "25",
                              "Description" => "WHMCS Api Secret", ),
		"backorder_urunid" => array ("FriendlyName" => "WHMCS Product ID", "Type" => "text", "Size" => "25",
                              "Description" => "The backorder product id number you have opened from the Whmcs panel", "Default" => "143", ),
    ));
    return $configarray;
}

function Backorder_Domain_Takip_output($vars) {
 $_lang = $vars['_lang'];	
	
		echo '<div class="alert alert-info" role="alert">'.$_lang['manueldesc'].'</div>
<form method="post">
<div class="row">
                        <div class="col-md-12">
						<div class="form-group">
						

 
 </div>
 
 </div>
 <p><center><button type="submit" name="baslat" class="btn btn-primary">'.$_lang['btnstart'].'</button></center></p>
  </form>
 </div>
 </div>';
	if (isset($_POST['baslat'])){

    $modulelink = $vars['modulelink'];
    $version = $vars['version'];
    $backorder_apiuser = $vars['backorder_apiuser'];
    $backorder_apisifre = $vars['backorder_apisifre'];
    $backorder_urunid = $vars['backorder_urunid'];

	$url	= mysql_fetch_array(mysql_query("SELECT * FROM  tblconfiguration where setting='Domain'"));
	$domain_cek=$url['value'];
  

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, ''.$domain_cek.'/includes/api.php');
		curl_setopt($ch, CURLOPT_POST, 1);



$urunler = mysql_query("SELECT * FROM tblhosting WHERE packageid=".$backorder_urunid." AND domainstatus='Active'");
while ($row_siparis = mysql_fetch_array($urunler)){
		$alanadi	= $row_siparis["domain"];


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
		$domainkontrolu=$jsonData['whois'];

			if(!stristr($sorguladomain,"unavailable")){
				
					$alanadibul = mysql_query("SELECT * FROM tbldomains WHERE domain LIKE '%$alanadi%'");
					$sonucdomain=  mysql_fetch_array($alanadibul);	
					$alanadiid=$sonucdomain["id"];
					$alanadidurum=$sonucdomain["status"];
					
					if($alanadidurum!="Active"){
				
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
					if($kayitedildi=="success"){ $kayitedildi="<br>".$_lang['success_msg']."<br><br>"; }
					if($mesaj==""){ $mesaj="<b>".$_lang['default_success_msg']."</b>"; }
					$domainler=	$alanadi.$kayitedildi.$mesaj;
		
					curl_setopt($ch, CURLOPT_POSTFIELDS,
						http_build_query(
							array(
								'action' => 'SendAdminEmail',
								// See https://developers.whmcs.com/api/authentication
								'username' => $backorder_apiuser,
								'password' => $backorder_apisifre,
								'messagename' => 'backorder',
								'custommessage' => $domainler,
								'customsubject' =>$_lang['customsubject'],
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




curl_close($ch);
$sure_bitimi = microtime(true);
$sure = $sure_bitimi - $sure_baslangici;
echo "<br>".$_lang['customsubject'].": $sure.\n";
 
echo $_lang['memory_usage'].': ',round(memory_get_peak_usage()/1048576, 2), 'MB';
}
}

?>