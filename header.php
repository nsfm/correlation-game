<?php
echo "
<html>
<head>
<title>Correlation Game V2</title>
        <style type='text/css'>
                label {border: 2px none green; padding : 2px;}
                h1 {color : navy; font-family : Andika, Constantina,Georgia,'Nimbus Roman No9 L',serif;}
                h2 {font-family: 'Arial Narrow', Verdana, Sans-Serif; color : navy;}
                td.oneplot {border: 1px solid gray; vertical-align: top;}
                td.corr {font-weight : bold;}
                button#goButton {font : 13pt bold;}
                p.results {
                        font-family : 'Bookman Old Style',Bookman,'URW Bookman L','Palatino Linotype',serif;
                        color : purple;
                }
                table.scores {
                        border : 1px solid blue;
                        border-collapse: collapse;
                        font-family: Arial;
                        font-size: 10pt;
                        padding: 5px;
                }
                div#main {float : left;}
                div#scores {float : right; padding : 20px;}
                input.hidden {display : none}
                header, footer, aside, nav, article {display: block;}
                #game {display: table;}
                #main {display: table-cell; padding-right: 50px;}
                #highscores {display: table-cell; vertical-align: top;}
        </style>
</head>
<body>
<header>
        <h2 id='top'>Guessing Correlations V2</h3>
                <h3 id='top'>Original by <a href='http://www.istics.net/Correlations/'>www.istics.net</a></h2>
</header>";
?>
