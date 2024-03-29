<style>
.navbar {
    padding-top: 0rem !important;
    padding-bottom: 0rem !important;
</style>

<nav class="navbar navbar-expand-lg navbar-light" style="background-color:#f5f5f5 ;z-index: 9999;";>
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><img src="img/logo2024.png" width="100"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent" style="
    font-size: 18px">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="index.php" style="color:#05b9df">Портфель</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="history.php" style="color:#05b9df">История сделок</a>
        </li>
		<li class="nav-item">
          <a class="nav-link" href="orders.php" style="color:#05b9df">Ордера</a>
        </li>
		<!--<li class="nav-item">
          <a class="nav-link" href="calc.php" style="color:#05b9df">Калькулятор</a>
        </li>-->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color:#05b9df">
            Графики
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="chart_coins.php">График распределения активов по монетам</a></li>
            <li><a class="dropdown-item" href="chart_sectors.php">График распределения активов по секторам</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="chart_profitloss.php">График прибыли/убытков</a></li>
			<li><a class="dropdown-item" href="chart_divers.php">График диверсификации по монетам</a></li>
			<li><a class="dropdown-item" href="chart_divsect.php">График диверсификации по секторам</a></li>
          </ul>
        </li>
		<!--
        <li class="nav-item">
          <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
        </li>
		-->
      </ul>
	  <?php
		require 'get_usdt_rub.php';
		require 'get_btc_usdt.php';
		require 'get_eth_usdt.php';
		echo "<span class='navbar-text' style='margin-right: 10px'>
				<a href='https://coinmarketcap.com/currencies/bitcoin/' target='_blank'>
				BTC/USDT: "
					.round($btc,2)."
				</a>
			</span>";
		echo "<span class='navbar-text' style='margin-right: 10px'>
			<a href='https://coinmarketcap.com/currencies/bitcoin/' target='_blank'>
			ETH/USDT: "
				.round($eth,2)."
			</a>
			</span>";
		echo "<span class='navbar-text' style='margin-right: 10px'>
				<a href='https://coinmarketcap.com/currencies/tether/' target='_blank'>
				USDT/RUB: "
					.round($usd,2)."
				</a>
			</span>";
	  ?>
      <form class="d-flex">
		<input class="btn" type="button" id='refresh' name="scriptbutton" value="Обновить" onclick="goPython()">
		<script src="http://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
		
		<script>
		
			function goPython(){

				document.querySelector('#refresh').value = 'ОБНОВЛЕНИЕ...';
				document.querySelector('#refresh').disabled = true;

				$.ajax({
					url: "parse.py",
					context: document.body
				}).done(function() {
					location.reload();;
					//alert('finished python script');;
				});
			}
		</script>
		
        <!-- <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
        <button class="btn" type="submit">Search</button> -->
      </form>
    </div>
  </div>
</nav>