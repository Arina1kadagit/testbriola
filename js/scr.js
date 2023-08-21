function showHistory(elem) {
	return false;
	historyElem = elem.querySelector("#product_history");
	if (historyElem.style.display == 'block') {
		historyElem.style.display = 'none';
	} else {
		historyElem.style.display = 'block';
	}
}

function updOverall() {

	stringsWithProducts = document.querySelectorAll('#order_table .in_order');
	Sum = 0;
	for (var i = 0; i <= stringsWithProducts.length - 1; i++) {
		stringWithProduct = stringsWithProducts[i];
		productQuantity = stringWithProduct.querySelector('input').value;
		//console.log(productQuantity);
		if (productQuantity > 0) {
			productPrice = parseNum(stringWithProduct.querySelector('#product_price').textContent);
			
			//console.log(Sum);
			//console.log(productQuantity);
			//console.log(productPrice);
			//console.log(productPrice*productQuantity);
			//console.log(Math.round(productPrice*productQuantity*100)/100);
			
			// Сумма не работает в Джаваскрипте???
			//Sum = Sum + Math.round(productPrice*productQuantity*100)/100;
			//Sum = Number((Sum + Math.round(productPrice*productQuantity*100)/100).toFixed(2));
			Sum = Number((Sum + productPrice*productQuantity).toFixed(2));
				//console.log((productPrice*productQuantity).toFixed(2));
			
			//console.log(Sum);

		}
	}
	Sum = Sum.toFixed(2);
	console.log(Sum);
	/*
	elemInfo = document.getElementById("overall_info");
	elemInfo.innerHTML = 'Сумма заказа:<br><b>' + Sum + '</b>';
	*/

	
	elemInfo = document.getElementById("sum_sum");
	elemInfo.innerHTML = 'Сумма заказа: <b>' + Sum + '</b>';

}

// При нажатии на родительскую строку Контрагента+ТорговойТочки открывает/скрывает её заказы
function toggleOrderList(elem) {
	elemContractGUID = elem.querySelector('#contract_GUID').textContent;
	console.log(elemContractGUID);
	allRows = document.querySelectorAll('.list_row');
	for (var i = 1; i <= allRows.length; i++) {
		elemRow = allRows[i-1];
		elemRowContractGUID = elemRow.querySelector('#contract_GUID').textContent;
		if (elemRowContractGUID == elemContractGUID) {
			elemRowOrderGUID =  elemRow.querySelector('#order_GUID').textContent;
			if (elemRowOrderGUID != '') {
				if (!elemRow.classList.contains('hidden_row')) {
					elemRow.classList.add("hidden_row");
				} else {
					elemRow.classList.remove("hidden_row");
				}
			}
		}
	}

}

function toggleOrderListTT(elem) {

	//console.log('toggleOrderList - '+elem);

	elemOrderGUID = elem.querySelector('#numberOrder');
	//console.log(elemOrderGUID);
	elemOrderGUID = elemOrderGUID.textContent;

	allRows = document.querySelectorAll('.list_row');
	for (var i = 1; i <= allRows.length; i++) {
		elemRow = allRows[i-1];
		//console.log(elemRow);
		elemRowOrderGUID = elemRow.querySelector('#numberOrder');
		//console.log(elemRowOrderGUID);
		elemRowOrderGUID = elemRow.querySelector('#numberOrder').textContent;
		if (elemRowOrderGUID == elemOrderGUID) {
			if (elemRowOrderGUID != '') {
				if (!elemRow.classList.contains('hidden_list_row')) {
					elemRow.classList.add("hidden_list_row");
				} else {
					elemRow.classList.remove("hidden_list_row");
				}
			}
		}
	}
}

function closeOrder() {
	event.preventDefault();
	// Блокируем окно на время открытия заказа
	document.getElementById("veil").style.display = 'grid';
	// 
	//document.getElementById("window").innerHTML = '';
	//document.getElementById("orders_list").style.display = 'grid';
	elemCurrentParentGUID = document.querySelector('#current_parent_GUID');
	elemCurrentParentGUID.textContent = '00000000-0000-0000-0000-000000000000';

	// Меняем строку адреса
	strLoc = location.toString();
	i = 0;
	i = strLoc.indexOf('?page=');
	if (i>0) {
		strLoc = strLoc.slice(0, i);
	} else {
		strLoc = strLoc;
	}
	location.href = strLoc;
	//history.pushState(null, null, strLoc);
}

// Открывает заказ из списка заказов интерфейса торгового представителя
function openOrder(elem) {

	// Блокируем окно на время открытия заказа
	document.getElementById("veil").style.display = 'grid';
	
	// Параметры запроса
	orderGUID = elem.querySelector('#order_GUID').textContent;
	//contractGUID = elem.querySelector('#contractGUID').textContent;
	onlyBody = true;
	
	// Отправка запроса с данными на tporder.php(orderGUID, onlyBody):
	
	// Инициализация запроса
	// С поддержкой старых браузеров:
	//Мы создаём XMLHttpRequest и проверяем, поддерживает ли он событие onload. 
	//Если нет, то это старый XMLHttpRequest, значит это IE8,9, 
	//и используем XDomainRequest.
	var xhrObj = ("onload" in new XMLHttpRequest()) ? XMLHttpRequest : XDomainRequest;
	var xhr = new xhrObj();
	// Без поддержки старых браузеров:
	// var xhr = new XMLHttpRequest();
	xhr.open("GET", 'tporder.php?orderGUID='+orderGUID+'&onlyBody='+onlyBody, true);
	//xhr.setRequestHeader('Authorization', '');
	xhr.setRequestHeader('Content-type', 'application/json; charset=utf-8');
	xhr.onreadystatechange = function () {
		if (this.readyState == 4 && this.status == 200) {
			// Вставляем результат в окно
			document.getElementById("window").innerHTML = this.responseText;
			document.getElementById("orders_list").innerHTML = '';
			document.getElementById("orders_list").style.display = 'none';
			document.getElementById("orders_list").textContent = '';
			document.getElementById("header_tp").style.display = 'grid';


			//document.getElementById("contract_GUID2").textContent = contractGUID;
			// Запрос остатков и цен
			complete();
			// Дозагрузка картинок
			setLazy();
			lazyLoad();
			// Разблокируем окно
			document.getElementById("veil").style.display = 'none';

			var strLoc = location.toString(); 
			console.log(strLoc);
			if (strLoc.indexOf("orderGUID") > -1){
				/*

				accordion = document.querySelector(".accordion");
				//console.log('accordion - '+accordion.length);
				li = accordion.getElementsByTagName('li');
				//console.log('length - '+li.length);
				count = 0;
				for (var i=0; i<li.length; i++){
					if(li[i].style.display == 'none'){
						count++;
					}
				}
				console.log('count - '+count);
				//if (count == (li.length -1))
				if (count == '1')
					
				

				//b = accordion.getElementsByTagName("b");
				//console.log(b[0]);
				//console.log(b[0].textContent);
				//orderGUID = document.querySelector("#order_GUID").textContent;
				//if (b != null){
					//if (b[0].textContent == 'Notice')
						


					{
					
					//переопределяем меню 1
					$.ajax({
				    	type : 'POST',
				        url: "setMenu.php",
				        data: {orderGUID},
				        success: function(data){
				        	document.querySelector(".matrix_goods").textContent = '';
				        	document.querySelector(".matrix_goods").innerHTML = data;
				        	var head= document.getElementsByTagName('head')[0];
						    var script= document.createElement('script');
						    script.src= 'js/script.js';
						    head.appendChild(script);
					    }
			   	 	})

			   	 	//переопределяем меню 2
					$.ajax({
				    	type : 'POST',
				        url: "setMenu2.php",
				        data: {orderGUID},
				        success: function(data){
				        	document.querySelector(".all_goods").textContent = '';
				        	document.querySelector(".all_goods").innerHTML = data;
				        	
					    }

			   	 	})

			   	 	
				}
				/*}*/


				//делаем ссылкти в меню с верным guid -  в подменю
				orderGUID = document.getElementById("order_GUID");
				console.log(orderGUID);
				elem = document.querySelectorAll(".elem");
				for (var i = 1; i <= elem.length; i++) {
					element = elem[i-1];
					//console.log(element);
					a = element.getElementsByTagName("a");
					//console.log(a[0]);
					href1 = a[0].href;
					//console.log(href1);
					index0 = href1.indexOf("orderGUID=");
					index1 = href1.indexOf("&menu");
					slice = href1.slice(index0, index1);
					//console.log('slice '+slice);
					if(slice == 'orderGUID='){
						numbers2 = href1.slice(0, index0+10) + orderGUID.textContent + href1.slice(index0+10);
						//console.log('numbers2 - '+numbers2);
						a[0].href = numbers2;
					}else{
						numbers =  slice.replace("orderGUID=", ""); 
					//console.log('numbers - '+numbers);
					//console.log(orderGUID.textContent);
					numbers2 =  href1.replace(numbers, orderGUID.textContent); 
					//console.log('numbers2 - '+numbers2);
					a[0].href = numbers2;
					}	
				}

				//ссылка для всего ассортимента и всей матрице
				accordion = document.querySelectorAll(".accordion");
				for (var i = 1; i <= accordion.length; i++) {
					li = accordion[i-1].getElementsByTagName('li');
					console.log('3 - '+li.length);
					last_li = li[li.length-1];
					link = last_li.querySelector('.link');
					a = link.getElementsByTagName('a');
					console.log(a);
					href1 = a[0].href;
					//console.log(href1);
					index0 = href1.indexOf("orderGUID=");
					index1 = href1.indexOf("&menu");
					slice = href1.slice(index0, index1);
					//console.log('slice '+slice);
					if(slice == 'orderGUID='){
						numbers2 = href1.slice(0, index0+10) + orderGUID.textContent + href1.slice(index0+10);
						//console.log('numbers2 - '+numbers2);
						a[0].href = numbers2;
					}else{
						numbers =  slice.replace("orderGUID=", ""); 
					//console.log('numbers - '+numbers);
					//console.log(orderGUID.textContent);
					numbers2 =  href1.replace(numbers, orderGUID.textContent); 
					//console.log('numbers2 - '+numbers2);
					a[0].href = numbers2;
					}	
				}

				
				
			}

			/*
			//делаем видимым блок call
			header = document.querySelector("#header_tp");
	        if(header.style.display != "none"){
	        	document.querySelector("#call").style.display = "block";
	        }
	        */

		};
	};
	xhr.onerror = function() {
		alert('Ошибка при получении цен и остатков: ' + this.status );
		// Разблокируем окно
		document.getElementById("veil").style.display = 'none';
	}
	// Отправляем запрос
	xhr.send();


	//строка адреса - для товаров без родителя 
	strLoc = location.toString();
	strLoc = strLoc + '?orderGUID=' + orderGUID+'&showAllMatrix=true';
	history.pushState(null, null, strLoc);
	
}


function openOrder2(elem) {	
	// Параметры запроса
	orderGUID = elem.querySelector('#order_GUID').textContent;
	onlyBody = true;


	//window.location.href = 'http://192.168.95.229/aut2/tporder.php?orderGUID='+orderGUID+'&onlyBody='+onlyBody';
	location.replace("./tporder.php?orderGUID="+orderGUID+"&onlyBody="+onlyBody);
}



function doNothing() {

	// Отменяем все остальные onclick
	/*
	event.preventDefault();
	event.stopPropagation();
	*/
}

function closeProductCard() {
	
	elemFloatWindow = document.getElementById("float_window");
	elemFloatWindow.innerHTML = '';
	elemFloatWindow.style.transform = 'scale3d(0,0,1)';
	
	// Разблокируем окно
	elemVeil = document.getElementById("veil");
	elemVeil.style.display = 'none';
	//elemVeil.removeEventListener('click', closeProductCard);

	// Убираем закрытие по клику
	window.removeEventListener("click", closeProductCard);

}

// Открывает заказ из списка заказов интерфейса торгового представителя
function openProductCard(elem) {

	// Отменяем все остальные onclick
	event.preventDefault();
	event.stopPropagation();

	// Блокируем окно на время открытия продукта
	elemVeil = document.getElementById("veil");
	elemVeil.style.backgroundColor = 'transparent';
	elemVeil.style.display = 'grid';
	//elemVeil.addEventListener('click', closeProductCard);

	// Закрытие по клику
	window.addEventListener("click", closeProductCard);
	product_card = document.getElementById("product_card");
	if(product_card != null){
		product_card.addEventListener("click", closeProductCard);
	}
		
	// Параметры запроса
	productGUID = elem.parentNode.querySelector('#guid').textContent; /**/
	onlyBody = true;
	
	// Отправка запроса с данными на tporder.php(orderGUID, onlyBody):
	
	// Инициализация запроса
	// С поддержкой старых браузеров:
	var xhrObj = ("onload" in new XMLHttpRequest()) ? XMLHttpRequest : XDomainRequest;
	var xhr = new xhrObj();
	// Без поддержки старых браузеров:
	// var xhr = new XMLHttpRequest();
	xhr.open("GET", 'product.php?productGUID='+productGUID+'&onlyBody='+onlyBody, true);
	//xhr.setRequestHeader('Authorization', '');
	xhr.setRequestHeader('Content-type', 'text/html; charset=utf-8');
	xhr.onreadystatechange = function () {
		if (this.readyState == 4 && this.status == 200) {
			// Вставляем результат в окно
			console.log('32143234234');
			elemFloatWindow = document.getElementById("float_window");
			elemFloatWindow.innerHTML = this.responseText
			elemFloatWindow.style.transform = 'none';
			
			// Скрываем список заказов
			//document.getElementById("orders_list").style.display = 'none';
			// Запрос остатков и цен
			//complete();
			// Дозагрузка картинок
			//setLazy();
			//lazyLoad();
			
		} else if (this.status == 401) {
			console.log('Ошибка при открытии карточки продукта: ' + this.status);
			//alert('Ошибка при открытии карточки продукта: ' + this.status);
			
			// Разблокируем окно
			elemVeil.style.display = 'none';

			// Убираем закрытие по клику
			window.removeEventListener("click", closeProductCard);

			location.href = 'login.php';
		}
	};
	xhr.onerror = function () {
		console.log('2222222222jfdhdf');
		alert('Ошибка при открытии карточки продукта: ' + this.status);
		// Разблокируем окно
		//document.getElementById("veil").style.display = 'none';
	}
	// Отправляем запрос
	xhr.send();
}

// Получение цен и остатков после полной загрузки страницы
function complete() {

	// Данные для отправки запроса
	elemWithContractGUID = document.querySelector('#contract_GUID');
	stringsWithProducts = document.querySelectorAll('#order_table .journal_row');
	if(elemWithContractGUID != null){
		contractGUID = elemWithContractGUID.textContent;
	} else{
		contractGUID = '';
	}
	console.log('contractGUID - '+contractGUID);
	let arrOfProductsGUID = new Array();
	for (var i = 1; i <= stringsWithProducts.length; i++) {
		stringWithProduct = stringsWithProducts[i-1];
		productGUID = stringWithProduct.querySelector('#guid');
		if (productGUID != null) {
			arrOfProductsGUID.push(productGUID.textContent);
		}
	}

	if(contractGUID != ''){
		// С поддержкой старых браузеров:
		var xhrObj = ("onload" in new XMLHttpRequest()) ? XMLHttpRequest : XDomainRequest;
		var xhr = new xhrObj();
		// Без поддержки старых браузеров:
		// var xhr = new XMLHttpRequest();
		var json = JSON.stringify({
			idContract: contractGUID,
			matrix: arrOfProductsGUID
		});
		console.log('contractGUID_ ' + contractGUID);
		//xhr.open("POST", ADDRESS_UT_API_CONST + '/update', true)
		xhr.open("POST", 'update.php', true);
		xhr.setRequestHeader('Authorization', 'Basic 0JDQtNC80LjQvdC40YHRgtGA0LDRgtC+0YA6aGpkdGg=');
		xhr.setRequestHeader('Content-type', 'application/json; charset=utf-8');
		xhr.onreadystatechange = function () {
			if (this.readyState == 4 && this.status == 200) {
				// Когда ответ с данными получен, заполняем страницу полученными данными цен и остатков
				fillTable(this);
			};
		};
		xhr.onerror = function() { alert('Ошибка при получении цен и остатков: ' + this.status ); }
		// Отсылаем объект в формате JSON и с Content-Type application/json
		// Сервер должен уметь такой Content-Type принимать и раскодировать
		xhr.send(json);
	}

	hideBurger();	


	hideRowsTableDebt(contractGUID);
};

function hideBurger(){
	var burger = document.querySelector('.header_burger');
	var orders_list = document.querySelector('#orders_list');
	if (orders_list != null){
		if (orders_list.style.display != 'none'){
			burger.style.display = 'none';
		} else{
			burger.style.display = 'block';
		}
	}
}

function complete2() {

		// Данные для отправки запроса
	elemWithContractGUID = document.querySelector('#contract_GUID2');
	stringsWithProducts = document.querySelectorAll('#order_table .journal_row');
	contractGUID = elemWithContractGUID.textContent;
	let arrOfProductsGUID = new Array();
	for (var i = 1; i <= stringsWithProducts.length; i++) {
		stringWithProduct = stringsWithProducts[i-1];
		productGUID = stringWithProduct.querySelector('#guid');
		if (productGUID != null) {
			arrOfProductsGUID.push(productGUID.textContent);
		}
	}

	// С поддержкой старых браузеров:
	var xhrObj = ("onload" in new XMLHttpRequest()) ? XMLHttpRequest : XDomainRequest;
	var xhr = new xhrObj();
	// Без поддержки старых браузеров:
	// var xhr = new XMLHttpRequest();
	var json = JSON.stringify({
		idContract: contractGUID,
		matrix: arrOfProductsGUID
	});
	console.log('contractGUID_2 ' + contractGUID);
	//xhr.open("POST", ADDRESS_UT_API_CONST + '/update', true)
	xhr.open("POST", 'update.php', true);
	xhr.setRequestHeader('Authorization', 'Basic 0JDQtNC80LjQvdC40YHRgtGA0LDRgtC+0YA6aGpkdGg=');
	xhr.setRequestHeader('Content-type', 'application/json; charset=utf-8');
	xhr.onreadystatechange = function () {
		if (this.readyState == 4 && this.status == 200) {
			// Когда ответ с данными получен, заполняем страницу полученными данными цен и остатков
			fillTable2(this);
		};
	};
	xhr.onerror = function() { alert('Ошибка при получении цен и остатков: ' + this.status ); }
	// Отсылаем объект в формате JSON и с Content-Type application/json
	// Сервер должен уметь такой Content-Type принимать и раскодировать
	xhr.send(json);
};


// Заполняем страницу полученными данными цен и остатков
function fillTable(xhttp) {
	
	elemWithContractGUID = document.querySelector('#contract_GUID');
	if (elemWithContractGUID.textContent == '') {
		thisIsExistOrder = true;
	} else {
		thisIsExistOrder = false;
	}

	stringsWithProducts = document.querySelectorAll('#order_table .journal_row');

	//console.log('запрос отправлен' + xhttp.responseText);
	productsBalancePriceArray = JSON.parse(xhttp.responseText);
	console.log('выполняется fillTable ' + 'productsBalancePriceArray - '+productsBalancePriceArray); //console
	console.dir(productsBalancePriceArray);
	arr = productsBalancePriceArray['arrayOfPrices'];

	seller = productsBalancePriceArray['seller'];
	contact_seller = document.querySelector('#contact_seller');
	if ((seller != 'showall') && (contact_seller != null)){ //чтобы не отображалось на tp.php
		contact_seller.textContent = seller;
		console.log('seller' + seller);
		$.ajax({
	    	type : 'POST',
	        url: "setSeller.php",
	        data: {name: seller},
	        success: function(data){
	        	console.log('Set seller!');
		    }
   	 	})

		contact_name = document.querySelector('#call_name');
		contact_telephone = document.querySelector('#call_telephone');

   	 	if (contact_seller.textContent == ""){

   	 	} else{
	   	 	if (contact_name.textContent == ''){
	   	 		name = seller.replace(/[^a-zа-яё ]/gi, '');
	   	 		contact_name.textContent = name;
	   	 	}
			if (contact_telephone.textContent == ""){
				number = seller.replace(/[^\d]/g, '');
	   	 		contact_telephone.textContent = number;
	   	 	}
   	 	}
   	 			
	} 
	
	let hString;
	console.log('stringsWithProducts.length - '+stringsWithProducts.length);

	//берем товар - ищем соответствие по гуиду в массиве цен
	for (var i = 1; i <= stringsWithProducts.length; i++) {

		stringWithProduct = stringsWithProducts[i-1];
		guidInString = stringWithProduct.querySelector('#guid');
		if (guidInString != null) {
			for (strIndx in arr) {
				if (guidInString.textContent == arr[strIndx].idNomenclature) {
					hString = '<div><table width = "100%" border = "1"><caption>История заказов</caption><tr><th>Номер</th><th>Дата</th><th>Кол-во</th><th>Цена</th><th>Сумма</th></tr>';
					elemPrice = stringWithProduct.querySelector('#product_price');
					elemBalance = stringWithProduct.querySelector('#product_balance');
					elemInput = stringWithProduct.querySelector('input');
					elemHistory = stringWithProduct.querySelector('#product_history');
					elemPrice.textContent = arr[strIndx].price + '\u00A0₽\u00A0/\u00A0ед.';
					if (arr[strIndx].remains > 0) {
						elemBalance.textContent = 'В наличии';
						elemBalance.style.color = 'darkgreen';
					} else {
						elemBalance.textContent = 'Отсутствует';
						elemBalance.style.color = 'darkred';
					}
					if (thisIsExistOrder) {
						elemInput.max = (arr[strIndx].remains < 0) ? 0 : arr[strIndx].remains;
					} else {
						elemInput.max = (arr[strIndx].remains < 0) ? 0 : arr[strIndx].remains;
					}
					arrHistory = arr[strIndx].arrayOfOrder;
					for (strHistoryIndx in arrHistory) {
						numberOrder = arrHistory[strHistoryIndx].numberOrder;
						dateOrder = arrHistory[strHistoryIndx].dateOrder;
						quantity = arrHistory[strHistoryIndx].quantity;
						price = arrHistory[strHistoryIndx].price;
						sum = arrHistory[strHistoryIndx].sum;
						//hString = hString + numberOrder + dateOrder + quantity + price + sum + '<br>';
						hString = hString + '<tr><td>'+numberOrder+'</td><td>'+dateOrder.substring(0, 10)+'</td><td>'+quantity+'</td><td>'+price+'</td><td>'+sum+'</td></tr>';
					}
					hString = hString + '</table></div>';
					elemHistory.innerHTML = hString;
					console.log(stringWithProduct);
				}
				//console.log(arr[strIndx].idNomenclature);
				//console.log(strIndx);
			}
			
		}
	}
	updOverall();

	//есть ли тп отображение таблицы? если нет, то проверить условие
	//DebitorDolg();
};

function fillTable2(xhttp) {
	
	elemWithContractGUID = document.querySelector('#contract_GUID2');
	if (elemWithContractGUID.textContent == '') {
		thisIsExistOrder = true;
	} else {
		thisIsExistOrder = false;
	}

	stringsWithProducts = document.querySelectorAll('#order_table .journal_row');

	//console.log('запрос отправлен' + xhttp.responseText);
	productsBalancePriceArray = JSON.parse(xhttp.responseText);
	//console.log(productsBalancePriceArray);
	arr = productsBalancePriceArray['arrayOfPrices'];
	let hString;
	console.log('выполняется fillTable2' + stringsWithProducts.length);
	for (var i = 1; i <= stringsWithProducts.length; i++) {

		stringWithProduct = stringsWithProducts[i-1];
		guidInString = stringWithProduct.querySelector('#guid');
		if (guidInString != null) {
			for (strIndx in arr) {
				if (guidInString.textContent == arr[strIndx].idNomenclature) {
					hString = '<div><table width = "100%" border = "1"><caption>История заказов</caption><tr><th>Номер</th><th>Дата</th><th>Кол-во</th><th>Цена</th><th>Сумма</th></tr>';
					elemPrice = stringWithProduct.querySelector('#product_price');
					elemBalance = stringWithProduct.querySelector('#product_balance');
					elemInput = stringWithProduct.querySelector('input');
					elemHistory = stringWithProduct.querySelector('#product_history');
					elemPrice.textContent = arr[strIndx].price + '\u00A0₽\u00A0/\u00A0ед.';
					if (arr[strIndx].remains > 0) {
						elemBalance.textContent = 'В наличии';
						elemBalance.style.color = 'darkgreen';
					} else {
						elemBalance.textContent = 'Отсутствует';
						elemBalance.style.color = 'darkred';
					}
					if (thisIsExistOrder) {
						elemInput.max = (arr[strIndx].remains < 0) ? 0 : arr[strIndx].remains;
					} else {
						elemInput.max = (arr[strIndx].remains < 0) ? 0 : arr[strIndx].remains;
					}
					arrHistory = arr[strIndx].arrayOfOrder;
					for (strHistoryIndx in arrHistory) {
						numberOrder = arrHistory[strHistoryIndx].numberOrder;
						dateOrder = arrHistory[strHistoryIndx].dateOrder;
						quantity = arrHistory[strHistoryIndx].quantity;
						price = arrHistory[strHistoryIndx].price;
						sum = arrHistory[strHistoryIndx].sum;
						//hString = hString + numberOrder + dateOrder + quantity + price + sum + '<br>';
						hString = hString + '<tr><td>'+numberOrder+'</td><td>'+dateOrder.substring(0, 10)+'</td><td>'+quantity+'</td><td>'+price+'</td><td>'+sum+'</td></tr>';
					}
					hString = hString + '</table></div>';
					elemHistory.innerHTML = hString;
					//console.log(stringWithProduct);
				}
				//console.log(arr[strIndx].idNomenclature);
				//console.log(strIndx);
			}
			
		}
	}
	updOverall();
};



function sendOrderTT1() {
	
	contractGUID = elemWithContractGUID.textContent;
	accountGUID = elemWithAccountGUID.textContent;
	let arrOfProducts = new Array();
	for (var i = 1; i <= stringsWithProducts.length; i++) {                    // линия для именения размера
		stringWithProduct = stringsWithProducts[i-1];
		productGUID = stringWithProduct.querySelector('#guid');
		productNumInOrderObj = stringWithProduct.querySelector('input');
		if (productGUID != null) {
			if (Boolean(productNumInOrderObj.value)) {
				productNumInOrder = productNumInOrderObj.value;
				//arrOfProduct = new Array('gui' , 'gtr');
				/*arrOfProduct = new Array(
					{'idNomenclature': productGUID.textContent,
					 'quantity': productNumInOrder}
				);*/
				//arrOfProduct.push(productGUID.textContent);
				//arrOfProduct.push(productNumInOrder);
				//arrOfProduct['gui'] = productGUID.textContent;
				//arrOfProduct['gtr'] = productNumInOrder;
				arrOfProducts.push({'idNomenclature': productGUID.textContent, 'quantity': productNumInOrder});
			} else {
				productNumInOrder = 0;
			}
			console.log(productGUID.textContent + ' ' + productNumInOrder);
			//console.log(productNumInOrder);
		}
	}
	console.log( arrOfProducts );
	// С поддержкой старых браузеров:
	var xhrObj = ("onload" in new XMLHttpRequest()) ? XMLHttpRequest : XDomainRequest;
	var xhr = new xhrObj();
	// Без поддержки старых браузеров:
	// var xhr = new XMLHttpRequest();
	//console.log( elemWithContractGUID );
	var json = JSON.stringify({
		idOrder: '',
		accountGUID: accountGUID,
		idContracts: 'c4523f4a-32b0-11eb-85ab-b42e99611d38',
		arrayOfPrices: arrOfProducts
	});
	console.log( json );

	xhr.open("POST", ADDRESS_UT_API_CONST + '/order', true)
	xhr.setRequestHeader('Authorization', 'Basic 0JDQtNC80LjQvdC40YHRgtGA0LDRgtC+0YA6aGpkdGg=');
	xhr.setRequestHeader('Content-type', 'application/json; charset=utf-8');

	xhr.onreadystatechange = function() {
		alert('запрос отправлен' + this.responseText)
	};
	xhr.onreadystatechange = function () {
		if (this.readyState == 4 && this.status == 200) {
			fillTable(this);
		};
    };
	xhr.onerror = function() { alert('Ошибка при получении цен и остатков: ' + this.status ); }

	// Отсылаем объект в формате JSON и с Content-Type application/json
	// Сервер должен уметь такой Content-Type принимать и раскодировать
	xhr.send(json);

}

function sendOrderTT() {
	
	// Блокируем окно на время отправки заказа
	document.getElementById("veil").style.display = 'grid';
	messageWindowElem = document.getElementById("message_window");
	messageWindowElem.style.display = 'grid';
	messageWindowElem.firstElementChild.innerHTML = '<p>Отправляем заказ...</p>';
	buttonOKElem = messageWindowElem.querySelector('input');
	buttonOKElem.disabled = true;
	buttonOKElem.classList.add('button_dis');
	buttonOKElem.classList.remove('button');

	// Создание запроса с данными
	// С поддержкой старых браузеров:
	var xhrObj = ("onload" in new XMLHttpRequest()) ? XMLHttpRequest : XDomainRequest;
	var xhr = new xhrObj();
	// Без поддержки старых браузеров:
	// var xhr = new XMLHttpRequest();
	xhr.open("GET", 'sendorder.php', true);
	//xhr.setRequestHeader('Authorization', 'Basic 0JDQtNC80LjQvdC40YHRgtGA0LDRgtC+0YA6aGpkdGg=');
	xhr.setRequestHeader('Content-type', 'application/json; charset=utf-8');

	xhr.onreadystatechange = function () {
		if (this.readyState == 4 && this.status == 200) {
			//fillTable(this);
			//alert('корзина обновлена 1' + this.responseText);
			//orderHTML = '<p>d</p>';
			//document.getElementById("window").innerHTML = orderHTML;
			//document.getElementById("window").innerHTML = this.responseText;
			console.log(document.getElementById("message_window").firstChildElement);
			document.getElementById("message_window").firstElementChild.innerHTML = this.responseText;
			buttonOKElem.disabled = false;
			buttonOKElem.classList.add('button');
			buttonOKElem.classList.remove('button_dis');
			//document.getElementById("message_window").style.display = 'grid';
		};
	};
	xhr.onerror = function() { alert('Ошибка при отправке заказа: ' + this.status ); }

	// Отсылаем запрос
	xhr.send();
}
function sendOrderTP(orderGUID) {

	//получим комментарий
	comment = document.querySelector('#comment_tp'); //new
	if (comment != null){
		comment = comment.value;
		console.log('comment - '+comment);
	}

	
	// Блокируем окно на время отправки заказа
	document.getElementById("veil").style.display = 'grid';
	
	// Вывод окна о состоянии отправки подтверждения заказа
	messageWindowElem = document.getElementById("message_window");
	messageWindowElem.style.display = 'grid';
	messageWindowElem.firstElementChild.innerHTML = '<p>Отправляем заказ...</p>';
	// Отключение кнопки "ОК" до получения результата
	buttonOKElem = messageWindowElem.querySelector('input');
	buttonOKElem.disabled = true;
	buttonOKElem.classList.add('button_dis');
	buttonOKElem.classList.remove('button');

	// Создание запроса с данными
	// С поддержкой старых браузеров:
	var xhrObj = ("onload" in new XMLHttpRequest()) ? XMLHttpRequest : XDomainRequest;
	var xhr = new xhrObj();
	// Без поддержки старых браузеров:
	// var xhr = new XMLHttpRequest();
	xhr.open("GET", 'sendorder.php?orderGUID='+orderGUID+'&comment='+comment, true);                 //new
	//xhr.setRequestHeader('Authorization', 'Basic 0JDQtNC80LjQvdC40YHRgtGA0LDRgtC+0YA6aGpkdGg=');
	xhr.setRequestHeader('Content-type', 'application/json; charset=utf-8');
	xhr.onreadystatechange = function () {
		if (this.readyState == 4 && this.status == 200) {
			// Выводим ответ с информацией о принятии заказа на стороне 1С
			document.getElementById("message_window").firstElementChild.innerHTML = this.responseText;
			// Ответ получен - включаем кнопу "ОК"
			buttonOKElem.disabled = false;
			buttonOKElem.classList.add('button');
			buttonOKElem.classList.remove('button_dis');
		};
	};
	xhr.onerror = function() { alert('Ошибка при отправке подтверждённого заказа: ' + this.status ); }
	// Отсылаем запрос
	xhr.send();
}

function sendOrderTP2(orderGUID) {

	event.preventDefault();
	
	// Блокируем окно на время отправки заказа
	document.getElementById("veil").style.display = 'grid';
	
	// Вывод окна о состоянии отправки подтверждения заказа
	messageWindowElem = document.getElementById("message_window");
	messageWindowElem.style.display = 'grid';
	messageWindowElem.firstElementChild.innerHTML = '<p>Отправляем заказ...</p>';
	// Отключение кнопки "ОК" до получения результата
	buttonOKElem = messageWindowElem.querySelector('input');
	buttonOKElem.disabled = true;
	buttonOKElem.classList.add('button_dis');
	buttonOKElem.classList.remove('button');
	

	// Создание запроса с данными
	// С поддержкой старых браузеров:
	var xhrObj = ("onload" in new XMLHttpRequest()) ? XMLHttpRequest : XDomainRequest;
	var xhr = new xhrObj();
	// Без поддержки старых браузеров:
	// var xhr = new XMLHttpRequest();
	xhr.open("GET", 'sendorder2.php?orderGUID='+orderGUID, true);
	//xhr.setRequestHeader('Authorization', 'Basic 0JDQtNC80LjQvdC40YHRgtGA0LDRgtC+0YA6aGpkdGg=');
	xhr.setRequestHeader('Content-type', 'application/json; charset=utf-8');
	xhr.onreadystatechange = function () {
		if (this.readyState == 4 && this.status == 200) {
			/* 
			//Для отслеживания запросов
			$.ajax({
				type : 'POST',
			    url: "sendorder2.php?orderGUID="+orderGUID,
			    data: {},
			    success: function(data){
			    /window.location = "tp.php";
			    	//document.getElementById("orders_list").textContent = '';
		    		//document.getElementById("orders_list").textContent = data;
			    }
			});
			*/

			// Выводим ответ с информацией о принятии заказа на стороне 1С
			document.getElementById("message_window").firstElementChild.innerHTML = this.responseText;
			// Ответ получен - включаем кнопу "ОК"
			buttonOKElem.disabled = false;
			buttonOKElem.classList.add('button');
			buttonOKElem.classList.remove('button_dis');
			

		};
	};
	xhr.onerror = function() { alert('Ошибка при отправке подтверждённого заказа: ' + this.status ); }
	// Отсылаем запрос
	xhr.send();

}


// Убрать/установить отбор только заказанных продуктов
function hideRows(element) {

	//elemInput = element.querySelector('input');
	elemInput = element.querySelector('button');

	event.preventDefault();

	//currentProductGroup = document.querySelector('#current_parent_GUID').textContent; //определяем категорию

	//allHiddenRows = document.querySelectorAll('#order_table .hidden_row');
	allHiddenRows = document.querySelectorAll('#order_table .in_order,.hidden_row'); // in_order

	// Если уже есть скрытые, значит текущее действие - открыть все (убрать отбор только заказанных)
	if (elemInput.value == '+' ) {

		console.log('+');

		for (var i = 1; i <= allHiddenRows.length; i++) {
			elem = allHiddenRows[i-1];
			elem.classList.remove('hidden_row'); //открываем все скрытые элементы
		}
		
		//для тт
		if (strLoc.indexOf('search') > -1){
			console.log('search!');
			OrderHiddenRows = document.querySelectorAll('#order_table .in_order,.order');
			for (var i = 1; i <= allRows.length; i++) {
				elem = allRows[i-1];
				if(elem.classList.contains('order')) {
					elem.classList.add('hidden_row');
					//console.log(i + ' no order');
				}
			}
		}

		//для тп
		if (strLoc.indexOf('parentGUID=none') > -1){
			if (strLoc.indexOf('tp.php')){
				console.log('tp.php. parentGUID=none');
				OrderHiddenRows = document.querySelectorAll('#order_table .in_order,.order');  
				for (var i = 1; i <= allRows.length; i++) {
					elem = allRows[i-1];
					if(elem.classList.contains('order')) {
						elem.classList.add('hidden_row');
					}
				}
			}
		}


		//надо переделывать логику - тт
		if ((strLoc.indexOf('showall') > -1)&&(strLoc.indexOf('tt.php') > -1)){
			console.log('kuku!');
			OrderHiddenRows = document.querySelectorAll('#order_table .in_order,.order'); 
			for (var i = 1; i <= allRows.length; i++) {
				elem = allRows[i-1];
				if(elem.classList.contains('order')) {
					elem.classList.add('hidden_row');
					//console.log(i + ' no order');
				}
			}
		}

		elemInput.value = '-';	

	// Иначе действие - скрыть все не заказанные продукты
	//показать заказанные
	} else {
		console.log('-');
	
		allRows = document.querySelectorAll('#order_table .journal_row');
		for (var i = 1; i <= allRows.length; i++) {
			elem = allRows[i-1];

			if(!elem.classList.contains('in_order')) {
				elem.classList.add('hidden_row');
			}
			/*--- NEW ----*/
			if(elem.classList.contains('in_order')) {
				elem.classList.remove('hidden_row');
			}
			/**/

		}

		strLoc = location.toString();

		
		// Если нечего было скрывать (нет незаказанных позиций, то просто поменять значок)
		if (elemInput.value == '-') {
			elemInput.value = '+';
		} else {
			elemInput.value = '-';
		}
	}	

	setPage(1);

	strLoc = location.toString();
	j = strLoc.indexOf('tp.php'); //для тп
	if (j>0) {
		hide_cursor();
	}
		
	closeHistoryOrders();

}

function closeHistoryOrders(){
	order_table = document.querySelector('#order_table');
	//console.log(order_table);
	list_order = document.querySelector('#list_order');
	//console.log(list_order);
	if(order_table.classList.contains('data')) {
		for (i = 0; i < container.childNodes.length; i++) {
			if (container.childNodes[i].tagName == 'DIV'){
				//console.log(container.childNodes[i]);
				elem = container.childNodes[i];
				elem.classList.remove('data');
			}
		}
		//order_table.classList.remove('data');
		//order_table.classList.add('margin');
		list_order.classList.add('data');
	} else{
		//console.log('no data');
	}
}

function countOpenedOrders(){
	array = new Array();
	listRows = document.querySelectorAll('#history_list_orders .list_row');
	for (var j = 1; j <= listRows.length; j++) {
		row = listRows[j-1];
		if (row != null){
			if (!row.classList.contains("hidden_list_row")){
				//console.log(row);
				array.push(row);
			}
		}
	}
	return array;

}

// Переключение страниц в истории заказов
function setPage_history(pageNumber) {

	array = new Array();
	array = countOpenedOrders();
	//console.log(array.length);

	strLoc = location.toString();
	//console.log('location' + strLoc);

	if (event != null){
		event.preventDefault();
	}

	allRows = document.querySelectorAll('#history_list_orders .panel');

	startIndex = (pageNumber - 1) * ROWS_ON_PAGE;
	endIndex = startIndex + ROWS_ON_PAGE - 1;
	numInProductGroup = 0;

	//panel
	//скрываем товары в заказе
	for (var i = 1; i <= allRows.length; i++) {
			elemRow = allRows[i-1];
			if (numInProductGroup >= startIndex && numInProductGroup <= endIndex) {
				elemRow.classList.remove("hide_on_page");

				sibling = elemRow.nextSibling;
				//показываем все дочерние элемент
				while((sibling != null)&&(sibling.tagName == 'DIV')&&(sibling.classList.contains("list_row"))){		
					if(sibling.tagName != 'DIV'){
						break;
					}
					sibling.classList.remove("hide_on_page");
					//console.log("remove");
					sibling = sibling.nextSibling;
					//console.log(sibling);
				}
				
				numInProductGroup++;
			} else {
				
				elemRow.classList.add("hide_on_page");
				numInProductGroup++;
				
				//скрываем все дочерние элементы
				sibling = elemRow.nextSibling;

				while((sibling != null)&&(sibling.classList.contains("list_row"))){
					if(sibling.tagName != 'DIV'){
						break;
					}
					sibling.classList.add("hide_on_page");
					//console.log("add");
					sibling = sibling.nextSibling;
					if(sibling !=null){
						if(sibling.tagName != 'DIV'){
							break;
						}
					}
					//console.log(sibling);
				}
			}
	}



	// Меняем строку адреса
	strLoc = location.toString();
	i = 0;
	i = strLoc.indexOf('?page=');
	//console.log(strLoc.slice(i+7, strLoc.length));
	if (i>0) {
		strLoc = strLoc.slice(0, i) + '?page=' + pageNumber + strLoc.slice(i+7, strLoc.length);
			//console.log(strLoc);
 	} else{
		strLoc = strLoc + '?page=' + pageNumber + '&parentGUID=' + currentProductGroup;
 	}

	
	maxPage = Math.ceil(numInProductGroup/ROWS_ON_PAGE);

	//console.log('numInProductGroup ' +numInProductGroup);
	//console.log('maxPage ' +maxPage);

	// Подкрашиваем выбранную страницу и скрываем ненужные
	allPages = document.querySelectorAll('.pages > a');
	//console.log('allPages.length ' + allPages.length);
	for (var i = 1; i <= allPages.length; i++) {
		elemPage = allPages[i-1];
		numP = Number(elemPage.textContent);
		if (numP <= maxPage && maxPage > 1) {
			elemPage.style.display='inline';
		} else {
			elemPage.style.display='none';
		}

		if (elemPage.textContent == pageNumber) {
			elemPage.style.color = 'black';
			elemPage.style.fontWeight = 'bold';
			elemPage.style.background = 'linear-gradient(91.65deg, #AFFC38 2.43%, #F6FD41 100%)';
		} else {
			elemPage.style.color = 'white';
			elemPage.style.fontWeight = 'regular';
			elemPage.style.background = '#5B5B5B';
		}
	}
	
	history.pushState(null, null, strLoc);

	window.scrollTo(pageXOffset, 0);


}

//заказы при поиске по своим страницам
function hide_list_orders(elemRow){ 
	//ищем предудущую карточку заказа
	sibling = elemRow.previousSibling;
	flag = 'null';

	//console.log(sibling);
	//ищем вверх, пока не найдем панель - ели много товаров в заказе
	while((sibling != null)&&(!sibling.classList.contains("panel"))){
		if(sibling.tagName != 'DIV'){
			break;
			flag = 0;
		}
		sibling = sibling.nextSibling;
		if(sibling != null){
			flag = 1;
		} else{
			flag = 'null';
		}
	}

	//если товар всего один
	if(sibling != null){
		if(sibling.classList.contains("panel")){ 
			flag = 1;}
	}
	

	//console.log('flag - '+flag);
	//скрыть товары у скрытых заказов
	if(flag == 1){
		if (sibling.classList.contains("panel")&&(sibling.classList.contains("hide_on_page"))){
		//console.log(sibling);
		//console.log('hide_list_orders');
		elemRow.classList.add('hide_on_page');
	}
	}	
}

// Переключение страниц ttorder и tporder
function setPage(pageNumber) {
	
	strLoc = location.toString();
	//console.log('location' + strLoc);

	if (event != null){
		event.preventDefault();
	}
	
	// Узнаём состояние вкл или откл отбор - после нажатия
	elemhHideRowsBtnForm = document.querySelector('#hiderowsbtnform');
	//elemhHideRowsBtn = elemhHideRowsBtnForm.querySelector('input');
	elemhHideRowsBtn = elemhHideRowsBtnForm.querySelector('button');
	console.log(elemhHideRowsBtn);
	/*
	if (elemhHideRowsBtnForm !== null){
		elemhHideRowsBtn = elemhHideRowsBtnForm.querySelector('input');
	}
	*/
	if (elemhHideRowsBtn.value == '+') {
		selectionOFF = false; //показываются заказанные
	} else {
		selectionOFF = true; //показываются все
	}
	//console.log('selectionOFF ' +selectionOFF);


	// Получаем текущую группу номенклатуры
	if(document.querySelector('#current_parent_GUID') !=null ){
			currentProductGroup = document.querySelector('#current_parent_GUID').textContent;
	} else{
		currentProductGroup = 'none';
	}
	//console.log('currentProductGroup ' +currentProductGroup);

	// Отбираем только те элементы продуктов, которые не скрыты кнопкой отбора только заказанных позиций
	//массив не скрытых корзиной позиций
	allRows = document.querySelectorAll('#order_table .journal_row');
	//console.log(allRows[0]);
	let arrOfRows = new Array(); //массив не срытых кнопкой отбора строк
	for (var i = 1; i <= allRows.length; i++) {
		elem = allRows[i-1];
		if (!elem.classList.contains('hidden_row')) {
			arrOfRows.push(elem);
		}
	}

	// Устанавливаем видимость элементов продуктов
	//console.log(arrOfRows[0]);
	startIndex = (pageNumber - 1) * ROWS_ON_PAGE;
	endIndex = startIndex + ROWS_ON_PAGE - 1;
	numInProductGroup = 0;

	//console.log('arrOfRows.length ' +arrOfRows.length);
	//console.log('startIndex ' +startIndex);
	//console.log('endIndex ' +endIndex);

	//Перебираем по порядку, и если соответствуют интервалу внутри индексов - показываем

	strLoc = location.toString();
	i = 0;
	//если поиск
	i = strLoc.indexOf('&search=');
	if (i>0) {
		for (var i = 1; i <= arrOfRows.length; i++) {
			elemRow = arrOfRows[i-1];
			if (numInProductGroup >= startIndex && numInProductGroup <= endIndex) {
				elemRow.classList.remove("hide_on_page");
				numInProductGroup++;
			} else {
				elemRow.classList.add("hide_on_page");
				numInProductGroup++;
			}
		}
		strLoc = location.toString();
		i = 0;
		i = strLoc.indexOf('?page=');
		if (i>0) {
			if(strLoc.indexOf('showall') > -1) {
				strLoc = strLoc.slice(0, i) + '?page=' + pageNumber + '&parentGUID=' + currentProductGroup+ '&showall=true';
			}
			else{
				strLoc = strLoc.slice(0, i) + '?page=' + pageNumber + '&parentGUID=' + currentProductGroup;	
			}
		} else {
			strLoc = strLoc + '?page=' + pageNumber + '&parentGUID=' + currentProductGroup;
		};
		strLoc = strLoc + '&search=true';
	}
	//конец если поиск
	else{

		if (strLoc.indexOf('&showAllMatrix=') > 0){
			console.log('showAllMatrix!');
			for (var i = 1; i <= arrOfRows.length; i++) {
			elemRow = arrOfRows[i-1];
			if (numInProductGroup >= startIndex && numInProductGroup <= endIndex) {
					elemRow.classList.remove("hide_on_page");
					numInProductGroup++;
				} else {
					elemRow.classList.add("hide_on_page");
					numInProductGroup++;
				}
			}
		}
		else if(strLoc.indexOf('parentGUID=none') > 0){ 
			console.log('parentGUID=none!');
			for (var i = 1; i <= arrOfRows.length; i++) {
			elemRow = arrOfRows[i-1];
			if (numInProductGroup >= startIndex && numInProductGroup <= endIndex) {
					elemRow.classList.remove("hide_on_page");
					numInProductGroup++;
				} else {
					elemRow.classList.add("hide_on_page");
					//console.log('hide1!');
					numInProductGroup++;
				}
			}
			console.log('elemRow - '+elemRow);
		}
		else{
			console.log('parentGUID != none');
			for (var i = 1; i <= arrOfRows.length; i++) {
				elemRow = arrOfRows[i-1];
				elemParentGUID = elemRow.querySelector('#parent_guid').textContent;
				if (elemParentGUID != currentProductGroup && selectionOFF) {//если элемент не в текущей группе и показываются все товары
					elemRow.classList.add("hide_on_page"); //скрыть его
					//console.log('hide2!');
				} else {
					if (numInProductGroup >= startIndex && numInProductGroup <= endIndex) {
						elemRow.classList.remove("hide_on_page");
						//console.log('hide3!');
						numInProductGroup++;
					} else {
						elemRow.classList.add("hide_on_page");
						//console.log('hide4!');
						numInProductGroup++;
					}
				}
			}
		}

		

		// Меняем строку адреса
		strLoc = location.toString();
		i = 0;
		i = strLoc.indexOf('?page=');
		if (i>0) {
			if(strLoc.indexOf('showall') > -1) {
				strLoc = strLoc.slice(0, i) + '?page=' + pageNumber + '&parentGUID=' + currentProductGroup+ '&showall=true';
				//console.log(strLoc);
			}
			else{
				strLoc = strLoc.slice(0, i) + '?page=' + pageNumber + '&parentGUID=' + currentProductGroup;	
			}
		} else {
			strLoc = strLoc + '?page=' + pageNumber + '&parentGUID=' + currentProductGroup;
		};
	}
	
	maxPage = Math.ceil(numInProductGroup/ROWS_ON_PAGE);

	console.log('numInProductGroup ' +numInProductGroup);
	console.log('maxPage ' +maxPage);

	//видимый диапазон страниц
	startPage = Math.floor((pageNumber-1)/10); 
	//console.log(startPage);

	
	// Подкрашиваем выбранную страницу и скрываем ненужные
	allPages = document.querySelectorAll('.pages > a');
	//console.log('allPages.length ' + allPages.length);
	for (var i = 1; i <= allPages.length; i++) {
		elemPage = allPages[i-1];
		//console.log(elemPage);
		numP = Number(elemPage.textContent);
		//console.log(numP+' - ');
		if (numP <= maxPage && maxPage > 1) {
			elemPage.style.display='inline';
		} else {
			elemPage.style.display='none';
		}

		
		j = strLoc.indexOf('tp.php'); //для тп
		if (j>0) {
			//для тех строк, которые показаны, скрыть вне дипазона
			if(elemPage.style.display=='inline'){
				//console.log(numP+' - '+Math.floor(numP/10)+' - '+startPage);
				if (Math.floor((numP-1)/10) != startPage){
					elemPage.style.display='none';
				}
			}
			
			//показываем стрелки в зависимости от числа страниц
			if(elemPage.textContent == '>' || elemPage.textContent=='<'){
				if (maxPage > 10){
					elemPage.style.display='inline';
				} else{
					elemPage.style.display='none';
				}
			}
		}
		
		

		if (elemPage.textContent == pageNumber) {
			elemPage.style.color = 'black';
			elemPage.style.fontWeight = 'bold';
			elemPage.style.background = 'linear-gradient(91.65deg, #AFFC38 2.43%, #F6FD41 100%)';
		} else {
			elemPage.style.color = 'white';
			elemPage.style.fontWeight = 'regular';
			elemPage.style.background = '#5B5B5B';
		}
	}


	j = strLoc.indexOf('tp.php'); //для тп
	if (j>0) {
		cursor(); //показ стрелочек
	}
		
	history.pushState(null, null, strLoc);

	window.scrollTo(pageXOffset, 0);
}


function setProductGroup2(element) {

	// Параметры запроса
	elem = element.parentNode;

	if (elem.querySelector('#guid')){
		guid = elem.querySelector('#guid').textContent;
		flag_maxrtix = true;
		console.log(flag_maxrtix);
	} else {
		if (elem.querySelector('#guid_all')){
			guid = elem.querySelector('#guid_all').textContent;
			flag_maxrtix = false;
			console.log(flag_maxrtix);
		}
	}

	console.log(guid);
	onlyBody = true;

	// Параметры зпроса
	if (document.querySelector('#order_GUID') !== null){	
		orderGUID = document.querySelector('#order_GUID').textContent;
		console.log('orderGUID' + orderGUID);
	}

	//href="?page1?page=1&parentGUID='. guid. '&showall=true"
	
	// Отправка запроса с данными на tporder.php(orderGUID, onlyBody):
	
	// Инициализация запроса
	// С поддержкой старых браузеров:
	//Мы создаём XMLHttpRequest и проверяем, поддерживает ли он событие onload. 
	//Если нет, то это старый XMLHttpRequest, значит это IE8,9, 
	//и используем XDomainRequest.
	var xhrObj = ("onload" in new XMLHttpRequest()) ? XMLHttpRequest : XDomainRequest;
	var xhr = new xhrObj();
	// Без поддержки старых браузеров:
	// var xhr = new XMLHttpRequest();
	if (flag_maxrtix){
		xhr.open("GET", 'showOfMatrix.php?orderGUID='+orderGUID+'&parentGUID=' + guid, true);
	} else {
		xhr.open("GET", 'showAll.php?orderGUID='+orderGUID+'&parentGUID=' + guid + '&showall=true', true);
	}
	
	//xhr.open("GET", 'tporder.php?page1?page=1&parentGUID='+guid, true);
	//xhr.setRequestHeader('Authorization', '');
	xhr.setRequestHeader('Content-type', 'application/json; charset=utf-8');
	xhr.onreadystatechange = function () {
		if (this.readyState == 4 && this.status == 200) {
			// Вставляем результат в окно
			document.getElementById("window").innerHTML = this.responseText;
			// Скрываем список заказов
			//document.getElementById("order_table").innerHTML = '';


			document.getElementById("accordion").classList.remove('show');
			document.getElementById("accordion2").classList.remove('show');
			// Запрос остатков и цен
			complete();
			// Дозагрузка картинок
			setLazy();
			lazyLoad();
			// Разблокируем окно
		};
	};
	xhr.onerror = function() {
		alert('Ошибка при получении цен и остатков: ' + this.status );
		// Разблокируем окно
	}
	// Отправляем запрос
	xhr.send();


	// Меняем строку адреса
	strLoc = location.toString();
	i = 0;
	i = strLoc.indexOf('?page=');
	if (i>0) {
		j = strLoc.indexOf('&parentGUID=');
		subStr = strLoc.substring(j+12); //parentGUID
		newstr = subStr.replace(subStr, guid);
		console.log('newstr ' + newstr);

		strLoc0 = strLoc.slice(0, j+12); //разделить строку
		console.log('strLoc0 ' + strLoc0);
		strLoc = strLoc0 + newstr;
		console.log('strLoc1 ' + strLoc);

	} else {
		strLoc = strLoc + '?page=1' +'&parentGUID=' + guid;
		console.log('strLoc2 ' + strLoc);
	}

	if (!flag_maxrtix){
		strLoc = strLoc + '&showall=true';
	}
	history.pushState(null, null, strLoc);
	
	document.getElementById("current_parent_GUID").textContent = guid;
}


// Переключение страниц ttorder и tporder
function setProductGroup(element) {
	
	//event.preventDefault();
	// Куда мы хотим прыгнуть 
	newParentGUID = element.querySelector('#guid').textContent;
	// Где мы сейчас находимся
	currentParentGUID = document.querySelector('#current_parent_GUID').textContent;
	// Страницу будем сбрасывать на первую при навигации по группам
	currentPageNumber = 1; 

	// Устанавливаем видимость всех элементов самих продуктовых групп
	allRowsProductGroup = document.querySelectorAll('#order_table .product_group_row');
	for (var i = 2; i <= allRowsProductGroup.length; i++) {
		elemRow = allRowsProductGroup[i-1];
		elemParentGUID = elemRow.querySelector('#parent_guid').textContent;
		// Для всех элементов, кроме первого, условие вывода - родитель элемента равен новому выбранному родителю (куда мы хотим прыгнуть)
		if (elemParentGUID != newParentGUID) {
			elemRow.classList.add("hide_on_page");
		} else {
			elemRow.classList.remove("hide_on_page");
		}
		// Запоминаем родителя того уровня, куда мы хотим прыгнуть (если это переход на уровень выше)
		elemGUID = elemRow.querySelector('#guid').textContent;
		if (elemGUID == newParentGUID) {
			//console.log('1');
			newParentGUIDParent = elemParentGUID;
			elemRow.classList.remove("hide_ageon_page");
			elemRow.classList.add("selected_group");
		}
		// Если это элемент из которого мы переходим и мы переходим не в этот же самый элемент, то нужно снять "выбраность элемента""
		if (elemGUID == currentParentGUID && newParentGUID != currentParentGUID) {
			//console.log('2');
			elemRow.classList.remove("selected_group");
		}
	}

	// Для первого элемента (перехода на уровень выше) - специальные условия
	elemRow = allRowsProductGroup[0];
	//console.log(elemRow);
	if (newParentGUID == '00000000-0000-0000-0000-000000000000') {
		elemRow.classList.add("hide_on_page");
	} else {
		elemRow.classList.remove("hide_on_page");
		// Если это условие верно, то мы спускаемся на уровень ниже (родитель нового текущего уровня равен текущему уровню (до его смены)
		if (newParentGUIDParent == currentParentGUID) {
			elemRow.querySelector('#guid').textContent = currentParentGUID;
		} else {
			elemRow.querySelector('#guid').textContent = newParentGUIDParent;
		}
	}

	// Отбираем только те элементы продуктов, которые не скрыты кнопкой отбора только заказанных позиций
	//console.log('pageNumber: '+pageNumber);
	allRows = document.querySelectorAll('#order_table .journal_row');
	//console.log(allRows[0]);
	let arrOfRows = new Array();
	for (var i = 1; i <= allRows.length; i++) {
		elem = allRows[i-1];
		if (!elem.classList.contains('hidden_row')) {
			arrOfRows.push(elem);
		}
	}

	// Устанавливаем видимость элементов продуктов
	//console.log(arrOfRows[0]);
	startIndex = (currentPageNumber - 1) * ROWS_ON_PAGE;
	endIndex = startIndex + ROWS_ON_PAGE - 1;
	numInProductGroup = 0;
	for (var i = 1; i <= arrOfRows.length; i++) {
		elemRow = arrOfRows[i-1];
		elemParentGUID = elemRow.querySelector('#parent_guid').textContent;
		if (elemParentGUID != newParentGUID) {
			elemRow.classList.add("hide_on_page");
		} else {
			if (numInProductGroup >= startIndex && numInProductGroup <= endIndex) {
				elemRow.classList.remove("hide_on_page");
				numInProductGroup++;
			} else {
				elemRow.classList.add("hide_on_page");
				numInProductGroup++;
			}
		}
	}
	
	//console.log(numInProductGroup);

	maxPage = Math.ceil(numInProductGroup/ROWS_ON_PAGE);
	// Подкрашиваем выбранную страницу и скрываем ненужные
	allPages = document.querySelectorAll('.pages > a');
	for (var i = 1; i <= allPages.length; i++) {
		elemPage = allPages[i-1];
		numP = Number(elemPage.textContent);
		if (numP <= maxPage && maxPage > 1) {
			elemPage.style.display='inline';
		} else{
			elemPage.style.display='none';
		}
		if (elemPage.textContent == currentPageNumber) {
			elemPage.style.color = 'blue';
		} else {
			elemPage.style.color = '#f58220';
		}
	}
	
	// Меняем строку адреса
	strLoc = location.toString();
	i = 0;
	i = strLoc.indexOf('?page=');
	if (i>0) {
		strLoc = strLoc.slice(0, i) + '?page=' + currentPageNumber + '&parentGUID=' + newParentGUID;
	} else {
		strLoc = strLoc + '?page=' + currentPageNumber + '&parentGUID=' + newParentGUID;
	}
	//console.log(strLoc);
	//location.href = strLoc;
	//location.hash = strLoc;
	//location.reload(false);
	//location.search = strLoc;
	history.pushState(null, null, strLoc);

	elemCurrentParentGUID = document.querySelector('#current_parent_GUID');
	elemCurrentParentGUID.textContent = newParentGUID;
}

function addToOrderTT() {

	// 1. Собираем данные для отправки
	let arrOfProducts = new Array();
	for (var i = 1; i <= stringsWithProducts.length; i++) {
		stringWithProduct = stringsWithProducts[i-1];
		objProductGUID = stringWithProduct.querySelector('#guid');
		objProductQuantityToOrder = stringWithProduct.querySelector('input');
		if (objProductGUID != null) {
			productGUID = objProductGUID.textContent
			if (Boolean(objProductQuantityToOrder.value)) {
				productQuantity = objProductQuantityToOrder.value;
			} else {
				productQuantity = 0;
			}
			arrOfProducts.push({'productGUID': productGUID, 'productQuantity': productQuantity});
			console.log(productGUID + ' ' + productQuantity);
			//console.log(productNumInOrder);
		}
	}
	console.log( arrOfProducts );

	// 2. Отправка запроса с данными
	// С поддержкой старых браузеров:
	var xhrObj = ("onload" in new XMLHttpRequest()) ? XMLHttpRequest : XDomainRequest;
	var xhr = new xhrObj();
	// Без поддержки старых браузеров:
	// var xhr = new XMLHttpRequest();
	var json = JSON.stringify({
		idContract: contractGUID,
		userMatrix: arrOfProducts
	});

	//console.log( json );
	xhr.open("POST", 'add2order.php', true)
	//xhr.setRequestHeader('Authorization', 'Basic 0JDQtNC80LjQvdC40YHRgtGA0LDRgtC+0YA6aGpkdGg=');
	xhr.setRequestHeader('Content-type', 'application/json; charset=utf-8');

	/*xhr.onreadystatechange = function() {
		alert('запрос отправлен' + this.responseText)
	};*/
	xhr.onreadystatechange = function () {
		if (this.readyState == 4 && this.status == 200) {
			//fillTable(this);
			alert('корзина обновлена ' + this.responseText);
		};
	};
	xhr.onerror = function() { alert('Ошибка при получении цен и остатков: ' + this.status ); }

	// Отсылаем объект в формате JSON и с Content-Type application/json
	// Сервер должен уметь такой Content-Type принимать и раскодировать
	xhr.send(json);

}
function sincMatrix(elem) {
	
	// 1. Собираем данные для отправки
	let arrOfProducts = new Array();
	stringWithProduct = elem.parentNode.parentNode;
	
	contractGUID = document.querySelector('#contract_GUID');
	objProductGUID = stringWithProduct.querySelector('#guid');
	objProductQuantityToOrder = stringWithProduct.querySelector('input');
	//console.log(objProductQuantityToOrder);
	if (objProductGUID != null) {
		productGUID = objProductGUID.textContent;	
		if (Boolean(objProductQuantityToOrder.value)) { //конвертация в число
			objProductQuantityToOrder.value = objProductQuantityToOrder.value.replace(",","."); /*new*/
			//console.log('replace - '+objProductQuantityToOrder.value);
			objProductQuantityToOrder.value = parseNum(objProductQuantityToOrder.value);
			productQuantity = objProductQuantityToOrder.value;
		} else {
			productQuantity = 0;
		}

		//console.log('max_quantity ' + objProductQuantityToOrder.max);
		// Не давать ввести больше чем есть на остатке
		if (Number(productQuantity) > objProductQuantityToOrder.max) {
			objProductQuantityToOrder.value = objProductQuantityToOrder.max;
			productQuantity = objProductQuantityToOrder.max;
			console.log('Исправлено');	
			swal({
			    title: "Внимание!",
			    text: "Вы ввели больше, чем есть на остатке. Установлено максимальное количество.",
			    position: "bottom-end",
			    background: "white",
			    backdrop: false,
			    allowOutsideClick: true,
			    allowEscapeKey: false,
			    allowEnterKey: true,
			    showConfirmButton: true,
			    confirmButtonText: "ОК",
			    confirmButtonColor: "#5B5B5B",
			    showCancelButton: false,
			    timer: 3000,
			  });

		}

		if (Number(productQuantity) > 0) {
			//в зависимости от ед изм, округляем или оставляем значение
			//console.log(objProductQuantityToOrder.value);
			objProductQuantityToOrder.value = setQuantity(objProductQuantityToOrder);
			productQuantity = objProductQuantityToOrder.value;
			//console.log('кол-во ' + objProductQuantityToOrder.value);
		}

		//if (Number(productQuantity) != productQuantity) {}
		if (Number(productQuantity) > 0) {
			stringWithProduct.classList.add("in_order");
		} else {
			stringWithProduct.classList.remove("in_order");
		}
		
		//если изменяем количество - позиция записывается массив
		arrOfProducts.push({'productGUID': productGUID, 'productQuantity': productQuantity});
		console.log(arrOfProducts.length);
		console.log(productGUID + ' + ' + productQuantity);
	}

	// 2. Отправка запроса с данными
	// С поддержкой старых браузеров:
	var xhrObj = ("onload" in new XMLHttpRequest()) ? XMLHttpRequest : XDomainRequest;
	var xhr = new xhrObj();
	// Без поддержки старых браузеров:
	// var xhr = new XMLHttpRequest();
	var json = JSON.stringify({
		idContract: contractGUID,
		userMatrix: arrOfProducts
	});

	//console.log( json );
	xhr.open("POST", 'add2order.php', true)
	//xhr.setRequestHeader('Authorization', 'Basic 0JDQtNC80LjQvdC40YHRgtGA0LDRgtC+0YA6aGpkdGg=');
	xhr.setRequestHeader('Content-type', 'application/json; charset=utf-8');

	/*xhr.onreadystatechange = function() {
		alert('запрос отправлен' + this.responseText)
	};*/
	xhr.onreadystatechange = function () {
		if (this.readyState == 4 && this.status == 200) {
			updOverall();
			//fillTable(this);
			//alert('корзина обновлена ' + this.responseText);
		};
	};
	xhr.onerror = function() { alert('Ошибка при добавлении товара в корзину: ' + this.status ); }

	// Отсылаем объект в формате JSON и с Content-Type application/json
	// Сервер должен уметь такой Content-Type принимать и раскодировать
	xhr.send(json);



}


function onChangeQuantity(elem) {
	console.log("Это ОНО!");
	form = elem.parentNode;
	measure = form.previousSibling;
	multiplElem = measure.previousSibling;
	multipl = Number(multiplElem.textContent);
	inputValue = Number(elem.value);
	inputValueStr = elem.value;
	inputValueLastChar = elem.value.charCodeAt(elem.value.length - 1);
	if (multipl != 0) {
	    console.log("введено " + inputValueStr);
	    col = Math.trunc(inputValue / multipl);
	    newValue = multipl * col;
	    if (newValue != inputValue) {
	        newValue = multipl * (col + 1);
	    }
	    if (inputValueLastChar == 46) {
	        newValue = newValue + '.';
	    }
		console.log("исправлено " + newValue);
		elem.value = newValue;
		sincMatrix(elem);
	}
}

function setQuantity(elem){

	form = elem.parentNode;
	measure = form.previousSibling;

	if ((measure.textContent == 'шт')||(measure.textContent == 'уп')){
		console.log('шт/уп');
		return Math.round(elem.value);
	} else if(measure.textContent == 'БЛК'){
		console.log('БЛК');
		return Math.round(elem.value);
	} else if (measure.textContent == 'кг') {
	    return elem.value;
	}
		// Убрал старое округление, теперь работает новое
//		else if (measure.textContent == 'кг') {
//			console.log('Ввели - '+ elem.value);
//			n = parseFloat(elem.value);
//			n1 = n.toFixed(2);
			//console.log('Окгруглили  -'+n); 
//			if (n1 % 0.5 == 0){
//			  console.log('Кратно!')
//			  n = elem.value;
//			} else{
				//console.log('Не кратно!');
//				n2 = parseFloat(n1).toFixed(1);
//				if (n2 % 0.5 == 0){
				  //console.log('Кратно!')
//				  n = n2;
//				}
//				else {
					//console.log('Не кратно!');
//					n2 = parseFloat(n1).toFixed(1);
//					if (n2 % 0.5 == 0){
					  //console.log('Кратно2!')
//					  n = n2;
//					}
//					else {
						//console.log('Не кратно2!');
//						a = n2 % 0.5;
//						a = a.toFixed(1);
//						b = ~~(n2 / 0.5);
						//console.log(a + ' - ' + b);
//						if (a ==  0.1 || a == 0.2){
//							n = b*0.5;
//						}
//						else {
//							n = (b+1)*0.5;
//						}
//					}  
//				}
//			}  
			//console.log('n - ' + n);
//			 return n;
//		}
}

function sincOrder(elem) {
	
	// 1. Собираем данные для отправки
	let arrOfProducts = new Array();
	stringWithProduct = elem.parentNode.parentNode;

	console.log('1 '+orderGUID);
	//console.log('Строка с продуктом:');
	//console.log(stringWithProduct);

	objProductGUID = stringWithProduct.querySelector('#guid');
	objProductQuantityToOrder = stringWithProduct.querySelector('input');
	console.log(objProductQuantityToOrder);
	if (objProductGUID != null) {
		productGUID = objProductGUID.textContent
		if (Boolean(objProductQuantityToOrder.value)) { //конвертация в число
			objProductQuantityToOrder.value = parseNum(objProductQuantityToOrder.value);
			productQuantity = objProductQuantityToOrder.value;
		} else {
			productQuantity = 0;
		}
		// Не давать ввести больше чем есть на остатке
		if (Number(productQuantity) > objProductQuantityToOrder.max) {
			objProductQuantityToOrder.value = objProductQuantityToOrder.max;
			productQuantity = objProductQuantityToOrder.max;
			console.log('Исправлено');
		}
		//if (Number(productQuantity) != productQuantity) {}
		if (Number(productQuantity) > 0) {
			stringWithProduct.classList.add("in_order");
		} else {
			stringWithProduct.classList.remove("in_order");
		}
		
		//если изменяем количество - позиция записывается массив
		arrOfProducts.push({'productGUID': productGUID, 'productQuantity': productQuantity});
		console.log(arrOfProducts.length);
		console.log(productGUID + ' + ' + productQuantity);
	}


	// 2. Отправка запроса с данными
	// С поддержкой старых браузеров:
	var xhrObj = ("onload" in new XMLHttpRequest()) ? XMLHttpRequest : XDomainRequest;
	var xhr = new xhrObj();
	// Без поддержки старых браузеров:
	// var xhr = new XMLHttpRequest();
	var json = JSON.stringify({
		idContract: contractGUID,
		userMatrix: arrOfProducts
	});



//ПРОВЕРИТь параметр
/*
	orderGUID = orderGUID.replace('"', '');
	console.log('2 '+orderGUID);
	*/

	//console.log( json );
	xhr.open("POST", 'add2order2.php', true)
	//xhr.setRequestHeader('Authorization', 'Basic 0JDQtNC80LjQvdC40YHRgtGA0LDRgtC+0YA6aGpkdGg=');
	xhr.setRequestHeader('Content-type', 'application/json; charset=utf-8');

	/*xhr.onreadystatechange = function() {
		alert('запрос отправлен' + this.responseText)
	};*/
	xhr.onreadystatechange = function () {
		if (this.readyState == 4 && this.status == 200) {
			updOverall();
			console.log('yes!');
			//fillTable(this);
			//alert('корзина обновлена ' + this.responseText);
		};
	};
	xhr.onerror = function() { alert('Ошибка при добавлении товара в корзину: ' + this.status ); }

	// Отсылаем объект в формате JSON и с Content-Type application/json
	// Сервер должен уметь такой Content-Type принимать и раскодировать
	xhr.send(json);

}

function hasOrder() {
	arrayInOrder = document.querySelectorAll('#order_table .in_order');

	formSend = document.querySelector('#order_table .sendformcont');

	if (arrayInOrder.length > 0){
		formSend.style.display = 'grid';
		console.log('grid!');
	} else{
		formSend.style.display = 'none';
		console.log('none!');
	}
}


// Функция контроля ввода количества (не работает на андроид)
function onlyNumber(elem) {
	
	
	//console.log(event);
	//console.log(elem.value);
	//console.log(event.target);
	//console.log(event.key);
	//console.log(event.keyCode);
	//console.log(event.charCode);
	//console.log(event.code);
	//console.log(event.originalEvent);
	//console.log(event.originalEvent.key);
	//console.log(event.which);
	//if (/^[А-Яа-яA-Za-z ]$/.test(event.key)) {
	if (event.keyCode == 116 || event.keyCode == 8 || event.keyCode == 37 || event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 9) {
		//return;
		return true;
	}
	
    if (elem.value.match(/[\.]/) || elem.value.match(/[\,]/)) {
	//if (elem.value.match(/[\.]/) || elem.value.match(/[\,]/) || elem.value == 0) {
		console.log('if');
		if (!(/^[0-9]$/.test(event.key))) {
			event.preventDefault();
			event.stopPropagation();
			return false;
		}
	} else {
		console.log('e');
		if (!(/^[0-9\.\,]$/.test(event.key))) {
			console.log('e2');
			event.preventDefault();
			event.stopPropagation();
			return false;
		}		
	}
	
	return true;
	
}

// Дополнительная функция для контроля ввода количества работающего на андроид
function parseNum(n) {
	
	// strip all whitespace before and after
	n = String(n).trim();
	// if number is '' or ' ' return empty string
	if (!n) return ''
	// remove everything except numbers and '.'
	n = n.replace(/[^0-9\.\,]+/g,"");
	//если количество точек больше 1, то оставляем только первую
	arrStr = n.split(".");
	if (arrStr.length > 2) {
		n = arrStr[0] + '.' + arrStr[1];
		for (var i = 2 ; i < arrStr.length; i++) {
			n = n + arrStr[i];
		}
	}
	return n;

}

ROWS_ON_PAGE = 50;
//ADDRESS_UT_API_CONST = 'http://192.168.95.229/FreshSCopy/hs/api';
//ADDRESS_UT_API_CONST = 'http://fresh.nvadm.ru/sskut/hs/api';
//ADDRESS_UT_API_CONST = 'http://fresh.nvadm.ru/TradeWork/hs/api';

elemWithUserGUID = document.querySelector('#user_GUID');
elemWithAccountGUID = document.querySelector('#account_GUID');
/*allElemsInput = document.querySelectorAll('input')
for (var i = 1; i <= allElemsInput.length; i++) {
	elemInput = allElemsInput[i-1];
	elemInput.onkeydown = function (e) { return !(/^[А-Яа-яA-Za-z ]$/.test(e.key)); } // IE > 9
}*/
//document.querySelectorAll('input').onkeydown = function (e) { return !(/^[А-Яа-яA-Za-z ]$/.test(e.key)); } // IE > 9

// при нажатии кнопки Esc будет запускаться функция обновления остатка и цены
document.onkeydown = null;
document.onkeydown = function(e) {
	if (e.keyCode == 27) { // escape
		complete();
		// Это для того чтобы браузер свои действия на нажатие клавиши не применял (в частности на Esc - отмена загрузки страницы) 
		return false;
	}
};

function history_orders(){
	container = document.querySelector('.container');
	for (i = 1; i < container.childNodes.length; i++) {
		if (container.childNodes[i].tagName == 'DIV'){
			if ((container.childNodes[i].id != 'goodbye')&&((container.childNodes[i].id != 'window'))){
				elem = container.childNodes[i];
	      		elem.classList.add("data");
			}
		}
    }

    t = document.querySelector('#header_tt').classList.contains('active');
    if (t){
    	document.querySelector('#header_tt').classList.remove('active');
    	document.querySelector('.header_burger').classList.remove('active');
    	document.getElementsByTagName('html')[0].style.overflow = 'scroll';
    }

    /////

    // Отменяем все остальные onclick
	event.preventDefault();
	event.stopPropagation();

	elemContractGUID = document.querySelector('#contract_GUID');
	b = elemContractGUID.getElementsByTagName("b");
	contractGUID = document.querySelector('#contract_GUID').textContent;
	
	console.log('contractGUID - '+contractGUID);

	if (b[b.length-1] != null){
		console.log('Ошибка1!');
		contractGUID = ' ';
	} 
	if (contractGUID == ''){
		console.log('Ошибка2!');
		contractGUID = ' ';
	} 

	// Инициализация запроса
	// С поддержкой старых браузеров:
	var xhrObj = ("onload" in new XMLHttpRequest()) ? XMLHttpRequest : XDomainRequest;
	var xhr = new xhrObj();
	// Без поддержки старых браузеров:
	// var xhr = new XMLHttpRequest();
	xhr.open("GET", 'historyOrders.php?contractGUID='+contractGUID, true);
	//xhr.setRequestHeader('Authorization', '');
	xhr.setRequestHeader('Content-type', 'text/html; charset=utf-8');
	xhr.onreadystatechange = function () {
		if (this.readyState == 4 && this.status == 200) {
			// Вставляем результат в окно
			console.log('32143234234');

			if (document.querySelector('.list_order') == null){
				//elemFloatWindow = document.getElementById("window");
				elem = document.querySelector('.container_tt');

				console.log(elem);
				div = document.createElement('div');

				parent = elem.parentNode;
				parent.insertBefore(div, elem);
				//document.insertBefore(div, elem);				
				div.id = "list_order";
				div.classList.add("list_order");
				div.innerHTML = this.responseText;



				//elemFloatWindow.style.transform = 'none';
				
				// Скрываем список заказов
				//document.getElementById("orders_list").style.display = 'none';
				// Запрос остатков и цен
				//complete();
				// Дозагрузка картинок
				//setLazy();
				//lazyLoad();
			} else{ //повторное открытие, без перезагрузки
				//чтобы не появлялся еще один
				document.querySelector('.list_order').classList.remove('data');
				//сворачиваем все заказы 
				list_rows = document.querySelectorAll('.list_row');
				for (var i = 1; i <= list_rows.length; i++) {
					list_row = list_rows[i-1];
					list_row.classList.add('hidden_list_row');
				}	
			}

			// Меняем строку адреса
			pageNumber = 1;
			strLoc = location.toString();
			i = 0;
			i = strLoc.indexOf('?page=');
			//console.log(strLoc.slice(i+7, strLoc.length));
			if (i>0) {
				strLoc = strLoc.slice(0, i) + '?page=' + pageNumber + strLoc.slice(i+7, strLoc.length);
					//console.log(strLoc);
		 	} else{
				strLoc = strLoc + '?page=' + pageNumber + '&parentGUID=' + currentProductGroup;
		 	}

			history.pushState(null, null, strLoc);

			
			
		} else if (this.status == 401) {
			console.log('Ошибка при открытии карточки продукта: ' + this.status);
			//alert('Ошибка при открытии карточки продукта: ' + this.status);

			//location.href = 'login.php';
		}
	};
	xhr.onerror = function () {
		console.log('2222222222jfdhdf');
		alert('Ошибка при открытии карточки продукта: ' + this.status);
		// Разблокируем окно
		//document.getElementById("veil").style.display = 'none';
	}
	// Отправляем запрос
	xhr.send();

}

function returnToShop(){
	window.location = "tt.php";
}

function printDate(elem){
	message = '';

	data2 = elem.parentNode;
	data2_c = data2.previousSibling;
	data2 = data2_c.querySelector('#date2');
	data2_v = data2.value;
	console.log(data2_v);

	data1_c = data2_c.previousSibling;
	data1 = data1_c.querySelector('#date1');
	data1_v = data1.value;
	console.log(data1_v);

	console.log(data1_v);
	console.log(data2_v);
	//if ((data1_v == '')||(data2_v == '')){
	//	message = message +  'Выбран некорректынй диапазон дат';
	//} //else{
		ValidateDate(data1);
		if (data1.classList.contains('date_ok')){
			message = '';
		} else{
			message = message + 'Некорректный ввод даты "C"</br>';
		}

		ValidateDate(data2);
		if (data2.classList.contains('date_ok')){
			console.log('data2 - date_ok');
		} else{
			message = message + 'Некорректный ввод даты "По"';
		}

		if (data2_v < data1_v){
			message = message + 'Некорректный ввод даты.</br>Пожалуйста, проверьте правильность</br>выбранного диапазона';
			console.log('Неверный порядок дат');
		} else{
			console.log('ok!');
		}
	//}

	console.log('message -'+message);

	if(message != ''){
		window_readonly = document.querySelector('.window_readonly');
		window_readonly.getElementsByTagName("p")[0].textContent = "";
		window_readonly.getElementsByTagName("p")[0].innerHTML = message ;
		window_readonly.classList.add('b-show');
	} else{

		data1_name = 'date1';
		data2_name =  'date2';
		arr = new Map([
		  [data1_name, data1_v],
		  [data2_name, data2_v]
		  ]);

		date1 = arr.get('date1');
		date1 = date1.replace('-', '');
		date1 = date1.replace('-', ''); 

		date2 = arr.get('date2');
		date2 = date2.toString().replace('-', '');
		date2 = date2.toString().replace('-', '');
		//console.log(date1);
		//console.log(date2);
		contractGUID = document.querySelector('#contract_GUID').textContent;
		userGUID = document.querySelector('#user_GUID').textContent;
		accountGUID = document.querySelector('#account_GUID').textContent;
		$.ajax({
	    	type : 'POST',
	        url: "historyOrders2.php",
	        data: {
	        	contractGUID: contractGUID,
	        	userGUID: userGUID,
	        	accountGUID: accountGUID,
	        	date1: date1,
	        	date2: date2
	        	
	        },
	        success: function(data){
	        	console.log('succes!!!');
	        	document.querySelector('.orders_container').textContent = '';
	        	data = data.toString();
	        	//data = data.substring(1, data.length-1);
	        	//document.querySelector('#history_list_orders').textContent = data.length;
	        	document.querySelector('.orders_container').innerHTML = data;
	        	inner = document.querySelector('#pages2').innerHTML;
	        	document.querySelector('#pages2').remove();
	        	document.querySelector('#pages').innerHTML = inner;
		    }
   	 	})
	}


	// Меняем строку адреса
	pageNumber = 1;
	strLoc = location.toString();
	i = 0;
	i = strLoc.indexOf('?page=');
	//console.log(strLoc.slice(i+7, strLoc.length));
	if (i>0) {
		strLoc = strLoc.slice(0, i) + '?page=' + pageNumber + strLoc.slice(i+7, strLoc.length);
			//console.log(strLoc);
 	} else{
		strLoc = strLoc + '?page=' + pageNumber + '&parentGUID=' + currentProductGroup;
 	}

	history.pushState(null, null, strLoc);


}

function ValidateDate(date_fl){
	str=date_fl.value;
	function TstDate(){
		str = str.toString().replace('-', '.');
		str = str.toString().replace('-', '.'); 
		str2=str.split(".");
		if(str2.length!=3){return false;}
		//Границы разрешенного периода. Нельзя ввести дату до 1990-го года и позднее 2020-го.
		if((parseInt(str2[0], 10)<=1990)||(parseInt(str2[0], 10)>=2030)) {return false;}
		str2=str2[2] +'-'+ str2[1]+'-'+ str2[0];
		if(Date(str2) =='Invalid Date'){return false;}
		return str;

	}
	var S=TstDate();
	if(S){
		//Дата валидна
		date_fl.className='date_ok';
	}
	else
	{
		//Дата не валидна
		date_fl.className='date_err';
	}
}

function next_block_page(){
	pages = document.querySelectorAll('.page_display');
	last_page = pages[pages.length-1];
	for( var i = 0; i <= pages.length-1; i++){
		elem = pages[i];
		if(elem.classList.contains("page_display")){
			elem.classList.remove("page_display");
			elem.classList.add("page_display_none");
			elem.style.display = 'none';
			//console.log(pages[i]);
		}	
	}
	//Массив следующих 10 страниц
	j = 0;
	array = new Array();
	while(j<10){
		elem = last_page.nextSibling;
		//console.log(elem);
		array.push(elem);
		last_page = elem;
		j++;
	}
	for( var i = 0; i <= array.length-1; i++){
		elem = array[i];
		if(elem.classList.contains("page_display_none")){
			//elem.classList.remove("data");
			//console.log(elem);
			elem.style.display = 'inline-block';
			elem.classList.add("page_display");
			elem.classList.remove("page_display_none");
		}	
	}

	cursor();
}

function previous_block_page(){
	pages = document.querySelectorAll('.page_display');
	console.log(pages);
	last_page = pages[0];
	for( var i = 0; i <= pages.length-1; i++){
		elem = pages[i];
		//console.log(elem);
		if(elem.classList.contains("page_display")){
			elem.classList.remove("page_display");
			elem.classList.add("page_display_none");
			elem.style.display = 'none';
			//console.log(pages[i]);
		}	
	}
	
	//Массив следующих 10 страниц
	j = 0;
	array = new Array();
	while(j<10){
		elem = last_page.previousSibling;
		array.push(elem);
		last_page = elem;
		j++;
	}

	console.log('last_page - '+last_page);
	console.log('array.length - '+array.length);
	
	for( var i = 0; i <= array.length-1; i++){
		//console.log(elem);
		elem = array[i];
		if(elem.classList.contains("page_display_none")){
			//elem.classList.remove("data");
					
			elem.classList.remove("page_display_none");
			elem.style.display = 'inline-block';
			elem.classList.add("page_display");
		}		
	}	

	cursor(); //показ стрелочек
	
}

function cursor(){

	//Вычисляем maxPage для показа стрелочки
	 // Узнаём состояние вкл или откл отбор - после нажатия
	elemhHideRowsBtnForm = document.querySelector('#hiderowsbtnform');
	elemhHideRowsBtn = elemhHideRowsBtnForm.querySelector('button');
	console.log(elemhHideRowsBtn);
	if (elemhHideRowsBtn.value == '+') {
		console.log('in_order');
		var allRows = document.querySelectorAll('#order_table .in_order');
	} else {
		console.log('all_in_category');
		var allRows = document.querySelectorAll('#order_table .all_in_category');
	}

	maxPage = Math.ceil(allRows.length/ROWS_ON_PAGE);
	console.log(allRows.length);
	console.log(maxPage);
	//


	//скрываем стрелочки у крайних страниц
	//при перехагузке
	//последняя страница не может открыться автоматически
	var allPages = document.querySelectorAll('.pages > a');
	var visiblePages = document.querySelectorAll('.page_display_none, .page_display');
	one = allPages[1];
	last = visiblePages[visiblePages.length-1]; //последняя странциа с классом
	cursor_left = allPages[0];
	cursor_right = allPages[allPages.length-1];

	console.log('one - '+one);
	console.log('last - '+last);
	console.log('cursor_left - '+cursor_left);
	console.log('cursor_right - '+cursor_right);

	if(one.classList.contains("page_display")){
		cursor_left.style.display='none';
	}else{
		cursor_left.style.display='inline-block';
	}

	if(last.classList.contains("page_display")){
		cursor_right.style.display='none';
	}else{
		if(maxPage > 10){
			cursor_right.style.display='inline-block';
			console.log('cursor_right display in cursor!');
		}	
	}	
}

function setClassOfPage(){
	allPages = document.querySelectorAll('.pages > a');
	for( var i = 0; i <= allPages.length-1; i++){
		elem = allPages[i];	
		if(elem.style.display == 'inline'){
			elem.classList.add('page_display');
			elem.classList.remove('page_display_none');
		}else{
			if(elem.classList.contains("page_display")){
				elem.classList.remove('page_display');
				elem.classList.add('page_display_none');
			}
		}
	}
}

function hide_cursor(){ //при щелчке на корзину
	 //скрываем стрелочки после загрузки, если страниц <=10

	 // Узнаём состояние вкл или откл отбор - после нажатия
	elemhHideRowsBtnForm = document.querySelector('#hiderowsbtnform');
	elemhHideRowsBtn = elemhHideRowsBtnForm.querySelector('button');
	console.log(elemhHideRowsBtn);
	if (elemhHideRowsBtn.value == '+') {
		console.log('in_order');
		var allRows = document.querySelectorAll('#order_table .in_order');
	} else {
		console.log('all_in_category');
		var allRows = document.querySelectorAll('#order_table .all_in_category');
	}

	console.log(allRows.length);
	let arrOfRows = new Array(); //массив не срытых кнопкой отбора строк
	for (var i = 1; i <= allRows.length; i++) {
		elem = allRows[i-1];
		if (!elem.classList.contains('hidden_row')) {
			arrOfRows.push(elem);
		}
	}
	maxPage = Math.ceil(arrOfRows.length/ROWS_ON_PAGE);
	console.log(arrOfRows.length);
	console.log(maxPage);

	var allPages = document.querySelectorAll('.pages > a');

	if(maxPage <= 10){
		console.log('maxPage <=10 - '+maxPage);
		for (var i = 1; i <= allPages.length; i++) {
			elemPage = allPages[i-1];
			if(elemPage.textContent == '>' || elemPage.textContent=='<'){
				elemPage.style.display='none';
			}
		}	
	} else{
		console.log('maxPage >10 - '+maxPage);
		for (var i = 1; i <= allPages.length; i++) {
			elemPage = allPages[i-1];
			if(elemPage.textContent == '>'){
				elemPage.style.display='inline-block';
			}
			if(elemPage.textContent == '<'){
				elemPage.style.display='none';
			}
		}
	}

	setClassOfPage();

}

function hideRowsTableDebt(text){

	//по хорошему переделать функцию, чтобы не искать и не проходиться по одним и тем же элементам


	let Sum = 0;
	console.log('Начальная сумма - '+Sum);

	rows = document.querySelectorAll('.dolg_history_row_contactGUID'); //строки с гуид

	button = document.querySelector('#contragent_dolg');

	contractGUID = text;

	for (var i = 0; i < rows.length; i++) {
		

		elem = rows[i].innerHTML;

		console.log('elem= - '+elem);
		//console.log('contractGUID= - '+contractGUID);

		if (elem == contractGUID){
			//console.log('elem= - '+elem);

			trs = document.querySelectorAll('.dolg_history_row');
			
			for (tr of trs) {

				if(!tr.classList.contains("this")){
						elem2 = tr.querySelector('.dolg_history_row_contactGUID').innerHTML; //для тп ок, для тт не сработало
						console.log('elem 2 - '+elem2);
						//console.log('elem   - '+elem);
						if (elem == elem2){
							console.log('Совпало '+elem2);
							tr.classList.add("this");
							
							//считаем сумму
							sumDebt = tr.querySelector('td:nth-child(4)');
							console.dir('sumDebt -'+ sumDebt);
							if (sumDebt != null){
								console.log(Number(sumDebt.innerHTML));
								Sum = Sum + Number(sumDebt.innerHTML);
								console.log('Sum - '+Sum);				
							}
							
						}
						else {
							tr.classList.add("no_this");
							tr.hidden = true;
						}
				}

				

				//подсвечиваем строки и кнопку если есть просрочки
				dayDebt = tr.querySelector('td:nth-child(5)');
				//console.log(dayDebt);
				if (dayDebt != null){
					if (Number(dayDebt.innerHTML) > 0  ){
						tr.classList.add("dayDebt");
						button.classList.add("dayDebt_button");

					}
				}
			}			
		}
		else{ //не совпало со строкой - скрываем ее
			console.log('не совпало');
			trs = document.querySelectorAll('.dolg_history_row');
			//console.dir(trs);
			for (tr of trs) {
				elem2 = tr.querySelector('.dolg_history_row_contactGUID').innerHTML;
				//console.log('elem 2 - '+elem2);
				if (elem == elem2){
					tr.hidden = true;
				}
			}
		}
	}


	elemInfo = document.getElementById("contragent_dolg");
	console.log('Sum - '+Sum);
	elemInfo.innerHTML = 'Сумма долга: <b>' + Sum.toFixed(2) + '</b>';
}

function sum_ajax2_tt(){

	Sum = 0;
	
	trs = document.querySelectorAll('.dolg_history_row');
			
	for (tr of trs) {

		if(tr.classList.contains("this")){
			//считаем сумму
					sumDebt = tr.querySelector('td:nth-child(4)');
					console.dir('sumDebt -'+ sumDebt);
					if (sumDebt != null){
						console.log(Number(sumDebt.innerHTML));
						Sum = Sum + Number(sumDebt.innerHTML);
						console.log('Sum - '+Sum);				
					}
		}
	}

	elemInfo = document.getElementById("contragent_dolg");
	console.log('Sum - '+Sum);
	elemInfo.innerHTML = 'Сумма долга: <b>' + Sum.toFixed(2) + '</b>';


}

function displayDebtHistory(){

	if (document.querySelector('#dolg_history').style.display == 'block'){
		document.querySelector('#dolg_history').style.display = 'none'
	}
	else{
		document.querySelector('#dolg_history').style.display = 'block';
	}

}

function DebitorDolg(){

	
			
}
