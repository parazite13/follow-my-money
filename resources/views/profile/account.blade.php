@extends('profile.template')

@section('section')
	<ul>
		@foreach($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
	</ul>
	<div id="add-account" class="card">
		<div class="card-block">
			<h4 class="card-title">Add account</h4>
			<p class="card-text">
				{!! Form::open(array('route' => 'my-profile/add-account', 'class' => 'form-inline row', 'method' => 'put'))!!}
					{!!Form::label('account', 'Account Name', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::text('account', null, array('class' => 'form-control mr-2'))!!}
					{!!Form::button('Add', array('class' => 'btn btn-primary', 'type' => 'submit'))!!}
				{!! Form::close()!!}
			</p>
		</div>
	</div>
	<div id="remove-account" class="card">
		<div class="card-block">
			<h4 class="card-title">Remove account</h4>
			<p class="card-text">
				{!! Form::open(array('route' => 'my-profile/remove-account', 'class' => 'form-inline row', 'method' => 'delete'))!!}
					{!!Form::label('account', 'Account Name', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::select('account', $accounts, null, array('class' => 'form-control mr-2'))!!}
					{!!Form::button('Remove', array('class' => 'btn btn-primary', 'type' => 'submit'))!!}
				{!! Form::close()!!}
			</p>
		</div>
	</div>
@stop

@section('ajax')
	<script type="text/javascript">
		$('#remove-subcategory #categories').change(function(){
			var select = $('#remove-subcategory #subcategories')
			select.empty();
			$.get('get-subcategories/' + slugify($(this).val()), function(data){
				$.each(data, function(index, value) {
				    select.append('<option value="'+index+'">'+value+'</option>');
				}); 
			});
		});
	</script>
@stop