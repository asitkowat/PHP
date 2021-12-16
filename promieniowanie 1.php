<?php

require 'eksport/db.php';
session_start();
$_SESSION=[];
if (!isset($_GET['granularity']) || empty($_GET['granularity']) || (int)$_GET['granularity'] < 1) {
		$_GET['granularity'] = 15;
}
if (!isset($_GET['end']) || empty($_GET['end'])) {

		$_GET['end'] = time();
} else {
		$_GET['end'] = strtotime($_GET['end']);
}
if (!isset($_GET['begin']) || empty($_GET['begin'])) {
		$_GET['begin'] = time() - 60 * 60 * 24;
} else {
		$_GET['begin'] = strtotime($_GET['begin']);
}

if ((int)$_GET['granularity'] === 1) {
		$sql = "
				SELECT
						*
				FROM history
				WHERE
						type = 3
								AND
						timestamp > " . $_GET['begin'] . "
								AND
						timestamp < " . $_GET['end'] . "
				ORDER BY timestamp
		";
}else{
		$sql = "
				SELECT
						*
				FROM history
				WHERE
						id % " . $_GET['granularity'] . " = 1
								AND
						type = 3
								AND
						timestamp > " . $_GET['begin'] . "
								AND
						timestamp < " . $_GET['end'] . "
				ORDER BY timestamp
		";
}

$records_array = [
				'labels' => [],
				'data'   => [],
];
$records = $db->query($sql) or die($db->error);
while ($record = $records->fetch_assoc()) {
		$records_array['labels'][] = '\'' . $record['date'] . ' ' . $record['time'] . '\'';
		$records_array['data'][] = '\'' . $record['value'] . '\'';
}
$_SESSION = $records_array;
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="utf-8">
    <title>Promieniowanie 1</title>
    <meta name="description" content="Wojskowa Akademia Techniczna" />
    <meta name="keywords" content="pogoda, stacja, meteorologiczna, pomiary" />
    <link rel="stylesheet" href="styll.css" type="text/css" />
    <script src="timer.js"></script>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
</head>

<body>

    <body>

        <div id="container">
            <div id="logo">
                <span style="color: #c34f4f">Stacja</span> Meteorologiczna
            </div>
            <div style="clear: both;"></div>
            <form>
                Od:
                <input type="datetime-local" name="begin" value="<?= date("Y-m-d\TH:i", $_GET['begin']) ?>">
                <br>
                Do:
                <input type="datetime-local" name="end" value="<?= date("Y-m-d\TH:i", $_GET['end']) ?>" max="<?= date("Y-m-d\TH:i") ?>">
                <br>
                Ziarnistość: wyświetlaj co
                <input type="number" name="granularity" value="<?= $_GET['granularity'] ?>">
                pomiar (1 = każdy)
                <br>
                <button type="submit">Pokaż</button>
            </form>
            <?php
if(!empty($_SESSION['labels']) && !empty($_SESSION['data'])) { ?>
            <form action="eksportp2.php" method="post">
                <button type="submit">Eksportuj</button>
            </form>
            <?PHP }?>
            <canvas id="temperature1" style="height: 90%">
                Alternatywny tekst
            </canvas>
            <a href="../index.html" class="button 1">Strona główna</a>
        </div>
        <div class="rectangle">2021 &copy; Adrian SITKO - WEL18EB1N1 - Stacja Meteorologiczna
        </div>
    </body>
    <script>
        var ctx = document.getElementById('temperature1').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [
                    <?= implode(',', $records_array['labels']) ?>,
                ],
                datasets: [{
                    label: 'Promieniowanie 1',
                    data: [
                        <?= implode(',', $records_array['data']) ?>,

                    ],
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }, ],
            },
            options: {
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day'
                        }
                    },
                    y: {
                        beginAtZero: true,
                    },
                },
            },
        });

    </script>

</html>
