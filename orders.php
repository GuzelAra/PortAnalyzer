<!DOCTYPE html>
<html lang="ru">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
		<meta http-equiv="x-ua-compatible" content="ie=edge" />
		<meta name="robots" content="noindex" />
		
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
		
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.contextMenu.min.css">
		
		<script src="../js/jquery-3.6.0.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.contextMenu.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.ui.position.js"></script>
		<script src="../fusion-charts/fusioncharts.js"></script>
		<script src="../fusion-charts/fusioncharts.charts.js"></script>			
		<script src="../fusion-charts/themes/fusioncharts.theme.fint.js"></script>
		<script src="../fusion-charts/themes/fusioncharts.theme.candy.js"></script>
		<script src="../fusion-charts/themes/fusioncharts.theme.carbon.js"></script>
		<script src="../fusion-charts/themes/fusioncharts.theme.fusion.js"></script>
		<script src="../fusion-charts/themes/fusioncharts.theme.gammel.js"></script>
		<script src="../fusion-charts/themes/fusioncharts.theme.ocean.js"></script>
		<script src="../fusion-charts/themes/fusioncharts.theme.umber.js"></script>
		<script src="../fusion-charts/themes/fusioncharts.theme.zune.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
		
		<link rel="stylesheet" type="text/css" href="css/style.css"></link>
		<script src="js/scripts.js"></script>
		
		<title>Orders</title>

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
				//$ticker = $_POST['ticker'];
			
				// Параметры для подключения к БД
				$db_host = "localhost"; 
				$db_user = "u717574_guzel"; // Логин БД
				$db_password = "qwerty123"; // Пароль БД
				$db_base = "u717574_crypto"; // Имя БД
				$db_table = "open_orders"; // Имя Таблицы БД
				$sym = "*";
				
				// Подключение к базе данных
				$mysqli = new mysqli($db_host,$db_user,$db_password,$db_base);
				// Установка кодировки
				//$mysqli->set_charset("utf8");
				// Если есть ошибка соединения, выводим её и убиваем подключение
				if ($mysqli->connect_error) {
					die('Ошибка : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
				}			
							
				
				// Получаем список открытых ордеров
				$sql_orders = "SELECT *
										FROM ".$db_table." 
										ORDER BY `date` DESC";
				$sql = mysqli_query($mysqli, $sql_orders);
				
				
				
				//Заголовок таблицы
				echo "<table class='table table-striped table-hover table-bordered table-sm align-middle table-sortable'>";
				echo "<thead>";
				echo "<tr class='header table-light' scope='col'>";
				echo "<th>Дата</th>";
				echo "<th>Время</th>";
				echo "<th>Пара</th>";
				echo "<th>Количество</th>";
				echo "<th>Цена</th>";
				echo "<th>Цена сделки</th>";
				echo "<th>Тип сделки</th>";
				echo "<th>Статус</th>";
				echo "<th>ID ордера</th>";
				echo "</tr>";
				echo "</thead>";
				
				
				$SumOrders = 0;
				while ($Orders = mysqli_fetch_array($sql)) {
					echo "<tr>";
					echo "<th>".$Orders['date']."</th>";
					echo "<th>".$Orders['time']."</th>";
					echo "<th>".$Orders['CurrencyPair']."</th>";
					echo "<th>".$Orders['Count']."</th>";
					echo "<th>$".$Orders['Price']."</th>";
					echo "<th>$".$Orders['DealPrice']."</th>";
					echo "<th>".$Orders['DealType']."</th>";
					echo "<th>".$Orders['Status']."</th>";
					echo "<th>".$Orders['OrderID']."</th>";
					echo "</tr>";			
					
					$SumOrders = $SumOrders + 1;
				
				};
				
				echo "<tr>";
				echo "<th><b>ОРДЕРОВ ВСЕГО</b></th>";
				echo "<th><b>".$SumOrders." шт.</b></th>";
				echo "<th></th>";
				echo "<th></th>";
				echo "<th></th>";
				echo "<th></th>";
				echo "<th></th>";
				echo "<th></th>";
				echo "<th></th>";
				echo "</tr>";
				echo "</table>";
				
				
				echo "</table>";

			?>
			
			<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
		
		</body>
</html>