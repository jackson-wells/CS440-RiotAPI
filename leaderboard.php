<?php
include_once 'PHPScripts/database.php';

//start the session as we need it to be running anyways
session_start();
//if there is no key, redirect back to home
if(!isset($_SESSION["key"])) {
    header('Location: index.php');
}

//getMasters requests the api for all the summoners in the Challengers League (key comes from index.php)
function getChallengers() {
	$pdo = new PDO(getDBDsn(), getDBUser(), getDBPassword());

	$query = "SELECT summonerName AS name, leaguePoints, wins, losses, profileIconId
	         FROM LeagueItem
	         LEFT JOIN Summoners
	         	ON LeagueItem.summonerName = Summoners.name
	         WHERE leagueId = '974b70e3-28eb-3b60-9e9f-82a8efa19f10'
	         ORDER BY leaguePoints DESC";
	$statement = $pdo->prepare($query);
	$statement->execute();
	$challengers = $statement->fetchAll();

	for($i = 0; $i < count($challengers); $i++)
	{
		if($challengers[$i]["profileIconId"] == null)
		{
			$key = $_SESSION["key"];
			$summonerName = rawurlencode($challengers[$i]["name"]);
			$crl = curl_init("https://na1.api.riotgames.com/lol/summoner/v4/summoners/by-name/$summonerName?api_key=$key");
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
			$data = curl_exec($crl);
			$summonerFromApi = json_decode($data, true);
			$httpcode = curl_getinfo($crl, CURLINFO_HTTP_CODE);
			curl_close($crl);

			if($httpcode == "200")
			{
				$challengers[$i]["profileIconId"] = $summonerFromApi["profileIconId"];

				$ch = curl_init("https://web.engr.oregonstate.edu/~hammockt/cs440/final_project/summoner.php");
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($summonerFromApi));
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$result = curl_exec($ch);
				curl_close($ch);
			}
		}
	}

	return $challengers;
}

function calcWinRate($wins, $losses)
{
	return round($wins / ($wins + $losses) * 100.00, 1);
}


//include header for navigation
require("header.php");
?>

<head>
	<title>Salty Lanes | Leaderboards</title>
</head>
    <!-- The sortable class comes from sortable.js and is used to sort the table by its header -->
    <div class='leaderboard_wrapper'>
        <div class='leaderboard_container'>
            <table class='sortable leaderboard'>
                <?php
                    $challengers = getChallengers();
                    if($challengers) {
                        echo '<thead>
                                    <th> Rank </th>
                                    <th class="sorttable_nosort"></th>
                                    <th class="sorttable_nosort"> Summoner </th>
                                    <th> League Points </th>
                                    <th> Wins </th>
                                    <th> Losses </th>
                                    <th> Win Rate </th>
                                </thead>
                                <tbody>';
                        $i = 0;
                        foreach($challengers as $summoner)
                        {
                            $i += 1;
                        	echo '<tr class="single_challenger">';
                                echo "<td><div>${i}</div></td>";
                            echo "
                                        <td class='ci_wrapper'>
                                            <img src='http://ddragon.leagueoflegends.com/cdn/9.5.1/img/profileicon/${summoner["profileIconId"]}.png' class='summoner_icon'/>
                                        </td>
                                <td class='summoner_n_icon'>
                                        <div class='cs_wrapper'>
                                            <strong hidden>${summoner["name"]} </strong>
                                            <form class='leaderboard_name_form' method='post' action='stats.php'>
                                                <input hidden type='text' name='summoner' value='${summoner["name"]}'/>
                                                <button type='submit' name='submit' id='submit'>${summoner["name"]}</button>
                                            </form>
                                        </div>
                                    </td>";
                                echo "<td><div>${summoner["leaguePoints"]}</div></td>";
                                echo "<td><div>${summoner["wins"]}</div></td>";
                                echo "<td><div>${summoner["losses"]}</div></td>";
                                echo "<td><div>".calcWinRate($summoner["wins"], $summoner["losses"])."%</div></td>";
                            echo '</tr>';
                        }
                        echo '</tbody>';
                    }
                    else {
                        echo '<h1>Error retrieving NA Challengers</h1>';
                    }
                ?>
            </table>
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
