@if($transfers->isEmpty())
    <div class="alert alert-info">No transfers found matching your criteria.</div>
@else
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transfers as $transfer)
                    <tr>
                        <td>{{ $transfer->executed_at->format('Y-m-d H:i') }}</td>
                        <td>
                            @if($transfer->fromAccount)
                                <a href="{{ route('accounts.show', $transfer->fromAccount) }}">
                                    {{ $transfer->fromAccount->name }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if($transfer->toAccount)
                                <a href="{{ route('accounts.show', $transfer->toAccount) }}">
                                    {{ $transfer->toAccount->name }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="{{ $transfer->to_account_id == $account->id ? 'text-success' : 'text-danger' }}">
                            {{ $transfer->to_account_id == $account->id ? '+' : '-' }}
                            {{ number_format($transfer->amount, 2) }}
                        </td>
                        <td>{{ $transfer->description }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('transfers.edit', $transfer) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="d-flex justify-content-center mt-4">
        {{ $transfers->links() }}
    </div>
@endif
