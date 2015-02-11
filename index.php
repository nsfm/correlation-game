<?php
//include the html header and stuff
require_once('header.php');

//configuration
//database connection
$dbhost = "localhost";
$dbuser = "ephemera";
$dbdb = "correlation";
$dbpassword = "ephemeral123!";

$dbconnect = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdb);
$dbsuccess = TRUE;

if (mysqli_connect_errno()) {
        $dbsuccess = FALSE;
        die('Failed to connect to database. Sorry to let you down.');
}

//process score registrations
if (isset($_GET['action'])) {
	//they might be trying to submit a score, so let's process this
	$query = "SELECT * FROM correlation.sessions WHERE (uid='{$_GET['subtoken']}')";
	$result = mysqli_query($dbconnect, $query);
	if (mysqli_num_rows($result) == 0) {
		die("Did you just try to submit a score for a nonexistant player?");
	} else {
		//the token exists
		$row = mysqli_fetch_assoc($result);
		if ($row['status'] == 'submitted') {
			die("Did you just try to submit somebody else's score?");
		} else {
			if ($row['score'] > 0) {
				//put it in, I guess
				$named = htmlentities($_POST['name'],ENT_QUOTES);
				$query = "INSERT INTO correlation.scores VALUES ('{$row['uid']}','{$named}',{$row['score']})";
				$result = mysqli_query($dbconnect, $query);
				
				echo "<b>Your score has been added to the ranking! Thank you for playing!</b><br><br>";
			} else {
				//no.
				echo "<b>Sorry, I'm not going to record a score of 0. Play again!</b><br><br>";
			}
		}
	}
}

//this checks to see if they have a token and if it's good
//if not, they get a new one
if (isset($_GET['token'])) {
	$query = "SELECT * FROM correlation.sessions WHERE (uid='{$_GET['token']}')";
	$result = mysqli_query($dbconnect, $query);
	if (mysqli_num_rows($result) == 0) {
		//mystery token?!
		echo "<br><b>You tried to use a bad play token! I've made a new one for you. Enjoy!</b><br>";
		$token = hash('md4', rand());
		$query = "INSERT INTO correlation.sessions VALUES ('{$token}','waiting','','','','',0)";
		mysqli_query($dbconnect, $query);
	} else {
		//existing token
		$token = $_GET['token'];
		$row = mysqli_fetch_assoc($result);
		switch($row['status']) {
			case 'waiting':
				echo "<br><b>You continued without checking your answers! Your game has ended.</b><br>";
			case 'done':
				$oldtoken = $row['uid'];
				if ($row['score'] > 0) {
					echo "<br><b>Your score was {$row['score']}. Enter your name here to submit it: </b><form method='POST' action='index.php?action=submit&subtoken={$oldtoken}'><input type='text' name='name'><input type='submit' value='Submit'></form><br><br>";
				} else {
					echo "<br><b>Sorry, you can't submit your score of 0!</b><br>";
				}
				//generate a new token for the new game that just started (in case they ignore their score)
				$token = hash('md4', rand());
				$query = "INSERT INTO correlation.sessions VALUES ('{$token}','waiting','','','','',0)";
				mysqli_query($dbconnect, $query);
			break;
			case 'continue':
			//this is good!
			break;
			default:
				die("<br><b>You did something unusual. Your game has ended! <a href='index.php'>Click here to play again!</a></b><br>");
			break;
		}
	}
} else {
	//issue a new token
	echo "<br><b>Welcome! Your play session has started.<br>You will be able to enter your name when you finish. Enjoy!</b><br>";
	$token = hash('md4', rand());
	$query = "INSERT INTO correlation.sessions VALUES ('{$token}','waiting','','','','',0)";
	mysqli_query($dbconnect, $query);
}

//now they are guaranteed to have a good token, so lets get their details
$query = "SELECT * FROM correlation.sessions WHERE (uid='{$token}')";
$result = mysqli_query($dbconnect, $query);
$details = mysqli_fetch_assoc($result);

$showscore = $details['score'];

//generate the unique IDs to use for the plots to be created
$uid = array();
for($i=0;$i<4;$i++) {
    $uid[] = hash('md4', rand());
}

//update all the image tokens so that they belong to this user
foreach($uid as $num => $iid) {
	$query = "UPDATE correlation.sessions SET iid{$num}='{$iid}' WHERE uid='{$token}'";
	mysqli_query($dbconnect, $query);
}

//update the status to waiting (waiting to solve)
$query = "UPDATE correlation.sessions SET status='waiting' WHERE uid='{$token}'";
mysqli_query($dbconnect, $query);

//generate the rest of the page
//it's small enough that there's no point in making a loop to do this
echo "
<table style='background-color:rgb(235,235,235); border: 1px solid gray;'>
	<tr><td><center><b>Current Score: {$showscore}</b></center></td></tr>
	<tr>
	<td>
	
	<table><tr><td>
	<table><tr>
	<td id='td0' class='oneplot'>
			<table cellpadding=1 id='table0'>
			<tr>
					<td>
							<b>A. </b><img src='genplot.php?uid={$uid[0]}' height=200 width=200 style='border:1px'></img>
					</td>
					<td>
							<b>B. </b><img src='genplot.php?uid={$uid[1]}' height=200 width=200 style='border:1px'></img>
					</td>
			</tr>
			</table>
	</td>
	</tr>

	<tr>
	<td id='td2' class='oneplot'>
			<table cellpadding=1 id='table2'>
			<tr>
					<td>
							<b>C. </b><img src='genplot.php?uid={$uid[2]}' height=200 width=200 style='border:1px'></img>
					</td>
					<td>
							<b>D. </b><img src='genplot.php?uid={$uid[3]}' height=200 width=200 style='border:1px'></img>
					</td>
			</tr>
			</table>

	</td>
	</tr>
	</table>
	</td><td>
	<td id='message'><iframe width='400px' height='270px' src='show.php?uid1={$uid[0]}&uid2={$uid[1]}&uid3={$uid[2]}&uid4={$uid[3]}&session={$token}'></iframe></td>
	</td></tr></table>
	</td>
	</tr>
	<tr><td><form method='POST' action='index.php?token={$token}'><center><input type='submit' value='Continue!'></center></form></td></tr>
</table>
<br>
If you have submitted your answers for the current set of graphs, 'continue' will show you a new set!<br>
If you have not, 'continue' will end your session and allow you to record your score.<br><br>

<b>Top Scores:</b><br><table>";

//let's print them high scores
$query = "SELECT * FROM correlation.scores ORDER BY score DESC LIMIT 50";
$result = mysqli_query($dbconnect,$query);

while($rows = mysqli_fetch_assoc($result)) {
	echo "<tr><td>{$rows['name']}</td><td> - </td><td>{$rows['score']}</td></tr>";
}


echo "</table><br><br>
<a href='changelog.txt'>View the Changelog</a>
</body>
</html>";
?>

