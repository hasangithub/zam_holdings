<div class="form-group">

    <label>
        Ledger
        <span class="text-danger">*</span>
    </label>

    <select name="ledger_id"
            id="ledger_id"
            class="form-control @error('ledger_id') is-invalid @enderror">

        <option value="">Select Ledger</option>

        @foreach($ledgers as $ledger)

            <option value="{{ $ledger->id }}"
                {{ old(
                    'ledger_id',
                    $subLedger->ledger_id ?? ''
                ) == $ledger->id ? 'selected' : '' }}>

                {{ $ledger->code }}
                -
                {{ $ledger->name }}
                ({{ $ledger->accountGroup->name ?? '' }})

            </option>

        @endforeach

    </select>

    @error('ledger_id')
        <span class="invalid-feedback">
            {{ $message }}
        </span>
    @enderror

</div>


{{-- Branch: Only for Cash and Bank Account --}}
<div class="form-group" id="branch_group">

    <label>
        Branch
        <span class="text-danger">*</span>
    </label>

    <select name="branch_id"
            id="branch_id"
            class="form-control @error('branch_id') is-invalid @enderror">

        <option value="">Select Branch</option>

        @foreach($branches as $branch)

            <option value="{{ $branch->id }}"
                {{ old(
                    'branch_id',
                    $subLedger->branch_id ?? ''
                ) == $branch->id ? 'selected' : '' }}>

                {{ $branch->name }}

            </option>

        @endforeach

    </select>

    @error('branch_id')
        <span class="invalid-feedback">
            {{ $message }}
        </span>
    @enderror

</div>


<div class="form-group">

    <label>
        Sub Ledger Name
        <span class="text-danger">*</span>
    </label>

    <input type="text"
           name="name"
           value="{{ old('name', $subLedger->name ?? '') }}"
           class="form-control @error('name') is-invalid @enderror">

    @error('name')
        <span class="invalid-feedback">
            {{ $message }}
        </span>
    @enderror

</div>


<div class="form-group">

    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i>
        Save
    </button>

    <a href="{{ route('accounting.sub-ledgers.index') }}"
       class="btn btn-secondary">
        Cancel
    </a>

</div>