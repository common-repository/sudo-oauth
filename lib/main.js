(function($) {
	"use strict";
	$(document).ready(function() {
		// add number
		$('.add-number').on('click',function(e){
			e.preventDefault();
			var number = $( this ).parent().find('input').val();
			number++;
			$( this ).parent().find('input').val(number);
		});
		$('.sub-number').on('click',function(e){
			e.preventDefault();
			var number = $( this ).parent().find('input').val();
			number--;
			if(number == 0){
				number = 1;
			}
			$( this ).parent().find('input').val(number);
		});
	});

})(jQuery);