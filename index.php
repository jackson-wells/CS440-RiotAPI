<?php

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
            	if(!isset($_SESSION)) {
        		session_start();
			$_SESSION['key'] = "$key";
			return 1;
		}
		else {
			return 0;
		}
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
<body style='background-color:grey;'>
<table style='width:80%;border-collapse:collapse;background-color:rgb(211,211,211);border-radius:10px;-moz-border-radius:10px;-webkit-border-radius:10px;' align='center'>";
if(!isset($_SESSION["key"])) {
        print " 
		<tr>
	                <td>
	                        <br>
	                                <center>$loginResult</center>
	                        <br>
	                </td>
	        </tr>
		<tr>
        	        <td style='text-align:center;'>
				<form method='post' action= "; echo htmlspecialchars($_SERVER["PHP_SELF"]); print">
                        		<label>Developer Key:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                        		<input style='background-color:#FFFFFF' type='password' name='key'/>
                        		<span class='error'>$submitError</span>
                        		<br><br>
                        		<input type='submit' name='submit' value='Login!' id='submit'>
               			</form>
                	</td>
        	</tr>";
}
else {
print "
	<tr>
                <td>
                        <br>
                                <center>$loginResult</center>
                        <br>
                </td>
        </tr>
	<tr>
		<td style='text-align:center;'>
			<form method='post' action='stats.php'>
                        	<br><br>
                              	<label>Summoner:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                              	<input style='background-color:#FFFFFF' type='text' name='summoner'/>
                           	<br><br>
                               	<input type='submit' name='submit' value='Get Statistics' id='submit'>
                   	</form>
		</td>
	</tr>
	<tr>
                <td>
                        <br>
                                <hr>
                        <br>
                </td>
        </tr>
	<tr>
		<td>
			<br>
			<center><a style='text-decoration:none' href='logout.php' ><h2>Logout</h2></a></center>
			<br>
		</td>
	</tr>		
</table>
</body>
";
}

?>

