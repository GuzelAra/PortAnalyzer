<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
	<meta name="robots" content="noindex" />
	
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
	
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.contextMenu.min.css">
	
	<!-- <script src="js/jquery-3.6.0.min.js"></script> -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.contextMenu.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.ui.position.js"></script>
	
	<link rel="stylesheet" type="text/css" href="css/style.css"></link>
	<script src="js/scripts.js"></script>
	
    <title>Portfolio</title>

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
			include ("context_menu.php");
						
			//$ticker = $_POST['ticker'];
		
			// Параметры для подключения к БД
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
			// -------------------------------------------------------------------------
			
			
			// --- Получаем список стейблкоинов из кошелька ---
			$wallet_query = "SELECT `Symbol`, `Available`, `Blocked`, `Total`  FROM `wallet`";
			$sql_wallet = mysqli_query($mysqli, $wallet_query);
			
			$arrWallet = array();	

			while ($Wallet = mysqli_fetch_array($sql_wallet)) {
				$arrWallet[] = $Wallet;
				
				// получаем кол-во USDT для дальнейшего вывода
				if ($Wallet['Symbol'] == 'USDT') {
					$USDTavailable = $Wallet['Available'];
					$USDTblocked = $Wallet['Blocked'];
					$USDTtotal = $Wallet['Total'];
				};

				//print_r($USDT);
			};
			
			
			//Если USDT на счете биржи = 0, то api не выдаст в списке USDT,
			//поэтому ставим нули, чтобы не возникало ошибок обработки
			if (empty($arrWallet)) {
				$USDTavailable = 0;
				$USDTblocked = 0;
				$USDTtotal = 0;
			};
			
			//print_r($arrWallet);
			
			
			
			// Получаем список монет, по которым требуется перевыставить или добавить ордера
			$notification_query = "SELECT DISTINCT(`Symbol`), `Info` FROM `notification`";
			$sql_notification = mysqli_query($mysqli, $notification_query);
			
			$arrNotification = array();
			#$arrNotification2 = array();

			$i=0;
			while ($Notification = mysqli_fetch_array($sql_notification)) {
				$arrNotification[$i] = $Notification['Symbol'];
				#$arrNotification2[$i] = $Notification;
				#array_push($arrNotification, $Notification);
				$i=$i+1;
			};
				
			#print_r($arrNotification2);
			
						
			
			// Получаем историю сделок
			$sql_deals_history = "SELECT `date`, `time`, `CurrencyPair`, `Count`, `Price`, `FeeUSDT`, `DealPrice`, `DealType`
									FROM `deals_history`
									ORDER BY `date`";
			$sql = mysqli_query($mysqli, $sql_deals_history);
			
			$arrDealsHistory = array();
			while ($History = mysqli_fetch_array($sql)) {
				$arrDealsHistory[$History['CurrencyPair']]['date'] = "Дата: ".$History['date'];
				$arrDealsHistory[$History['CurrencyPair']]['time'] = "Время: ".$History['time'];
				//$arrDealsHistory[$History['CurrencyPair']]['CurrencyPair'] = $History['CurrencyPair'];
				$arrDealsHistory[$History['CurrencyPair']]['Count'] = "Кол-во: ".$History['Count'];
				$arrDealsHistory[$History['CurrencyPair']]['Price'] = "Цена монеты: ".$History['Price'];
				$arrDealsHistory[$History['CurrencyPair']]['FeeUSDT'] = "Комиссия в USDT: ".$History['FeeUSDT'];
				$arrDealsHistory[$History['CurrencyPair']]['DealPrice'] = "Цена сделки: ".$History['DealPrice'];
				$arrDealsHistory[$History['CurrencyPair']]['DealType'] = "Тип сделки: ".$History['DealType'];
			};
			//echo "<pre>";
			//print_r($arrDealsHistory['KLAY_USDT'][0]);
			//print_r($arrDealsHistory);
			//echo "</pre>";
			
			//$strDealsHistory = implode(',', $original_array)
			
			//print_r($arrDealsHistory[0][]);
			
			try {

	
					
					echo "<table class='table table-striped table-hover table-bordered table-sm align-middle table-sortable'>";
					echo "<thead>";
					echo "<tr class='header table-light' scope='col'>";
					echo "<th style='width: 10px' oncontextmenu='return menu(1, event);' ></th>";
					echo "<th style='width: 150px'>Монета</th>";
					echo "<th style='width: 230px'>Сектор</th>";
					echo "<th style='background-color: #e5f3f3'>Доступно</th>";
					echo "<th style='background-color: #f6ebeb'>Заблок.</th>";
					echo "<th style='background-color: #ecf6ed'>Всего</th>";
					echo "<th style='width: 80px'>Ср.цена покупки</th>";
					echo "<th>Текущая цена</th>";
					echo "<th>Min цена/день</th>";
					echo "<th>Max цена/день</th>";
					echo "<th style='width: 60px'>Изменение/день</th>";
					echo "<th>Объем</th>";
					echo "<th>Закупочная цена монет</th>";
					echo "<th>Текущая цена монет</th>";
					echo "<th>Прибыль/убыток</th>";
					echo "<th>Изменение/все время</th>";
					echo "<th>% монеты</th>";
					echo "<th>% сектора</th>";
					echo "<th style='width: 50px'>Ордеров</th>";
					echo "</tr>";
					echo "</thead>";
					
					//$sql_query = "SELECT * FROM ".$db_table;
					//$sql_query = "SELECT * FROM `portfolio` p, `coins` c
					//				WHERE p.Symbol = c.Symbol
					//				ORDER BY ProfitLoss DESC";
					$sql_query = "SELECT * FROM `portfolio` p, `coins` c
									WHERE p.Symbol_id = c.id
									ORDER BY ProfitLoss DESC";
					
					

					
					$sql = mysqli_query($mysqli, $sql_query);
					
					//$arr = array();
					
					
					$sumProfitLoss = 0;
					$sumTotalAveragePrice = 0;
					$sumTotalCurrentPrice = 0;
					$sumPercent = 0;
					$sumOpenOrdersCount = 0;
					$sumCoins = 0;
					$arrayCoins = array("USDT","POINT");
					while ($result = mysqli_fetch_array($sql)) {
						// Если монета не входит в список блок монет, то выводим ее в таблице
						if (!in_array($result['Symbol'], $arrBlockedCoins)) {
							// Если монета не входит в массив $arrayCoins, то добавляем к ней вывод последней сделки в подсказке
							if (!in_array($result['Symbol'], $arrayCoins)) {
								echo "<tr title='Последняя сделка: \n".implode("\n", $arrDealsHistory[$result['Symbol'].'_USDT'])."'>";
							}
							else {
								echo "<tr title='Сделки отсутствуют'>";
							};


							
							echo "
							<td class='{$result['Symbol']}'>
								<a href='https://coinmarketcap.com/currencies/{$result['Coin']}' target='_blank'>
									<img src='https://s2.coinmarketcap.com/static/img/coins/32x32/{$result['id']}.png' width='20'>
								</a>
							</td>";
							
							
							// Если монета в списке Notification, то добавляем ей стиль мигающего шрифта						
							if (in_array($result['Symbol'], $arrNotification)) {
								echo "
								<td class='{$result['Symbol']}' style='font-weight: bold'>
								<a href='https://www.gate.io/ru/trade-old/{$result['Symbol']}_USDT' target='_blank' class='change-color'>{$result['Symbol']}</a>
								</td>";
							}
							else {
								echo "
								<td class='{$result['Symbol']}' style='font-weight: bold'>
								<a href='https://www.gate.io/ru/trade-old/{$result['Symbol']}_USDT' target='_blank' style='color: #6cbcc9'>{$result['Symbol']}</a>
								</td>";
							};
							
							
							echo "
							<td class='{$result['Symbol']}'>
							<a href='https://coinmarketcap.com/view/{$result['Sector']}' target='_blank'>{$result['Sector']}</a>
							</td>
							<td class='{$result['Symbol']}' style='background-color: #e5f3f3'>".round($result['Available'], 2)."</td>
							<td class='{$result['Symbol']}' style='background-color: #f6ebeb'>".round($result['Blocked'], 2)."</td>
							<td class='{$result['Symbol']}' style='background-color: #ecf6ed'>".round($result['Total'], 2)."</td>
							<td class='{$result['Symbol']}'>$".round($result['AveragePrice'], 4)."</td>
							<td class='{$result['Symbol']}'>$".round($result['CurrentPrice'], 4)."</td>
							<td class='{$result['Symbol']}'>$".round($result['Min24h'], 4)."</td>
							<td class='{$result['Symbol']}'>$".round($result['Max24h'], 4)."</td>";

							if ($result['Change24h'] > 0) {
								echo "<td class='table-success {$result['Symbol']}'>{$result['Change24h']}%</td>";
								}
							elseif ($result['Change24h'] < 0){
								echo "<td class='table-danger {$result['Symbol']}'>{$result['Change24h']}%</td>";
								}
							else {
								echo "<td class='{$result['Symbol']}'>{$result['Change24h']}%</td>";
								};
							
							echo "<td class='{$result['Symbol']}'>".round($result['Volume'], 0)."</td>
							<td class='{$result['Symbol']}'>$".round($result['TotalAveragePrice'], 2)."</td>
							<td class='{$result['Symbol']}'>$".round($result['TotalCurrentPrice'], 2)."</td>";
							
							$ChangeAllTime = round(($result['TotalCurrentPrice'] - $result['TotalAveragePrice']) / $result['TotalAveragePrice'] * 100, 2);
							if ($result['ProfitLoss'] > 0) {
								echo "<td class='table-success {$result['Symbol']}'>$".round($result['ProfitLoss'], 2)."</td>";
								echo "<td class='table-success {$result['Symbol']}'>{$ChangeAllTime}%</td>";
								}
							elseif ($result['ProfitLoss'] < 0){
								echo "<td class='table-danger {$result['Symbol']}'>$".round($result['ProfitLoss'], 2)."</td>";
								echo "<td class='table-danger {$result['Symbol']}'>{$ChangeAllTime}%</td>";
								}
							else {
								echo "<td class='{$result['Symbol']}'>$".round($result['ProfitLoss'], 2)."</td>";
								echo "<td class='{$result['Symbol']}'>{$ChangeAllTime}%</td>";
								};
							
							
							
							
							$percentRound = round( $result['Percent'], 2);
							$percentSectorRound = round( $result['PercentSector'], 2);
							echo "<td class='{$result['Symbol']}'>{$percentRound}%</td>
							<td class='{$result['Symbol']}'>{$percentSectorRound}%</td>";
							//<td>{$result['OpenOrdersCount']}</td>
							
							
							if ($result['OpenOrdersCount'] > 5) {
								echo "<td class='{$result['Symbol']}'>{$result['OpenOrdersCount']}</td>";
								}
							elseif ($result['OpenOrdersCount'] < 5) {
								echo "<td class='table-danger {$result['Symbol']}'>{$result['OpenOrdersCount']}</td>";
								}
							else {
								echo "<td class='table-warning {$result['Symbol']}'>{$result['OpenOrdersCount']}</td>";
								};
							
							
							
							echo "</tr>";
							
							$sumProfitLoss = round($sumProfitLoss + $result['ProfitLoss'], 2);
							$sumTotalAveragePrice = round($sumTotalAveragePrice + $result['TotalAveragePrice'], 2);
							$sumTotalCurrentPrice = round($sumTotalCurrentPrice + $result['TotalCurrentPrice'], 2);
							$sumPercent = round($sumPercent + $result['Percent'], 2);
							$sumOpenOrdersCount = $sumOpenOrdersCount + $result['OpenOrdersCount'];
							$sumCoins = $sumCoins + 1;
							$sumChangeAllTime = round(($sumTotalCurrentPrice - $sumTotalAveragePrice) / $sumTotalAveragePrice * 100, 2);
							
						
						};
						
					};

									
					echo "<tr>";
					echo "
					<td></td>
					<td><b>МОНЕТ: {$sumCoins}<b></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td><b>ИТОГО</b></td>
					<td></td>
					<td><b>$ {$sumTotalAveragePrice}</b></td>
					<td><b>$ {$sumTotalCurrentPrice}</b></td>
					<td><b>$ {$sumProfitLoss}</b></td>
					<td><b> {$sumChangeAllTime}%</b></td>
					<td><b>{$sumPercent}%</b></td>
					<td></td>
					<td><b>{$sumOpenOrdersCount}</b></td>";
					echo "</tr>";

					
					
					$sTAP_USDT = round($sumTotalAveragePrice + $USDTtotal, 2);
					$sTCP_USDT = round($sumTotalCurrentPrice + $USDTtotal, 2);
					echo "<tr>";
					echo "
					<td><b><b></td>
					<td><b>USDT<br>Всего: $".round($USDTtotal, 2)."<br>Блок: $".round($USDTblocked, 2)."<br>Доступно: $".round($USDTavailable, 2)."<b></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td><b>ИТОГО+USDT</b></td>
					<td></td>
					<td><b>$ {$sTAP_USDT}</b></td>
					<td><b>$ {$sTCP_USDT}</b></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>";
					echo "</tr>";
					
					
					//require 'getcourse.php';
					require 'get_usdt_rub.php';
					//echo $usd * $sTAP_USDT;
					//echo "<br>";
					//echo $usd * $sTCP_USDT;
					
					$RUBtotal = round($USDTtotal * $usd, 2);
					$RUBblocked = round($USDTblocked * $usd, 2);
					$RUBavailable = round($USDTavailable * $usd, 2);
					$sTAP_rub = round($sTAP_USDT * $usd, 2);
					$sTCP_rub = round($sTCP_USDT * $usd, 2);
					$sumProfitLoss_rub = round($sumProfitLoss * $usd, 2);
					
					echo "<tr>";
					echo "
					<td></td>
					<td><b>RUB<br>Всего: ".$RUBtotal."р.<br>Блок: ".$RUBblocked."р.<br>Доступно: ".$RUBavailable."р.<b></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td><b>ИТОГО+RUB</b></td>
					<td></td>
					<td><b>{$sTAP_rub}р.</b></td>
					<td><b>{$sTCP_rub}р.</b></td>
					<td><b>{$sumProfitLoss_rub}р.<b></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>";
					echo "</tr>";
					echo "</table>";
					
				
					
				

					} catch (\AmoCRM\Exception $e) {
					printf('Error (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
				}
				
			
		?>
	
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
	</body>
</html>