<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
	<meta name="robots" content="noindex" />
    <title>График диверсификации по монетам</title>
	
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity=
		"sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
		
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
			// Установка кодировки
			//$mysqli->set_charset("utf8");
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
			
			//print_r($arrBlockedCoins);
			
			
			
			$result = $mysqli->query("SELECT `Symbol` as label, `TotalCurrentPrice` as value, `Sector` as sector, `СurrentPriceSector` as CPSector, 
			`PercentSector` as PercSector, `Percent` FROM ".$db_table." ORDER BY `Percent`;");
			if(!$result) echo "Кривой SQL запрос!";
			
			$arr = array();
		 
			while($row = mysqli_fetch_array($result)) {
				if (!in_array($row['label'], $arrBlockedCoins)) {
					//print_r($row['label']);
					$arr['data'][] = $row;
					
					//для apexchart
					$coin[] = $row['label'];
					$percent[] = round($row['Percent'], 1); //преобразовываем в float и округляем
				}
			};
			
			//print_r($coin);
			//print_r($percent);
			
			$arr['data'] = json_encode($arr['data'], JSON_PRETTY_PRINT); //конвертим данные в нужный для диаграммы формат
			///echo "<pre>";
			///print_r($arr['data']);
			///echo "</pre>";
			
			
			//для ApexCharts
			$coin = json_encode($coin);
			$percent = json_encode($percent);
			//print_r($coin); //для проверки формата данных
			//print_r($percent); //для проверки формата данных
			//echo "Тип $percent: " . gettype ($percent) . "<br />\n";
			
		?>
		
		<!-- fusioncharts -->
		<div style="display: table; margin: 0 auto" id="chartContainer">Блок для диаграммы</div>
		<!-- apexcharts
		<div style="width: 50%; margin: 0 auto" id="chart2"></div>
		-->
		
	<script>
			//FusionChart диаграмма
			FusionCharts.ready(function()
			{
		 
				var revenueChart = new FusionCharts({
					"type":"pie2d",
					"renderAt":"chartContainer",
					"width":1920,
					"height":880,
					"dataFormat":"json",
					"dataSource":
					{
						"chart":
						{
							"caption":"Доли монет в портфеле",
							"subCaption":"Диаграмма",
							"plottooltext": "<b>$label - $percentValue</b> от портфеля",
							"showlegend": "1",
							"showpercentvalues": "1",
							"legendposition": "bottom",
							"usedataplotcolorforlabels": "1",
							"theme": "fusion",
							"xAxisName":"Месяц",
							"yAxisName":"Сумма",
							"numberPrefix":"Руб."
						},
						"data": <?php echo $arr['data']; ?>,
					}
				});
		 
				revenueChart.render();
			});
		 
	</script>
	
	
	<script>
		var options = {
				  series: <? echo $percent; ?>,
				  chart: {
				  width: 880,
				  type: 'pie',
				},
				labels: <? echo $coin; ?>,
				theme: {
				  monochrome: {
					enabled: false
				  }
				},
				fill: {
					type: 'gradient2'
				},
				theme: {
					palette: 'palette1' // upto palette10
				},
				responsive: [{
				  breakpoint: 480,
				  options: {
					chart: {
					  width: 600
					},
					legend: {
					  position: 'bottom'
					}
				  }
				}]
				};

				var chart = new ApexCharts(document.querySelector("#chart2"), options);
				chart.render();
	</script>

	
	
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
	
	</body>
</html>