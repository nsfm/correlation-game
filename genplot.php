<?php
//Correlation Game, 9/29/2014
//Converted to PHP by Nate Dube, thanks to www.istics.net
//The original version was too easy to cheat; this version
//will be more reliable since the processing happens serverside.

//I just converted most of the javascript functions directly to PHP
//and added a MySQL backend.

require_once('stats.php');
require_once('phplot.php');

//configuration
$dbhost = "localhost";
$dbuser = "ephemera";
$dbdb = "correlation";
$dbpassword = "ephemeral123!";

$dbconnect = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbdb);
$dbsuccess = TRUE;

if (mysqli_connect_errno()) {
        $dbsuccess = FALSE;
        die('Database failed to connect. Sorry to let you down.');
}

$uid = $_GET['uid'];

if (isset($_GET['rho'])) {
        $rh = $_GET['rho'];
} else {
        $rh = setRho();
}

//draw this many points
$points = 100;

//store all of our points
$bvn = bvn($rh, $points);

//get the correlation coefficient
$r = bcround(Correlation($bvn),2);

//add this plot to the database
$query = "INSERT INTO images VALUES ('{$uid}', {$r})";
$result = mysqli_query($dbconnect, $query);

//configure the graph - excuse the ugliness
$plot = new PHPlot(200,200);
$plot->SetImageBorderType('plain');
$plot->SetPlotType('points');
$plot->SetDataType('data-data');
$plot->SetDataValues($bvn);
$plot->SetPlotAreaWorld(-3.5,-3.5,3.5,3.5); //bounds
$plot->SetXTickIncrement(20);
$plot->SetYTickIncrement(20);
$plot->SetXAxisPosition(100);
$plot->SetYAxisPosition(100);
$plot->SetXTickLabelPos('none');
$plot->SetYTickLabelPos('none');
$plot->SetPlotBorderType('full');
$plot->SetBackgroundColor('blue');
$plot->SetDataColors('yellow');
$plot->SetDrawDataBorders(False);
//draw the graph - converts the output of this file to an image
if (isset($_GET['showcor'])) { $plot->SetTitle('Correlation: '.$r); }
$plot->DrawGraph();


//define functions below

function bcround($number, $scale=0) {
        $fix = "5";
        for ($i=0;$i<$scale;$i++) $fix="0$fix";
        $number = bcadd($number, "0.$fix", $scale+1);
        return    bcdiv($number, "1.0",    $scale);
}

//sets a value for Rho before each draw
function setRho() {
        $temp = sqrt(js_random());
        if ($temp < .5) {
                return 0-$temp;
        }
        return $temp;
}

//generate points in a vague line in a random direction
function bvn($rho, $n) {
        if ($rho > 1)  { $rho = 1;  }
        if ($rho < -1) { $rho = -1; }
        $c = sqrt(1-$rho*$rho);
        $point  = array();
        for ($i=0; $i<$n; $i++) {
                $u = sqrt(-2*log(js_random()));
                $v = 2*pi()*js_random();
                $w = $c*$u*cos($v);
                $z = $u*sin($v);
                $point[] = array('',$w+$rho*$z, $z);
        }
        return $point;
}

//returns a random number in the way that javascript does
function js_random() {
        return mt_rand()/mt_getrandmax();
}

?>

