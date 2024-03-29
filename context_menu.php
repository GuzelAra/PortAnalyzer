<!--
<div oncontextmenu="return menu(1, event);" style="height:100px; border:1px solid red; background-color:#FFCCCC;">Кликни правой кнопкой</div>
<div oncontextmenu="return menu(2, event);" style="height:100px; border:1px solid blue; background-color:#CCCCFF;">Кликни правой кнопкой</div>
<div style="height:100px; border:1px solid green; background-color:#CCFFCC;">Кликни правой кнопкой</div>
-->

<!-- Контер для собственного контекстного меню. По умолчания - скрыт. -->
<div id="contextMenuId" style="position:absolute; top:0; left:0; border:1px solid #666; background-color:#FFF; display:none; float:left; z-index:9999;"></div>


<script>
	// Функция для определения координат указателя мыши
	function defPosition(event) {
		  var x = y = 0;
		  if (document.attachEvent != null) { // Internet Explorer & Opera
				x = window.event.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
				y = window.event.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
		  } else if (!document.attachEvent && document.addEventListener) { // Gecko
				x = event.clientX + window.scrollX;
				y = event.clientY + window.scrollY;
		  } else {
				// Do nothing
		  }
		  return {x:x, y:y};
	}

	function menu(type, evt) {
		// Блокируем всплывание события contextmenu
		evt = evt || window.event;
		evt.cancelBubble = true;
		// Показываем собственное контекстное меню
		var menu = document.getElementById("contextMenuId");
		var html = "";
		switch (type) {
			case (1) :
				html = "Меню 1";
				html += "<br><a href='https://cryptomoon.ru/sol-usd/' target='_blanc'>Прогноз по монете</a>";
				html += "<br><a href='https://ru.tradingview.com/chart' target='_blanc'>TradingView</a>";
				html += "<br><a href='#'>Третья функция</a>";
			break;
			case (2) :
				html = "Меню для второго ДИВа";
				html += "<br><i>(пусто)</i>";
			break;
			default :
				// Nothing
			break;
		}
		// Если есть что показать - показываем
		if (html) {
			menu.innerHTML = html;
			menu.style.top = defPosition(evt).y + "px";
			menu.style.left = defPosition(evt).x + "px";
			menu.style.display = "";
		}
		// Блокируем всплывание стандартного браузерного меню
		return false;
	}

	// Закрываем контекстное при клике левой или правой кнопкой по документу
	// Функция для добавления обработчиков событий
	function addHandler(object, event, handler, useCapture) {
		if (object.addEventListener) {
			object.addEventListener(event, handler, useCapture ? useCapture : false);
		} else if (object.attachEvent) {
			object.attachEvent('on' + event, handler);
		} else alert("Add handler is not supported");
	}
	addHandler(document, "contextmenu", function() {
		document.getElementById("contextMenuId").style.display = "none";
	});
	addHandler(document, "click", function() {
		document.getElementById("contextMenuId").style.display = "none";
	});
</script>