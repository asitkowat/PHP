<?php
require 'db.php';

$types = [
	0 => [
		'name' => "Temperatura 2",
		'hash' => "c8a21e75-35cf-4acd-9464-85ffdcded852",
	],
	1 => [
		'name' => "Kierunek Wiatru",
		'hash' => "252795cb-2d5f-4d52-a05a-e7740ecad2f5",
	],
		2 => [
			'name' => "Wilgotność Powietrza",
			'hash' => "8b945125-bc47-4804-9f68-8c3afe9448d9",
		],
	3 => [
		'name' => "Promieniowanie 1",
		'hash' => "340c7d0b-64f1-4a92-86e8-6aa93de19f9e",
	],
	4 => [
		'name' => "Promieniowanie 2",
		'hash' => "340c7d0b-64f1-4a92-86e8-6aa93de19f9e",
	],
	5 => [
		'name' => "Temperatura Powietrza",
		'hash' => "10118008-3f6f-4803-9d20-024d4a6a31e0",
	],
];



foreach ($types as $type => $details) {
	try {
		$sql = "
						SELECT
								date,
								time
						FROM history
						WHERE
							type = " . $type . "
						ORDER BY
							timestamp DESC
						LIMIT 1
				";
		$last_timestamp = $db->query($sql) or print_r($db->error, TRUE);
		$last_timestamp = $last_timestamp->fetch_assoc();
		$api_content = json_decode(
			file_get_contents(
				'https://api.system.pmecology.com/v1/data/' .
				$details['hash'] .
				'?timestamp=' .
				$last_timestamp['date'] .
				'T' .
				$last_timestamp['time'] .
				'&records=1000'
			),
			TRUE,
			512,
			JSON_THROW_ON_ERROR
		);
		$values = [];
		foreach ($api_content['history'] as $item) {
			$unix_time = strtotime($item['timestamp']);
			$sql = "
								SELECT
									id
								FROM history
								WHERE
									type = " . $type . "
										AND
									timestamp =  " . $unix_time . "
								LIMIT 1
						";
			$exists = $db->query($sql) or print_r($db->error, TRUE);
			if ($exists->num_rows === 0) {
				$sql = "
										INSERT INTO history
										(
												timestamp,
												date,
												time,
												type,
												value,
												raw
										) VALUES (
												'" . $unix_time . "',
												'" . date("Y-m-d", $unix_time) . "',
												'" . date("H:i:s", $unix_time) . "',
												'" . $type . "',
												'" . $item['value'] . "',
												'" . $item['raw'] . "'
										)
								";
				$db->query($sql) or print_r($db->error, TRUE);
			}
		}
	} catch (Exception $e) {
		die(print_r($e, TRUE));
	}
}
