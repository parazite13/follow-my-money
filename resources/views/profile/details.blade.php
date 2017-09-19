@extends('profile.template')

@section('head')
	{!!Html::style('css/table-sorter.css')!!}
@stop

@section('section')
	<ul class="nav nav-tabs" id="tabbed-menu">
		@foreach($accountsInfos as $slug => $infos)
			<li class="nav-item">
				<a class="nav-link" href="#" data-content="{{$slug}}">
					{{$infos['name']}}
					<p class="text-center mb-0">{{number_format($accountsInfos[$slug]['sum'], 2)}} €</p>
				</a>
			</li>
		@endforeach
		<li class="px-3 py-2" style="position: absolute; right: 0">
			<p class="text-center mb-0">{{number_format($total['payed_amount'], 2)}} €</p>
			<p class="text-center mb-0">
				<small>
					+ {{number_format(abs($total['real_amount']), 2)}} €
				</small>
			</p>
		</li>
	</ul>
	<div id="details-content">
		@foreach($accountsInfos as $slug => $infos)
			<div id="details-{{$slug}}" class="d-none">
				<table class="table table-hover table-bordered table-sm tablesorter">
					<thead class="thead-default">
						<tr>
							<th>Date</th>
							<th>Payed Amount</th>
							<th>Real Amount</th>
							<th>Description</th>
							<th>Detail</th>
							<th>Memo</th>
							<th>Category</th>
							<th>Subcategory</th>
						</tr>
					</thead>
					<tbody>
					@foreach($infos['transactions'] as $transaction)
						<tr>
							<td>{{$transaction->date}}</td>
							<td class="{{$transaction->payed_amount > 0 ? 'table-success' : 'table-danger'}}">{{number_format($transaction->payed_amount, 2)}} €</td>
							<td>{{$transaction->real_amount == null ? '' : number_format($transaction->real_amount, 2)}}</td>
							<td>{{$transaction->description}}</td>
							<td>{{$transaction->details}}</td>
							<td>{{$transaction->memo}}</td>
							<td>{{$transaction->category}}</td>
							<td>{{$transaction->subcategory}}</td>
						</tr>
					@endforeach
					@foreach($infos['transfer'] as $transfer)
						@if($slug == $transfer->from)
							<tr>
								<td>{{$transfer->date}}</td>
								<td class="table-danger">{{number_format(-$transfer->amount, 2)}} €</td>
								<td></td>
								<td colspan="5">{{$transfer->description}} &rarr; {{$transfer->toName}}</td>
							</tr>
						@elseif($slug == $transfer->to)
							<tr>
								<td>{{$transfer->date}}</td>
								<td class="table-success">{{number_format($transfer->amount, 2)}} €</td>
								<td></td>
								<td colspan="5">{{$transfer->description}} &larr; {{$transfer->fromName}}</td>
							</tr>
						@endif
					@endforeach
					</tbody>
				</table>
			</div>
		@endforeach
	</div>
@stop

@section('script')

	{!!Html::script('js/jquery.tablesorter.min.js')!!}

	<script type="text/javascript">
		$(document).ready(function(){
			$("table").each(function(){
				if($(this).find('tbody').children().length > 0){
					$(this).tablesorter({
        				sortList: [[0, 1]]
        			});
        		}
        	});
    	}); 

		$('#tabbed-menu a').click(function(){
			$('#tabbed-menu a').removeClass('active');
			$(this).addClass('active');
			$('#details-content > div').addClass('d-none');
			$('#details-' + $(this).attr('data-content')).removeClass('d-none');
		})
	</script>
@stop