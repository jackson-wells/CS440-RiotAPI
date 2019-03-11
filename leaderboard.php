<?php
//start the session as we need it to be running anyways
session_start();
//if there is no key, redirect back to home
if(!isset($_SESSION["key"])) {
    header('Location: index.php');
}

//getMasters requests the api for all the summoners in the Grand Masters League (key comes from index.php)
function getMasters() {
	$key = $_SESSION["key"];
	$url = "https://na1.api.riotgames.com/lol/league/v4/grandmasterleagues/by-queue/RANKED_SOLO_5x5?api_key=$key";
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
    <div class='stats_container'>
    <table class='sortable' align='center'>
        <?php
            $return_array = getMasters();
            if($return_array) {
                echo '<thead>
                            <th> Summoner </th>
                            <th> League Points </th>
                            <th> Wins </th>
                            <th> Losses </th>
                        </thead>
                        <tbody>';
                //For each element in the array assign key and value, access only the sub array with key entries
                foreach($return_array as $array_key => $array_value) {
                    if($array_key == 'entries') {
                        //loop through each sub array (1 array = 1 summoner) 
                        foreach($array_value as $summoner_array){
                            echo '<tr>';
                            //loop through the summoner array and get the name, lp, wins,  and losses
                            foreach($summoner_array as $summoner_key => $summoner_value){
                                switch($summoner_key) {
                                    case 'summonerName':
                                        echo '<td>'.$summoner_value.'</td>';
                                        break;
                                    case 'leaguePoints':
                                        echo '<td>'.$summoner_value.'</td>';
                                        break;
                                    case 'wins':
                                        echo '<td>'.$summoner_value.'</td>';
                                        break;
                                    case 'losses':
                                        echo '<td>'.$summoner_value.'</td>';
                                        break;
                                }
                            }
                            echo '</tr>';
                        }
                    }
                }
                echo '</tbody>';
            }
        ?>
    </table>
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
