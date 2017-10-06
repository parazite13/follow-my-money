@extends('profile.template')

@section('section')
	<div class="row">
		@php
			$oneMonth = 60 * 60 * 24 * 30;
			$oneYear = 60 * 60 * 24 * 365;
			$end = time();
			$start = $end - $oneYear;
		@endphp
		<input id="startDate" type="month" class="form-control" name="start" value="{{date("Y-m", $start)}}" onchange="refreshCanvas()">
		<input id="endDate" type="month" class="form-control" name="end" value="{{date("Y-m", $end)}}" onchange="refreshCanvas()">
	</div>
	<div class="row">
		<canvas id="canvas-amount"></canvas>
	</div>
	<div class="row">
		<canvas id="canvas-category"></canvas>
	</div>
@stop

@section('script')

	{!!Html::script('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.min.js')!!}

	<script type="text/javascript">

		function pad(n, width, z) {
			z = z || '0';
			n = n + '';
			return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
		}

		// CUSTOM DATE FUNCTIONS
		Date.isLeapYear = function (year) { 
		    return (((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0)); 
		};

		Date.getDaysInMonth = function (year, month) {
		    return [31, (Date.isLeapYear(year) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month];
		};

		Date.prototype.isLeapYear = function () { 
		    return Date.isLeapYear(this.getFullYear()); 
		};

		Date.prototype.getDaysInMonth = function () { 
		    return Date.getDaysInMonth(this.getFullYear(), this.getMonth());
		};

		Date.prototype.addMonths = function (value) {
		    var n = this.getDate();
		    this.setDate(1);
		    this.setMonth(this.getMonth() + value);
		    this.setDate(Math.min(n, this.getDaysInMonth()));
		    return this;
		};
		// END DATE FUNCTIONS

		function refreshCanvas(){

			var start = $('#startDate').val();
			var end = $('#endDate').val();

			$.get('get-overview-infos/' + start + '/' + end, function(data){
				var labels = [];
				var startDate = new Date(start);
				var endDate = new Date(end);
				while(startDate <= endDate){
					labels.push(startDate.getFullYear() + "-" + pad(startDate.getMonth() + 1, 2));
					startDate.addMonths(1);
				}
				drawCanvasAmount(labels, data.accountsInfos);

				labels = [];
				jQuery.each(data.categoryInfos.category, function(key, val) {
					labels.push(val.name);
				});
				drawCanvasCategory(labels, data);
			});

		}

		function drawCanvasAmount(label, data){

			var datasets = [];
			jQuery.each(data, function(key, val) {
				datasets.push({
					"label":val.name, 
					"data":Object.values(val.monthly),
					"fill":false,
					"borderColor":val.color
				});
			});

			// Canvas amount
			new Chart($('#canvas-amount'),{
				"type":"line",
				"data":{
					"labels": label,
					"datasets": datasets
				},
				"options":{}
			});

		}

		function drawCanvasCategory(label, data){

			var datasets = [];
			jQuery.each(data.categoryInfos, function(key, val) {
				var labelArray = [];
				var sumArray = [];
				var colorArray = [];
				jQuery.each(val, function(key2, val2) {
					labelArray.push(val2.name);
					sumArray.push(val2.sum);
					colorArray.push(val2.color);
				});
				datasets.push({
					"labels":labelArray, 
					"data":sumArray,
					"backgroundColor":colorArray
				});
			});

			// Canvas category
			new Chart($('#canvas-category'),{
				"type":"pie",
				"data":{
				    labels: label,
					datasets: datasets				    
				},
				options: {
				    responsive: true,
				    legend: {
	                    onClick: (e) => e.stopPropagation()
	                },
				    tooltips: {
				    	callbacks: {
					      	label: function(tooltipItem, data) {
					        	var dataset = data.datasets[tooltipItem.datasetIndex];
					        	var index = tooltipItem.index;
					        	return dataset.labels[index] + ': ' + dataset.data[index];
					        }
					    }
		      		}
		      	}
			});	

		}

		$(document).ready(refreshCanvas());


	</script>
	
@stop