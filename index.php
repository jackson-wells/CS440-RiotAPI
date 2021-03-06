<?php
session_start();
$submitError = $key = $loginResult = "";

function login() {
	$key = $_POST["key"];
	$url = "https://na1.api.riotgames.com/lol/summoner/v4/summoners/by-name/RiotSchmick?api_key=$key";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	$data = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if($httpcode == "200") {
        $_SESSION['key'] = "$key";
        return 1;
    }
    else {
        return 0;
    }
}

function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (empty($_POST["key"]) || $_POST["key"] == "") {
                $submitError = "* Key is required";
        }
        else {
                $password = test_input($_POST["pass"]);
                if(login() == 1) {
			$loginResult = "<h2>Successful Login!</h2><hr>";
            
		}
		else {
			$loginResult = "<h2>Invalid Key!</h2><hr>";
		}
        }
}

if(!isset($_SESSION)) { 
	session_start();
}



require("header.php");
print"
<head>
	<title>Salty Lanes | Home</title>
</head>
<body>
    <div class='home_container'>";
if(!isset($_SESSION["key"])) {
        print " 
        <center><div>Please login to use this site.</div></center>
    ";
}
else {
print "
        <div class='summoner_search_container'>
			<form class='summoner_search' method='post' action='stats.php'>
                <label class='dev_key'>Summoner:
                    <input type='text' name='summoner'/>
                </label>
                <button type='submit' name='submit' value='Get Statistics' id='submit'>Get Statistics</button>
            </form>
        </div>
";
}
print "
    </div>
    ";
if(isset($_SESSION["key"])) {
    print "
        <div class='logout_container'>
            <a href='logout.php'>Logout</a>
            <br>
            This website was developed for CS440 Winter Term 2019 at Oregon State University. 
        </div>
        ";
}
print "
</body>
    ";
?>
