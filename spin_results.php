<!DOCTYPE HTML>  
<html>
<head>
<style>
.error {color: #FF0000;}
</style>
</head>
<body>  

<?php
$playerErr = $hashErr = $betErr = $wonErr = "";
$player = $hash = $bet = $won = "";

// QC form entries
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["player"])) {
    $playerErr = "Player ID is required";
  } else {
    $player = qc($_POST["player"]);
    if (!preg_match("/^[a-zA-Z0-9]*$/",$player)) {
      $playerErr = "Only letters and numbers allowed";
    }
  }

  if (empty($_POST["hash"])) {
    $hashErr = "Password is required";
  } else {
    $hash = qc($_POST["hash"]);
  }
    
  if (empty($_POST["won"])) {
    $wonErr = "Coins won is required";
  } else {
    $won = qc($_POST["won"]);
    if (!preg_match("/^[0-9]*$/",$won)) {
      $wonErr = "Only numbers allowed";
    }
  }

  if (empty($_POST["bet"])) {
    $betErr = "Coins bet is required";
  } else {
    $bet = qc($_POST["bet"]);
    if (!preg_match("/^[0-9]*$/",$bet)) {
      $betErr = "Only numbers allowed";
    }
  }
}
// Connect to MySQL database where player data is stored
// and update player data based on form entries
list($host,$user,$pass,$db) = sql_settings();
$conn = new mysqli($host,$user,$pass,$db);
if ($conn->connect_errno) {
  die("Connection failed: " . $conn->connect_error);
} else {
  $sql_player = "SELECT id,credits,spins,avg_return FROM player_stats WHERE id=$player";
  $res_player = $conn->query($sql_player);
  if ($res_player->num_rows > 0) {
    $sql_fetch = $res_player->fetch_array();
    $total_return = $sql_fetch['avg_return'] * $sql_fetch['spins'];
    $net = $won - $bet;
    $sql_update = "UPDATE player_stats SET credits = credits + $net,
                   spins = spins + 1, avg_return = ($total_return + $net)/spins
                   WHERE id=$player";
    $res_update = $conn->query($sql_update);
    $sql_confirm = "SELECT id,name,credits,spins,avg_return FROM 
                    player_stats WHERE id=$player";
    $res_sql_confirm = $conn->query($sql_confirm);
    $confirm_array = $res_sql_confirm->fetch_array();
    $conn->close();
  }
}
//Remove excess characters from form entries
function qc($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
//User must configure to sql server settings
function sql_settings() {
  $servername = "";
  $username = "";
  $password = "";
  $dbname = "";
  $settings = array($servername,$username,$password,$dbname);
  return $settings;
}
?>

<h2>Player Update</h2>
<p><span class="error">* required field.</span></p>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
  Player ID: <input type="text" name="player">
  <span class="error">* <?php echo $playerErr;?></span>
  <br><br>
  Password: <input type="password" name="hash">
  <span class="error">* <?php echo $hashErr;?></span>
  <br><br>
  Coins Bet: <input type="number" name="bet">
  <span class="error">* <?php echo $betErr;?></span>
  <br><br>
  Coins Won: <input type="number" name="won">
  <span class="error">* <?php echo $wonErr;?></span>
  <br><br>
  <input type="submit" name="submit" value="Submit">  
</form>

<?php
//Print updated stats after form is submitted
echo "<h2>Player Stats:</h2>";
if (!$confirm_array['id']) {
  echo "Player ID $player does not exist. Please update
        and try again.";
} else {
  echo "<p>Player ID: ", $confirm_array['id'], " </p>";
  echo "<p>Name: ", $confirm_array['name'], " </p>";
  echo "<p>Credits: ", $confirm_array['credits'], " </p>";
  echo "<p>Lifetime Spins: ", $confirm_array['spins'], " </p>";
  echo "<p>Lifetime Average Return: ", $confirm_array['avg_return'], " </p>";
}
?>

</body>
</html>
