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
	<label for="search_address_or_hash">View address / block hash</label><br>
	<input type="text" name="search_address_or_hash" id="search_address_or_hash">
	<button type="submit">View</button>
</form>
<br>
<hr>
