<pre>
	<?php
	var_dump($_POST);
	var_dump(file_exists("./app/updater/" . "3C71BF22FDCF" . ".bin"));
	?>
</pre>

<form class="" action="" method="post">
	<label for="operator">Typ podmínky</label>
	<select class="" name="operator">
		<option value="or">OR</option>
		<option value="and">AND</option>
	</select>
	<div class="variableArea">

		<button name="addButton" id="addButton">+</button>
	</div>
	<br>

	<label for="resetOperator">Typ podmínky</label>
	<select class="" name="resetOperator">
		<option value="or">OR</option>
		<option value="and">AND</option>
	</select>
	<div class="resetvariableArea">

		<button name="restartaAddButton" id="restartAddButton">+</button>
	</div>
	<br>

	<button type="submit" name="button">Odeslat</button>

</form>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script type="text/javascript">
function foo(element){
	var id = element.attr("name").match(/\d+/)[0]
	$("[name='variable["+id+"][value]']").remove();
	$("[name='variable["+id+"][equaler]']").remove();

	if (element.val() == "atDeviceValue") {
		var arrVarOperator = [
			{val : '>', text: '>'},
			{val : '<', text: '<'},
		];

		var varOperator = $('<select name="variable['+id+'][equaler]">');
		$(arrVarOperator).each(function() {
			varOperator.append($('<option>').attr('value',this.val).text(this.text));
		});
		element.parent().append(varOperator);


		var input = $("<input/>");
		input.attr("type","text");
		input.attr("name","variable["+id+"][value]");
		element.parent().append(input);
	} else if (element.val() == "time") {
		var arrVarOperator = [
			{val : '>', text: '>'},
			{val : '<', text: '<'},
		];

		var varOperator = $('<select name="variable['+id+'][equaler]">');
		$(arrVarOperator).each(function() {
			varOperator.append($('<option>').attr('value',this.val).text(this.text));
		});
		element.parent().append(varOperator);

		var input = $("<input/>");
		input.attr("type","time");
		input.attr("name","variable["+id+"][value]");
		element.parent().append(input);
	}else {
		var arrVarOperator = [
			{val : '=', text: '=='},
			{val : '!=', text: '!='},
		];

		var varOperator = $('<select name="variable['+id+'][equaler]">');
		$(arrVarOperator).each(function() {
			varOperator.append($('<option>').attr('value',this.val).text(this.text));
		});
		element.parent().append(varOperator);

		var arrVarValue = [
			{val : 'true', text: 'True'},
			{val : 'false', text: 'False'},
		];

		var varValue = $('<select name="variable['+id+'][value]">');
		$(arrVarValue).each(function() {
			varValue.append($('<option>').attr('value',this.val).text(this.text));
		});
		element.parent().append(varValue);
	}
}
$("#addButton,#restartAddButton").click( function (event) {
	event.preventDefault();
	var numItems = $('.var').length
	var arrVarSelect = [
		{val : 'sunSet', text: 'Západ Slunce'},
		{val : 'sunRise', text: 'Východ Slunce'},
		{val : 'inHome', text: 'Příchod'},
		{val : 'outHome', text: 'Odchod'},
		{val : 'time', text: 'Čas'},
		{val : 'atDeviceValue', text: 'Při hodnotě zařízení'},
		{val : 'sunRise', text: 'Východ Slunce'},
		{val : 'noOneHome', text: 'Nikdo Doma'},
		{val : 'someOneHome', text: 'Nekdo Doma'},
	];

	var varSelect = $('<select name="variable['+numItems+'][what]">');
	$(arrVarSelect).each(function() {
		varSelect.append($('<option>').attr('value',this.val).text(this.text));
	});
	varSelect.attr("onchange", "foo($(this))");

	/*onchange = function(e) {
	console.log(this.value);
	if (this.value == 'atDeviceValue') {
	alert("ok");
}
};*/

var arrVarOperator = [
	{val : '>', text: '>'},
	{val : '<', text: '<'},
	{val : '=', text: '=='},
	{val : '!=', text: '!='},
];

var varOperator = $('<select name="variable['+numItems+'][equaler]">');
$(arrVarOperator).each(function() {
	varOperator.append($('<option>').attr('value',this.val).text(this.text));
});

var arrVarValue = [
	{val : 'true', text: 'True'},
	{val : 'false', text: 'False'},
];

var varValue = $('<select name="variable['+numItems+'][value]">');
$(arrVarValue).each(function() {
	varValue.append($('<option>').attr('value',this.val).text(this.text));
});

var newDiv = $("<div class=var>").append(varSelect);
newDiv = newDiv.append(varOperator);
newDiv = newDiv.append(varValue);
$(this).parent().append(newDiv);

});

/*var arrVarSelect = [
{val : 'sunSet', text: 'Západ Slunce'},
{val : 'sunRise', text: 'Východ Slunce'},
{val : 'inHome', text: 'Příchod'},
{val : 'outHome', text: 'Odchod'},
{val : 'time', text: 'Čas'},
{val : 'atDeviceValue', text: 'Při hodnotě zařízení'},
{val : 'sunRise', text: 'Východ Slunce'},
{val : 'noOneHome', text: 'Nikdo Doma'},
{val : 'someOneHome', text: 'Nekdo Doma'},
];

var varSelect = $('<select name="variable['+numItems+'][]">');
$(arrVarSelect).each(function() {
varSelect.append($('<option>').attr('value',this.val).text(this.text));
});

var arrVarOperator = [
{val : '>', text: '>'},
{val : '<', text: '<'},
{val : '=', text: '=='},
{val : '!=', text: '!='},
];

var varOperator = $('<select name="variable['+numItems+'][]">');
$(arrVarOperator).each(function() {
varOperator.append($('<option>').attr('value',this.val).text(this.text));
});

var arrVarValue = [
{val : 'true', text: 'True'},
{val : 'false', text: 'False'},
];

var varValue = $('<select name="variable['+numItems+'][]">');
$(arrVarValue).each(function() {
varValue.append($('<option>').attr('value',this.val).text(this.text));
});

//TODO změna výstupní proměné na základě vstupu date,num etc
var newDiv = $("<div class=var>").append(varSelect);/*.change(
function (subEvent) {
alert(subEvent);
}
);*/
/*newDiv = newDiv.append(varOperator);
newDiv = newDiv.append(varValue);
$(".restartaAddButton")append(newDiv);*/


/*
var arrVarSelect = [
{val : 'sunSet', text: 'Západ Slunce'},
{val : 'sunRise', text: 'Východ Slunce'},
{val : 'inHome', text: 'Příchod'},
{val : 'outHome', text: 'Odchod'},
{val : 'time', text: 'Čas'},
{val : 'atDeviceValue', text: 'Při hodnotě zařízení'},
{val : 'sunRise', text: 'Východ Slunce'},
{val : 'noOneHome', text: 'Nikdo Doma'},
{val : 'someOneHome', text: 'Nekdo Doma'},
];

var varSelect = $('<select name="variable['+numItems+'][]">');
$(arrVarSelect).each(function() {
varSelect.append($('<option>').attr('value',this.val).text(this.text));
});

var arrVarOperator = [
{val : '>', text: '>'},
{val : '<', text: '<'},
{val : '=', text: '=='},
{val : '!=', text: '!='},
];

var varOperator = $('<select name="variable['+numItems+'][]">');
$(arrVarOperator).each(function() {
varOperator.append($('<option>').attr('value',this.val).text(this.text));
});

var arrVarValue = [
{val : 'true', text: 'True'},
{val : 'false', text: 'False'},
];

var varValue = $('<select name="variable['+numItems+'][]">');
$(arrVarValue).each(function() {
varValue.append($('<option>').attr('value',this.val).text(this.text));
});

//TODO změna výstupní proměné na základě vstupu date,num etc
var newDiv = $("<div class=var>").append(varSelect);/*.change(
function (subEvent) {
alert(subEvent);
}
);*/
/*	newDiv = newDiv.append(varOperator);
newDiv = newDiv.append(varValue);
$(".variableArea").parent().append(newDiv);*/

</script>
