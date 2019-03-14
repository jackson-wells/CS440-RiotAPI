<?php
include_once 'PHPScripts/database.php';

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
    	$name = rawurlencode($_POST["summoner"]);
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

function getMatchList($acct_id){
    	$key = $_SESSION["key"];
	$url = "https://na1.api.riotgames.com/lol/match/v4/matchlists/by-account/$acct_id?api_key=$key";
	$crl = curl_init();
	curl_setopt($crl, CURLOPT_URL, $url);
	curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
    	$data = curl_exec($crl);
    	$MatchList = json_decode($data, true);
	$httpcode = curl_getinfo($crl, CURLINFO_HTTP_CODE);
	curl_close($crl);

	if($httpcode == "200") {
		return $MatchList;
	}
	else {
		return 0;
	}
}
//Get Rank retrieves the summoners ranked information 
// I believe you have to encode the summoner name in html url encoding for this to work, but im not sure
function getRank($summonerID) {
    	$key = $_SESSION["key"];
    	$name = $_POST["summoner"];
    	$url = "https://na1.api.riotgames.com/lol/league/v4/positions/by-summoner/$summonerID?api_key=$key";
    	$crl = curl_init();
    	curl_setopt($crl, CURLOPT_URL, $url);
    	curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
    	$data = curl_exec($crl);
    	$rank = json_decode($data, true);
    	$httpcode = curl_getinfo($crl, CURLINFO_HTTP_CODE);
    	curl_close($crl);

    	if($httpcode == "200") {
		$rank = $rank[0];
            	return $rank;
        }
        else {
            	return 0;
        }
}

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
	$rank = getRank($summoner["id"]);
        if($summoner) {
            echo '<div class="summoner_title">';
                echo "<img src='http://ddragon.leagueoflegends.com/cdn/9.5.1/img/profileicon/${summoner["profileIconId"]}.png' class='summoner_icon'/>";
                echo "<div class='summoner_name'>${summoner["name"]}</div>";
                echo "<div class='summoner_level'> Level: ${summoner["summonerLevel"]}</div>";
		echo "<div> Tier: ${rank["tier"]} ${rank["rank"]}  </div>";
		echo "<div> League Points: ${rank["leaguePoints"]} </div>";
		echo "<div> Wins: ${rank["wins"]} </div>";
		echo "<div> Losses: ${rank["losses"]} </div>";
            echo '</div>';

            $matchInfo = getMatchList($summoner["accountId"]);
            
            echo '<br><div class="summoner_matches">';
            $counter = 1;
            foreach($matchInfo as $matches){
                foreach($matches as $basicInfo){
                    echo '<div class="match_list">Game: '.$counter.'';
                    foreach($basicInfo as $info => $match_values){
                        switch($info) {
                            case 'champion':
                                $champ_id = $match_values;
                                //$champ_icon = "http://ddragon.leagueoflegends.com/cdn/9.5.1/img/profileicon/$s_icon.png";
                                echo '<div class="champion_id">Champion id: '.$champ_id.'</div>';
                                break;
                            case 'lane':
                                $player_role = $match_values;
                                echo '<div class="summoner_lane">Lane: '.$player_role.'</div>';
                                break;
                        }
                    }
                    $counter++;
                    echo '</div><br>';
                    if($counter > 20){
                        break;
                    }
                    else{continue;}
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
