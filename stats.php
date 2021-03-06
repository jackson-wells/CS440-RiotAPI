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
function getSummoner($pdo) {
    $query = "SELECT name, profileIcon, summonerLevel, accountId FROM Summoners WHERE name = :summonerName";
    $statement = $pdo->prepare($query);
    $statement->bindValue(":summonerName", $_POST["summoner"]);
    $statement->execute();
    $results = $statement->fetchAll();
    if(count($results) > 0)
    	return $results[0];

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

function addMatchToDB($pdo, $gameId)
{
	$key = $_SESSION["key"];
	$url = "https://na1.api.riotgames.com/lol/match/v4/matches/$gameId?api_key=$key";
	$crl = curl_init();
	curl_setopt($crl, CURLOPT_URL, $url);
	curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($crl);
	$httpcode = curl_getinfo($crl, CURLINFO_HTTP_CODE);
	curl_close($crl);

	if($httpcode != "200")
		return;

	$ch = curl_init("https://web.engr.oregonstate.edu/~hammockt/cs440/final_project/match.php");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$results = curl_exec($ch);
	curl_close($ch);
}

function getMatchList($pdo, $acct_id) {
    $key = $_SESSION["key"];
	$url = "https://na1.api.riotgames.com/lol/match/v4/matchlists/by-account/$acct_id?api_key=$key";
	$crl = curl_init();
	curl_setopt($crl, CURLOPT_URL, $url);
	curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($crl);
    $matches = json_decode($data, true);
	$httpcode = curl_getinfo($crl, CURLINFO_HTTP_CODE);
	curl_close($crl);

	if($httpcode != "200")
		return 0;
	
	$query = "SELECT gameId FROM Participants WHERE accountId = ? ORDER BY gameId DESC LIMIT 10";
	$statement = $pdo->prepare($query);
	$statement->bindValue(1, $acct_id);
	$statement->execute();
	$dbMatches = $statement->fetchAll();
	$dbGameIds = array_column($dbMatches, "gameId");

	for($i = 0; $i < count($matches["matches"]) && $i < 10; $i++)
	{
		$match = $matches["matches"][$i];
		if(!in_array($match["gameId"], $dbGameIds))
			addMatchToDB($pdo, $match["gameId"]);
	}

	$query = "SELECT Participants.*, gameDuration FROM Participants INNER JOIN Matches ON Participants.gameId = Matches.gameId WHERE accountId = ? ORDER BY gameId DESC LIMIT 10";
	$statement = $pdo->prepare($query);
	$statement->bindValue(1, $acct_id);
	$statement->execute();
	$results = $statement->fetchAll();
	return $results;
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
            return $rank[0];
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
        $pdo = new PDO(getDBDsn(), getDBUser(), getDBPassword());
        $summoner = getSummoner($pdo);
		$rank = getRank($summoner["id"]);

        if($summoner) {
            echo '<div class="summoner_title">';
                echo "<img src='https://ddragon.leagueoflegends.com/cdn/9.5.1/img/profileicon/${summoner["profileIconId"]}.png' class='summoner_icon'/>";
                echo "<div class='summoner_name'>${summoner["name"]}</div>";
                echo "<div class='summoner_level'> Level: ${summoner["summonerLevel"]}</div>";
				echo "<div> Tier: ${rank["tier"]} ${rank["rank"]}  </div>";
				echo "<div> League Points: ${rank["leaguePoints"]} </div>";
				echo "<div> Wins: ${rank["wins"]} </div>";
				echo "<div> Losses: ${rank["losses"]} </div>";
            echo '</div>';

            $matches = getMatchList($pdo, $summoner["accountId"]);
            
            echo '<br><div class="summoner_matches">';
            foreach($matches as $match)
            {
            	echo "<div class='match_list'>";
            		echo "<div>Match ID: ${match["gameId"]}</div>";
            		if ($match["win"] == 1) {
		        		echo "<div>Victory!</div>";
            		} else {
            			echo "<div>Defeat</div>";
            		}

		        	echo "<div>KDA: ${match["kills"]}/${match["deaths"]}/${match["assists"]}</div>";
		        	echo "<div class='summoner_lane'>Lane: ${match["lane"]}</div>";
		        	echo "<div>Role: ${match["role"]}</div>";
		        	echo "<div>Game Duration: ".gmdate("H:i:s", $match["gameDuration"])."</div>";
            	echo "</div>";
            	echo "<br>";
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
