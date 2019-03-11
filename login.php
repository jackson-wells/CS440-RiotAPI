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
";
if(!isset($_SESSION["key"])) {
    print " 
    <div class='login_container'>
        <center>$loginResult</center>
        <div class='login_form'>
            <form method='post' action= "; echo htmlspecialchars($_SERVER["PHP_SELF"]); print">
                <h1> Login </h1>
                <label class='dev_key'>Developer Key:
                    <input class='dev_key' type='password' name='key'/>
                </label>
                <span class='error'>$submitError</span>
                <button type='submit' name='submit' id='submit'>Login</button>
            </form>
        </div>
    </div>
</body>";
}
else {
    print "
    <div class='login_container'>
        <h1> Successfully logged in! </h1>
        <center><div> Redirecting in <strong id='redirect_num'>3</strong> seconds.<div></center>
        <script type='text/javascript'>
            function changeNum(num) {
                document.getElementById('redirect_num').innerHTML = num; 
            }
            function redirect() {
                window.location='./index.php';
            }
            setTimeout('redirect()', 3000);
            setTimeout('changeNum(2)', 1000);
            setTimeout('changeNum(1)', 2000);
        </script>
    </div>
";
}
?>
