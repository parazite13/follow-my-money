@extends('profile.template')

@section('section')
	<div class="row">
		<canvas id="canvas-global"></canvas>
	</div>
	<div class="row">
		<canvas id="canvas-category"></canvas>
	</div>
@stop

@section('script')

	{!!Html::script('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.min.js')!!}

	<script type="text/javascript">

		// Canvas global
		new Chart($('#canvas-global'),{
			"type":"line",
			"data":{
				"labels":
					@php
						$oneMonth = 60 * 60 * 24 * 30;
						$oneYear = 60 * 60 * 24 * 365;
						$end = time();
						$start = $end - $oneYear + $oneMonth;
					@endphp
					[@for($date = $start; $date < $end; $date += $oneMonth)
						'{{date('1 M Y', $date)}}',
					@endfor],
				"datasets":
					[@foreach($accountsInfos as $slug => $infos)
					{
						"label":"{{$infos['name']}}",
						"data":
							[@foreach($infos['monthly'] as $info)
								'{{$info}}',
							@endforeach],
						"fill":false,
						"borderColor":"{{$infos['color']}}",
						"lineTension":0.1
					},
					@endforeach]
			},
			"options":{}
		});


		// Canvas category
		new Chart($('#canvas-category'),{
			"type":"pie",
			"data":{
			    labels: 
			    	[@foreach($categoryInfos['category'] as $infos)
			    		"{{$infos['name']}}",
			    	@endforeach],
				datasets: [
					{
						labels:
							[@foreach($categoryInfos['category'] as $infos)
					    		"{{$infos['name']}}",
					    	@endforeach],

						data: 
							[@foreach($categoryInfos['category'] as $infos)
								{{$infos['sum']}},
							@endforeach],
					    	
					    backgroundColor:
						    [@foreach($categoryInfos['category'] as $infos)
								"{{$infos['color']}}",
							@endforeach]
					},{
						labels:
							[@foreach($categoryInfos['subcategory'] as $infos)
					    		"{{$infos['name']}}",
					    	@endforeach],

						data: 
							[@foreach($categoryInfos['subcategory'] as $infos)
								{{$infos['sum']}},
							@endforeach],
					    	
					    backgroundColor:
						    [@foreach($categoryInfos['subcategory'] as $infos)
								"{{$infos['color']}}",
							@endforeach]
					}

			    ],
			    
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


	</script>
	
@stop