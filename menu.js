<script>
function Show_matrix_goods() {
    document.getElementById("accordion").classList.toggle("show");
    //скрыть картинку в меню снизу
    if ($('#graphic').hasClass('active') ) { 
			$('#graphic').toggleClass('hide');
			}
}
function Show_matrix_goods2() {
    document.getElementById("accordion2").classList.toggle("show");
    //скрыть картинку в меню снизу
    if ($('#graphic').hasClass('active') ) { 
			$('#graphic').toggleClass('hide');
			}
}


function getStyle(element, cssRule) {
    if (document.defaultView && document.defaultView.getComputedStyle) {
        var value = document.defaultView.getComputedStyle(element, '').getPropertyValue(
            cssRule.replace(/[A-Z]/g, function(match, char) {
                return '-' + char.toLowerCase();
            })
        );
    } else if (element.currentStyle) {
        var value = element.currentStyle[cssRule];
    } else {
        var value = false;
    }
    return value;
}


// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.button_goods_left')) {

    var dropdowns = document.getElementById("accordion");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
    }
  }
    if (!event.target.matches('.button_goods_right')) {

    var dropdowns = document.getElementById("accordion2");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
    }
  }
}

//мобильное меню закрывается при окончании ввода в строке поиска
var inputName = document.getElementById("inputName");
//alert(inputName);
inputName.addEventListener("keyup", function(event) {
  if (event.keyCode === 13) {
   event.preventDefault();
    document.querySelector('.header_burger').click();
  }
});


function closeMenu(){
	document.querySelector('.header_burger').click();
}

//функция скррытия категорий первого уровня, если нет подкатегорий
document.addEventListener('DOMContentLoaded', function(){ 
	var submenu = document.querySelectorAll('.submenu');
  for (i=0; i<submenu.length; i++){
  	if (submenu[i].childNodes.length > 0){
	   //console.log('yeah!');
		}else{
			//console.log('no!');
			submenu[i].parentElement.style.display = 'none';
		}
  }	
});

function load_options(elem){
	input = document.getElementById("inputName");
	value =  input.value;
	if (value != ''){
		$.ajax({
    	type : 'POST',
        url: "ajax2.php?tt.php",
        data: {name: value},
        success: function(data){
        	setPage(1);
        	
        	strLoc = location.toString();
        	i = strLoc.indexOf('&search');
			if (i<0) { strLoc = strLoc + '&search=true';};

			history.pushState(null, null, strLoc);
	
			document.getElementById("order_table").innerHTML = '';
        	document.getElementById("order_table").innerHTML = data;
        	setLazy();
        	lazyLoad();
        	complete();
        	hasOrder();
	    }
    })
	} else{
		location.href = location.toString();
	} 
}

function input_readonly(){
	//document.querySelector('.window_readonly').style.display = 'grid';
	document.querySelector('.window_readonly').classList.add('b-show');
}

function close_window_readonly(){
	//document.querySelector('.window_readonly').style.display = 'none';
	document.querySelector('.window_readonly').classList.remove('b-show');
}


</script> 
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mobile-detect/1.4.4/mobile-detect.min.js"></script>
  <script  src="js/script.js"></script>
  <script src="https://kit.fontawesome.com/cd9a557ac3.js" crossorigin="anonymous"></script>