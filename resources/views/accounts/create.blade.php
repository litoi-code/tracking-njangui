@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    {!! session('success') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Créer un Nouveau Compte</h5>
                    <a href="{{ route('accounts.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Retour aux Comptes
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('accounts.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom du Compte</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="account_type_id" class="form-label">Type de Compte</label>
                            <select class="form-select @error('account_type_id') is-invalid @enderror" 
                                    id="account_type_id" 
                                    name="account_type_id" 
                                    required>
                                <option value="">Sélectionner le Type de Compte</option>
                                @foreach($accountTypes as $type)
                                    <option value="{{ $type->id }}" {{ (old('account_type_id') ?? $defaultAccountTypeId) == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('account_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="balance" class="form-label">Initial Balance</label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control" 
                                       id="balance" 
                                       name="balance" 
                                       min="0"
                                       step="1"
                                       value="0"
                                       placeholder="Enter initial balance">
                                <span class="input-group-text">XAF</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('accounts.index') }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">Créer le Compte</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format number input on change
    const balanceInput = document.getElementById('balance');
    balanceInput.addEventListener('change', function() {
        // Ensure whole numbers
        this.value = Math.round(this.value);
        
        // Ensure non-negative
        if (this.value < 0) {
            this.value = 0;
        }
    });

    // Initialize Select2 for account type
    $('#account_type_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select account type'
    });
});
</script>
@endpush
