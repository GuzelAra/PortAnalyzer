<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
	<meta name="robots" content="noindex" />
    <title>График диверсификации по монетам</title>
	
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
			// Установка кодировки
			//$mysqli->set_charset("utf8");
			// Если есть ошибка соединения, выводим её и убиваем подключение
			if ($mysqli->connect_error) {
				die('Ошибка : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
			}
			
			$portfolio = "SELECT count(`Symbol`) as label FROM `portfolio`";
					
			$portfolio_sql = mysqli_query($mysqli, $portfolio);
			
			while ($result = mysqli_fetch_array($portfolio_sql)) {
				$ChartData[] = $result;
			};
			
			//print_r($ChartData[0][0]);
			
			//$ChartData = json_encode($ChartData, JSON_PRETTY_PRINT); //конвертим данные в нужный для диаграммы формат
			//print_r($ChartData['label']);
			//echo "<pre>";
			//print_r($bar2dArray);
			//echo "</pre>";
	
		?>
		<div id="chart-container" style="height: 500px;"></div>
		
	
	<script>
		const dataSource = {
		  chart: {
			caption: "Диверсификация портфеля по монетам",
			lowerlimit: "0",
			upperlimit: "50",
			showvalue: "1",
			numbersuffix: " монет",
			theme: "fusion",
			showtooltip: "0"
		  },
		  colorrange: {
			color: [
			  {
				minvalue: "0",
				maxvalue: "10",
				code: "#F2726F"
			  },
			  {
				minvalue: "10",
				maxvalue: "20",
				code: "#FFC533"
			  },
			  {
				minvalue: "20",
				maxvalue: "100",
				code: "#62B58F"
			  }
			]
		  },
		  dials: {
			dial: [
			  {
				value: <? echo $ChartData[0][0]; ?>
			  }
			]
		  }
		};

		FusionCharts.ready(function() {
		  var myChart = new FusionCharts({
			type: "angulargauge",
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