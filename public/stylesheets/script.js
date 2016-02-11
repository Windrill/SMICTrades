/*
function head() {
//copied code for reference
alert('s');
var position = function(){
    var x = 0, y = 0;
    if( typeof( window.pageYOffset ) == 'number' ) {
        //Netscape compliant
        y = window.pageYOffset;
        x = window.pageXOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
        //DOM compliant
        y = document.body.scrollTop;
        x = document.body.scrollLeft;
    } else if( document.documentElement && 
    ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
        //IE6 standards compliant mode
        y = document.documentElement.scrollTop;
        x = document.documentElement.scrollLeft;
    }
    var obj = new Object();
    obj.x = x;
    obj.y = y;
    return obj;
};

if(position<300){
var header = document.getElementById('header');
if(header!=null)header.style.display='none';

}

document.getElementById('aa').append(position);
alert(position);
return;
}
var ready = function ( fn ) {

    // Sanity check
    if ( typeof fn !== 'function' ) return;

    // If document is already loaded, run method
    if ( document.readyState === 'complete'  ) {
        return fn();
    }
	}
ready(function(){
alert('sss');
head();
});
*/
$(window).scroll(function() {

//var show = $("show");
var position =$(window).scrollTop();
//$(show).text(position);
if(position>300){
$('nav').fadeIn('default');
} else{
$('nav').fadeOut('default');

}
});

$(document).ready(function(){
//$('books').css("color", "transparent");
//var books = $("books:last");

// if($('books:last').has('img')){
// $('books:last').append('aaaa');

// }

$('books').each(function(){
	if($(this).find('img').length){
		$(this).children('contents').hide();
//		$(this).append('aaaa'); testing code; don't delete until code fully in place
//		book_list.push($(this)); 
	}else
		$(this).children('contents').show();
});

$("books").mouseenter(function(){
	if($(this).find('img').length){
		$(this).children("img").hide();
		$(this).children("contents").show();
	}
});

$("books").mouseleave(function(){
	if($(this).find('img').length){
		$(this).children("img").show();
		$(this).children("contents").hide();
	}
});
});
