@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Account: {{ $account->name }}</h5>
                    <a href="{{ route('accounts.show', $account) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Account
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('accounts.update', $account) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Account Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $account->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="account_type_id" class="form-label">Account Type</label>
                            <select class="form-select @error('account_type_id') is-invalid @enderror" 
                                    id="account_type_id" name="account_type_id" required>
                                <option value="">Select Account Type</option>
                                @foreach($accountTypes as $type)
                                    <option value="{{ $type->id }}" 
                                        {{ old('account_type_id', $account->account_type_id) == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('account_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="balance" class="form-label">Balance (XAF)</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('balance') is-invalid @enderror" 
                                       id="balance" name="balance" value="{{ old('balance', $account->balance) }}" 
                                       step="0.01" min="0" required>
                                <span class="input-group-text">XAF</span>
                            </div>
                            @error('balance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" 
                                      rows="3">{{ old('description', $account->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Account
                            </button>
                        </div>
                    </form>

                    <div class="mt-4 pt-3 border-top">
                        <form action="{{ route('accounts.destroy', $account) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this account? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Delete Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
