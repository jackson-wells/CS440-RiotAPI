<?php
print "
<head>
    <link rel='stylesheet' type='text/css' href='./lanes.css'/>
    <script src='Scripts/sorttable.js' type='text/javascript'></script>
</head>
<body>
<div class='table_header'>
    <span class='table_span'>
        <img class='challenger_emblem' src='Images/Emblem_Challenger.png'>";

if ($_SESSION['key']) {

    print " <h1> Logged In </h1>";

} else {
    
    print " <a href='login.php'><h1>Log In</h1></a>";

}

print "
        <a href='index.php'><h1>Salty Lanes</h1></a>
        <a href='leaderboard.php'><h1>Leaderboards</h1></a>
    </span>
</div>";
?>
