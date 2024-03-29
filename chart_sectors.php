<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
	<meta name="robots" content="noindex" />
    <title>График диверсификации по секторам</title>
	
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
		
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.contextMenu.min.css">
		
		<script src="js/jquery-3.6.0.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.contextMenu.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.ui.position.js"></script>
		<script src="fusion-charts/fusioncharts.js"></script>
		<script src="fusion-charts/fusioncharts.charts.js"></script>
		<script src="fusion-charts/themes/fusioncharts.theme.accessibility.js"></script>
		<script src="fusion-charts/themes/fusioncharts.theme.candy.js"></script>
		<script src="fusion-charts/themes/fusioncharts.theme.carbon.js"></script>
		<script src="fusion-charts/themes/fusioncharts.theme.fint.js"></script>
		<script src="fusion-charts/themes/fusioncharts.theme.fusion.js"></script>
		<script src="fusion-charts/themes/fusioncharts.theme.gammel.js"></script>
		<script src="fusion-charts/themes/fusioncharts.theme.ocean.js"></script>
		<script src="fusion-charts/themes/fusioncharts.theme.umber.js"></script>
		<script src="fusion-charts/themes/fusioncharts.theme.zune.js"></script>
		<script src="fusion-charts/themes/fusioncharts.theme.kelansky.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
		
		<link rel="stylesheet" type="text/css" href="css/style.css"></link>
		<script src="js/scripts.js"></script>
		
		<!-- Font Awesome -->
		<link
		  rel="stylesheet"
		  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
		/> 
		<!-- Google Fonts Roboto -->
		<link
		  rel="stylesheet"
		  href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap"
		/> 
			
			<!-- MDB -->
		<link rel="stylesheet" href="css/mdb.min.css" />
		
  </head>
	<body>
		<?php
			ini_set("display_errors",1);
			error_reporting(E_ALL);
			
			include ("navbar.php");
					
			$db_host = "localhost"; 
			$db_user = "u717574_guzel"; // Логин БД
			$db_password = "qwerty123"; // Пароль БД
			$db_base = "u717574_crypto"; // Имя БД
			$db_table = "portfolio"; // Имя Таблицы БД
			$sym = "*";
			
			// Подключение к базе данных
			$mysqli = new mysqli($db_host,$db_user,$db_password,$db_base);

			// Если есть ошибка соединения, выводим её и убиваем подключение
			if ($mysqli->connect_error) {
				die('Ошибка : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
			}
			
			
			
			// --- Получаем список монет, которые мы не хотим выводить в таблице ---
			$blocked_coins = "SELECT *
								FROM `blocked_coins`";
			$sql_blocked_coins = mysqli_query($mysqli, $blocked_coins);
			
			$arrBlockedCoins = array();	
			while ($Blocked = mysqli_fetch_array($sql_blocked_coins)) {
				$arrBlockedCoins[] = $Blocked['Symbol'];
			};
			
			
			
			
			$result = $mysqli->query("SELECT `Symbol` as label, `TotalCurrentPrice` as value, `Sector` as sector, `СurrentPriceSector` as CPSector, `PercentSector` as PercSector FROM ".$db_table." ORDER BY `Sector`;");
			if(!$result) echo "Кривой SQL запрос!";
			
				
			//--------------------------------------------------------------------
			//сначала получаем массив с названиями секторов
			//затем в цикле по этим секторам через sql вытаскиваем все монеты, принадлежашие сектору
			//и загоняем их в массив в нужном формате
			$sectorsArr = array();
			$db_table = "portfolio_sectors";
			$sectors = $mysqli->query("SELECT `Sector`, `PercentSector`, `СurrentPriceSector` FROM ".$db_table." ORDER BY `PercentSector` DESC;");
			if(!$sectors) echo "Кривой SQL запрос!";
			
			
			$CoinSectorsArr = array();

			$CoinSectorsArr['category']['label'] = "Сектора";
			$CoinSectorsArr['category']['tooltext'] = "Наведите на категорию для информации";
			$CoinSectorsArr['category']['color'] = "##FFFFFF";
			$CoinSectorsArr['category']['value'] = "100";
			
			$j = 0;
			$ColorsArr = array("#3adf16",
								"#54eb71",
								"#96e9a6",
								"#c0e996",
								"#d0f3ab",
								"#e5f57c",
								"#e7fd5b",
								"#ebff00",
								"#ebf300",
								"#f3d900",
								"#f3a100",
								"#f37500",
								"#f35300",
								"#f33100",
								"#ff0000",
								"#ff0036",
								"#FFA2AA",
								"#E47473",
								"#FE9B62",
								"#CA310F");
			foreach ($sectors as $item) {
				///echo $item['Sector'].": ";
				
			
				//-----
				$CoinSectorsArr['category']['category'][] = array(
				'label' => ucfirst($item['Sector']),
				'color' => $ColorsArr[$j],
				'value' => round($item['СurrentPriceSector'],2),
				);
				///echo "<pre>БЛАБЛА";
				///print_r($CoinSectorsArr);
				///echo "</pre>";
				//------
				
				
				$i = 0;
				foreach ($result as $item2) {
					if (!in_array($item2['label'], $arrBlockedCoins)) {
						if ($item['Sector'] == $item2['sector']) {
							///echo $item2['label'].", ";
							
													
							$CoinSectorsArr['category']['category'][$j]['category'][$i]['label'] = $item2['label'];
							$CoinSectorsArr['category']['category'][$j]['category'][$i]['color'] = $ColorsArr[$j];
							$CoinSectorsArr['category']['category'][$j]['category'][$i]['value'] = $item2['value'];
							$i = $i + 1;
							//$j = $j + 1;

						}
					}
					
				}

				
				// если в массиве цветов не хватает цвета, то берем цвет с начала массива
				if ($j <= count($ColorsArr)) {
					$j = $j + 1;
				}
				else {
					$j = 0;
				};
			}
			
			$CoinSectorsArr['category'] = json_encode($CoinSectorsArr['category'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
			
			//создаем файл json
			//$file = fopen("file.json", "a+");
			//записываем данные в файл
			//file_put_contents('file.json', $CoinSectorsArr['category']);
	
		?>
		<div style="width: 50%; margin: 0 auto; height: 880px;" id="chart-container"></div>
		
	
	<script>
		const dataSource = {
		  chart: {
			caption: "Доли монет в портфеле",
			subcaption: "по секторам",
			showplotborder: "1",
			plotfillalpha: "60",
			hoverfillcolor: "#FFFFFF",
			numberprefix: "$",
			plottooltext:
			  "Сумма инвестиций в <b>$label</b> составляет <b>$$value</b>, это $percentValue от родительской категории.",
			theme: "fusion"
		  },
			category: <?php echo "[" . $CoinSectorsArr['category'] . "]"; ?>,
		};


		FusionCharts.ready(function() {
		  var myChart = new FusionCharts({
			type: "multilevelpie",
			renderAt: "chart-container",
			width: "100%",
			height: "100%",
			dataFormat: "json",
			dataSource
		  }).render();
		});
		</script>
	
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
	</body>
</html>