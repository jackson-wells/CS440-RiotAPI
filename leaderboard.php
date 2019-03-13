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
	         WHERE leagueId = '6b64089f-e9b8-308d-926b-1bca75db4d98'
	         ORDER BY leaderBoardRank";
	$statement = $pdo->prepare($query);
	$statement->execute();
	$results = $statement->fetchAll();

	return $results;
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
    <div class='leaderboard_container'>
        <div class='leaderboard_container'>
            <table class='sortable leaderboard'>
                <?php
                    $challengers = getChallengers();
                    if($challengers) {
                        echo '<thead>
                                    <th class="sorttable_nosort"> Summoner </th>
                                    <th> League Points </th>
                                    <th> Wins </th>
                                    <th> Losses </th>
                                    <th> Win Rate </th>
                                </thead>
                                <tbody>';

                        foreach($challengers as $summoner)
                        {
                        	echo '<tr class="single_challenger">';
                                echo "<td><div><strong hidden>${summoner["name"]} </strong><form class='leaderboard_name_form' method='post' action='stats.php'>
                                            <input hidden type='text' name='summoner' value='${summoner["name"]}'/>
                                            <button type='submit' name='submit' id='submit'>${summoner["name"]}</button>
                                        </form></div></td>";
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
