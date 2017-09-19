@extends('profile.template')

@section('section')
	<ul>
		@foreach($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
	</ul>
	<div id="add-transaction" class="card">
		<div class="card-block">
			<h4 class="card-title">Add transaction</h4>
			<p class="card-text">
				{!! Form::open(array('route' => 'my-profile/add-transaction', 'class' => 'form row', 'method' => 'put'))!!}
					{!!Form::label('date', 'Date', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::input('date', 'date', null, array('class' => 'col-4 form-control'))!!}
					{!!Form::label('account', 'Account', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::select('account', $accounts, 'compte_courant', array('class' => 'col-4 form-control'))!!}
					{!!Form::label('payed_amount', 'Amount', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::input('number', 'payed_amount', null, array('class' => 'col-2 form-control', 'step' => 'any'))!!}
					<div class="col-2 text-right">
						{!!Form::label('check_real_amount', 'Make advance')!!}
						{!!Form::label('check_refund', 'Refund advance')!!}
					</div>
					<div class="col-1 py-2">
						{!!Form::checkbox('check_real_amount', null)!!}
						<br>
						{!!Form::checkbox('check_refund', 'check_refund')!!}
					</div>								
					{!!Form::label('real_amount', 'Real Amount', array('class' => 'col-2 col-form-label real-amount-hide'))!!}
					{!!Form::input('number', 'real_amount', null, array('class' => 'col-3 form-control real-amount-hide', 'step' => 'any', 'min' => '0'))!!}					
					{!!Form::label('description', 'Description', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::text('description', null, array('class' => 'form-control mr-2'))!!}
					{!!Form::label('detail', 'Detail', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::text('detail', null, array('class' => 'form-control mr-2'))!!}
					{!!Form::label('memo', 'Memo', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::text('memo', null, array('class' => 'form-control mr-2'))!!}
					{!!Form::label('category', 'Category', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::select('category', $categories, null, array('id' => 'select-categories', 'class' => 'form-control'))!!}
					{!!Form::label('subcategory', 'Subcategory Name', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::select('subcategory', array(), null, array('id' => 'select-subcategories', 'class' => 'form-control mr-2'))!!}
					{!!Form::button('Add', array('class' => 'btn btn-primary mx-auto mt-2', 'type' => 'submit'))!!}
				{!! Form::close()!!}
			</p>
		</div>
	</div>
	<div id="add-transfer" class="card">
		<div class="card-block">
			<h4 class="card-title">Add transfer</h4>
			<p class="card-text">
				{!! Form::open(array('route' => 'my-profile/add-transfer', 'class' => 'form row', 'method' => 'put'))!!}
					{!!Form::label('date', 'Date', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::input('date', 'date', null, array('class' => 'col-4 form-control'))!!}
					<div class="col-6"></div>
					{!!Form::label('from_account', 'From account', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::select('from_account', $accounts, null, array('class' => 'col-4 form-control'))!!}
					{!!Form::label('to_account', 'To account', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::select('to_account', $accounts, null, array('class' => 'col-4 form-control'))!!}
					{!!Form::label('amount', 'Amount', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::input('number', 'amount', null, array('class' => 'col-2 form-control', 'step' => 'any'))!!}
					<div class="col-8"></div>			
					{!!Form::label('description', 'Description', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::text('description', null, array('class' => 'form-control mr-2'))!!}
					{!!Form::button('Add', array('class' => 'btn btn-primary mx-auto mt-2', 'type' => 'submit'))!!}
				{!! Form::close()!!}
			</p>
		</div>
	</div>
@stop


@section('script')
	<script type="text/javascript">
		$('#check_real_amount').change(function(){
			if($(this).prop('checked')){
				$('.real-amount-hide').css('visibility', 'visible');
			}else{
				$('.real-amount-hide').css('visibility', 'hidden');
			}
		});
	</script>
@stop

@section('ajax')
	<script type="text/javascript">
		$('#select-categories').change(function(){
			var select = $('#select-subcategories')
			select.empty();
			$.get('get-subcategories/' + slugify($(this).val()), function(data){
				$.each(data, function(index, value) {
				    select.append('<option value="'+index+'">'+value+'</option>');
				}); 
			});
		});
		$('#select-categories').trigger('change');
	</script>
@stop
