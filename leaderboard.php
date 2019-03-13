<?php
//start the session as we need it to be running anyways
session_start();
//if there is no key, redirect back to home
if(!isset($_SESSION["key"])) {
    header('Location: index.php');
}

//getMasters requests the api for all the summoners in the Challengers League (key comes from index.php)
function getChallengers() {
	$key = $_SESSION["key"];
	$url = "https://na1.api.riotgames.com/lol/league/v4/challengerleagues/by-queue/RANKED_SOLO_5x5?api_key=$key";
	$crl = curl_init();
	curl_setopt($crl, CURLOPT_URL, $url);
	curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($crl);
    $leaders = json_decode($data, true);
	$httpcode = curl_getinfo($crl, CURLINFO_HTTP_CODE);
	curl_close($crl);

	if($httpcode == "200") {
			return $leaders;
		}
		else {
            echo '<div> Error code: '.$httpcode .'</div>';
			return 0;
		}
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
                    $return_array = getChallengers();
                    if($return_array) {
                        echo '<thead>
                                    <th> Summoner </th>
                                    <th> League Points </th>
                                    <th> Wins </th>
                                    <th> Losses </th>
                                    <th> Win Rate </th>
                                </thead>
                                <tbody>';
                        //For each element in the array assign key and value, access only the sub array with key entries
                        foreach($return_array as $array_key => $array_value) {
                            if($array_key == 'entries') {
                                //loop through each sub array (1 array = 1 summoner) 
                                foreach($array_value as $summoner_array){
                                    //loop through the summoner array and get the name, lp, wins,  and losses
                                    foreach($summoner_array as $summoner_key => $summoner_value){
                                        switch($summoner_key) {
                                            case 'summonerName':
                                                $s_name = $summoner_value;
                                                break;
                                            case 'leaguePoints':
                                                $s_lp = $summoner_value;
                                                break;
                                            case 'wins':
                                                $s_wins = $summoner_value;
                                                break;
                                            case 'losses':
                                                $s_losses = $summoner_value;
                                                break;
                                        }
                                    }
                                    echo '<tr class="single_challenger">';
                                        echo '<td>'.$s_name.'</td>';
                                        echo '<td>'.$s_lp.'</td>';
                                        echo '<td>'.$s_wins.'</td>';
                                        echo '<td>'.$s_losses.'</td>';
                                        echo '<td>'.bcdiv((($s_wins / ($s_wins + $s_losses))*100.00), 1, 2).'%</td>';
                                    echo '</tr>';
                                }
                            }
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
