<html>
<body>
<h1>Items</h1>

@fragment('rows')
<ul id="rows">
@foreach ($items as $item)
<li>{{ $item }}</li>
@endforeach
</ul>
@endfragment

@fragment('form-errors')
@foreach ($errors->all() as $error)
<p>{{ $error }}</p>
@endforeach
@endfragment
</body>
</html>
