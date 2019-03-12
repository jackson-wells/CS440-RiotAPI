<?php
//start the session as we need it to be running anyways
session_start();
$name = $_POST["summoner"];

//if there is no key, redirect back to home
if(!isset($_SESSION["key"])) {
    header('Location: index.php');
}

//getSummoner requests the api for information on the provided summoner name (comes from index.php)
function getSummoner() {
	$key = $_SESSION["key"];
    $name = $_POST["summoner"];
	$url = "https://na1.api.riotgames.com/lol/summoner/v4/summoners/by-name/$name?api_key=$key";
	$crl = curl_init();
	curl_setopt($crl, CURLOPT_URL, $url);
	curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($crl);
    $summoner = json_decode($data, true);
	$httpcode = curl_getinfo($crl, CURLINFO_HTTP_CODE);
	curl_close($crl);

	if($httpcode == "200") {
			return $summoner;
		}
		else {
			return 0;
		}
}

//Get Rank retrieves the summoners ranked information 
// I believe you have to encode the summoner name in html url encoding for this to work, but im not sure
//function getRank() {
    //$key = $_SESSION["key"];
    //$name = $_POST["summoner"];
    //$url = "";
    //$crl = curl_init();
    //curl_setopt($crl, CURLOPT_URL, $url);
    //curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
    //$data = curl_exec($crl);
    //$summoner = json_decode($data, true);
    //$httpcode = curl_getinfo($crl, CURLINFO_HTTP_CODE);
    //curl_close($crl);

    //if($httpcode == "200") {
            //return $summoner;
        //}
        //else {
            //return 0;
        //}
//}

//include header for navigation
require 'header.php';
?>
<!-- This page is simple, call getSummoner  and print results --> 
<head>
    <title>Salty Lanes | <?php echo $name ?> </title>
</head>
<body>
    <div class='stats_container'>
        <div class='summoner_overview'>
        <?php
        $summoner = getSummoner();
        if($summoner) {
            echo '<div class="summoner_title">';
            foreach($summoner as $summoner_key => $summoner_value) {
                switch($summoner_key) {
                    case 'name':
                        echo '<div class="summoner_icon"> temp box </div>';
                        echo '<div class="summoner_name">'.$summoner_value.'</div>';
                        break;
                    case 'summonerLevel':
                        echo '<div class="summoner_level"> Level: '.$summoner_value.'</div>';
                        break;
                }
            }
            echo '</div>';
        }
        ?>
        </div>
    </div>
<?php
if(isset($_SESSION["key"])) {
    print "
        <div class='logout_container'>
            <a href='logout.php'>Logout</a>
            <br>
            This website was developed for CS440 Winter Term 2019 at Oregon State University. 
        </div>
        ";
}?>
</body>
