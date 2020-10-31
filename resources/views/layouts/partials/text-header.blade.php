<a href="{{ route('home') }}">
	<h1 class="pl-4 text-xl">XDAG Block Explorer</h1>
</a>

<a href="{{ route('mining calculator') }}">Mining calculator</a>
<br>
<br>

<a href="{{ route('balance checker') }}">Balance checker</a>
<br>
<br>

<form action="{{ route('block search') }}" method="POST">
	{{ csrf_field() }}
	<label for="search">Search address / block hash / height</label><br>
	<input type="text" name="search" id="search">
	<button type="submit">Search</button>
</form>
<br>
<hr>
