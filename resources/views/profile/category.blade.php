@extends('profile.template')

@section('section')
	<ul>
		@foreach($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
	</ul>
	<div id="add-category" class="card">
		<div class="card-block">
			<h4 class="card-title">Add category</h4>
			<p class="card-text">
				{!! Form::open(array('route' => 'my-profile/add-category', 'class' => 'form-inline row', 'method' => 'put'))!!}
					{!!Form::label('category', 'Category Name', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::text('category', null, array('class' => 'form-control mr-2'))!!}
					{!!Form::button('Add', array('class' => 'btn btn-primary', 'type' => 'submit'))!!}
				{!! Form::close()!!}
			</p>
		</div>
	</div>
	<div id="remove-category" class="card">
		<div class="card-block">
			<h4 class="card-title">Remove category</h4>
			<p class="card-text">
				{!! Form::open(array('route' => 'my-profile/remove-category', 'class' => 'form-inline row', 'method' => 'delete'))!!}
					{!!Form::label('category', 'Category Name', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::select('category', $categories, null, array('class' => 'form-control mr-2'))!!}
					{!!Form::button('Remove', array('class' => 'btn btn-primary', 'type' => 'submit'))!!}
				{!! Form::close()!!}
			</p>
		</div>
	</div>
	<div id="add-subcategory" class="card">
		<div class="card-block">
			<h4 class="card-title">Add subcategory</h4>
			<p class="card-text">
				{!! Form::open(array('route' => 'my-profile/add-subcategory', 'class' => 'form row', 'method' => 'put'))!!}
					{!!Form::label('category', 'Parent category', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::select('category', $categories, null, array('class' => 'form-control'))!!}
					{!!Form::label('subcategory', 'Subcategory Name', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::text('subcategory', null, array('class' => 'form-control mr-2'))!!}
					{!!Form::button('Add', array('class' => 'btn btn-primary mt-2 mx-auto', 'type' => 'submit'))!!}
				{!! Form::close()!!}
			</p>
		</div>
	</div>
	<div id="remove-subcategory" class="card">
		<div class="card-block">
			<h4 class="card-title">Remove subcategory</h4>
			<p class="card-text">
				{!! Form::open(array('route' => 'my-profile/remove-subcategory', 'class' => 'form row', 'method' => 'put'))!!}
					{!!Form::label('category', 'Parent category', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::select('category', $categories, null, array('class' => 'form-control', 'id' => 'select-categories'))!!}
					{!!Form::label('subcategory', 'Subcategory Name', array('class' => 'col-2 col-form-label'))!!}
					{!!Form::select('subcategory', array(), null, array('id' => 'select-subcategories', 'class' => 'form-control mr-2'))!!}
					{!!Form::button('Remove', array('class' => 'btn btn-primary mt-2 mx-auto', 'type' => 'submit'))!!}
				{!! Form::close()!!}
			</p>
		</div>
	</div>
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