<?php
//configuration
$dbhost = "localhost";
$dbuser = "ephemera";
$dbdb = "correlation";
$dbpassword = "ephemeral123!";

$dbconnect = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdb);

if (mysqli_connect_errno()) {
	die('Failed to connect to database. Sorry to let you down.');
}

if (isset($_GET['session'])) {
	$session = $_GET['session'];
} else {
	//get off this page!
	die('No session ID set!');
}
	
if (isset($_GET['submit'])) {
	//are we processing results
	
	
	//now they are guaranteed to have a good token, so lets get their details
	$query = "SELECT * FROM correlation.sessions WHERE (uid='{$session}')";
	$result = mysqli_query($dbconnect, $query);
	$userdetails = mysqli_fetch_assoc($result);
	$score = $userdetails['score'];
	
	//I am lazy
	$query = "SELECT * FROM correlation.images WHERE (uid='{$userdetails['iid0']}')";
	$result = mysqli_query($dbconnect, $query);
	$id0 = mysqli_fetch_assoc($result);
	$i0 = $id0['coefficient'];
	
	$query = "SELECT * FROM correlation.images WHERE (uid='{$userdetails['iid1']}')";
	$result = mysqli_query($dbconnect, $query);
	$id1 = mysqli_fetch_assoc($result);
	$i1 = $id1['coefficient'];
	
	$query = "SELECT * FROM correlation.images WHERE (uid='{$userdetails['iid2']}')";
	$result = mysqli_query($dbconnect, $query);
	$id2 = mysqli_fetch_assoc($result);
	$i2 = $id2['coefficient'];
	
	$query = "SELECT * FROM correlation.images WHERE (uid='{$userdetails['iid3']}')";
	$result = mysqli_query($dbconnect, $query);
	$id3 = mysqli_fetch_assoc($result);
	$i3 = $id3['coefficient'];
	
	//is the streak unbroken? lets find out
	$unbroken = TRUE;
	
	if ($_POST['uid0'] == $i0) {
		$score++;
		echo "A: Correct!<br>";
	} else {
		echo "A: Incorrect!<br>";
		$unbroken = FALSE;
	}
	
	if ($_POST['uid1'] == $i1) {
		$score++;
		echo "B: Correct!<br>";
	} else {
		echo "B: Incorrect!<br>";
		$unbroken = FALSE;
	}
	
	if ($_POST['uid2'] == $i2) {
		$score++;
		echo "C: Correct!<br>";
	} else {
		echo "C: Incorrect!<br>";
		$unbroken = FALSE;
	}
	
	if ($_POST['uid3'] == $i3) {
		$score++;
		echo "D: Correct!<br>";
	} else {
		echo "D: Incorrect!<br>";
		$unbroken = FALSE;
	}
	
	$query = "UPDATE correlation.sessions SET score='{$score}' WHERE uid='{$session}'";
	mysqli_query($dbconnect, $query);
	
	if ($unbroken) {
		$query = "UPDATE correlation.sessions SET status='continue' WHERE uid='{$session}'";
		echo "Hit the continue button to move on!";
	} else { 
		$query = "UPDATE correlation.sessions SET status='done' WHERE uid='{$session}'";
		echo "Your streak was broken! Final score: {$score}. Hit continue to save your score.";
	}
	mysqli_query($dbconnect, $query);
	
} else {
	//ask the questions!

	usleep(250000); //wait 2.5 tenths of a second for the results to arrive

	$coeff = array();
	$uids  = array();
	
	$uids[] = $_GET['uid1'];
	$uids[] = $_GET['uid2'];
	$uids[] = $_GET['uid3'];
	$uids[] = $_GET['uid4'];

	foreach($uids as $uid) {
			$query = "SELECT * FROM correlation.images WHERE uid='{$uid}'";
			$return = mysqli_query($dbconnect, $query);
			$row = mysqli_fetch_assoc($return);
			$coeff[] = $row['coefficient'];
	}

	shuffle($coeff);

	$i = 0;
	$costring = array('','','','');
	$letters  = array('A','B','C','D');

	foreach ($costring as $str) {
			foreach($coeff as $co) {
					$costring[$i] .= "<input type='radio' name='uid{$i}' value='{$co}'>{$co}<br>";
			}
			$i++;
	}

	echo "<form method='POST' action='show.php?session={$session}&submit=yes'><table width='100%'><tr>";
	
	$t = 0;
	foreach ($letters as $num => $letter) {
			echo "<td><b>{$letter}.</b> <br>{$costring[$num]}</td>";
			if ($t == 1) {
				echo "<tr></tr>";
			}
			$t++;
	}
	echo "</tr></table><table width='100%'><tr><td><center><input type='submit' value='Check answers!'></center></td></tr></table></form>";
}
?>
