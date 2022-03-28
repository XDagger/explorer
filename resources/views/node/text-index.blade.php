@extends('layouts.text-app')

@section('body')
    <h2>Node statistics</h2>

    <p>Last check: {{ $lastCheckAt ? $lastCheckAt->toDateTimeString() . ' UTC' : 'never' }}</p>

    <table cellpadding="10">
        <thead>
        <tr>
            <th>Node</th>
            <th>Status</th>
            <th align="center">Last seen</th>
            <th align="right">Uptime % (last 3 months)</th>
        </tr>
        </thead>

        <tbody>
        @forelse ($nodes as $node)
            <tr>
                <td>{{ $node->node }}</td>

                @php($node_data = $repo->uptimePercentageAndLastSeenAt($node))

                <td>
                    @if ($node->is_reachable)
                        <span>Node is up</span><br>

                    @else
                        <span>Node is down</span><br>
                    @endif
                </td>

                <td align="center">
                    {{ $node_data['last_seen_at'] ? $node_data['last_seen_at']->toDateTimeString() . ' UTC' : 'never' }}
                </td>

                <td align="right">
                    {{ number_format($node_data['uptime_percentage'], 2) }}%
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" align="center">There are no node statistics yet.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $nodes->links('support.text-pagination') }}
@endsection
