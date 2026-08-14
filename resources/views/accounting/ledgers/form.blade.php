<div class="form-group">

    <label>
        Account Group
        <span class="text-danger">*</span>
    </label>

    <select name="account_group_id"
        id="account_group_id"
        class="form-control @error('account_group_id') is-invalid @enderror">

        <option value="">
            Select Account Group
        </option>

        @foreach($accountGroups as $group)

        <option value="{{ $group->id }}"
            {{ old(
                    'account_group_id',
                    $ledger->account_group_id ?? ''
                ) == $group->id ? 'selected' : '' }}>

            {{ $group->code }}
            -
            {{ $group->name }}

            ({{ $group->accountType->name ?? '' }})

        </option>

        @endforeach

    </select>

    @error('account_group_id')

    <span class="invalid-feedback">
        {{ $message }}
    </span>

    @enderror

</div>

<div class="form-group">

    <label>
        Ledger Name
        <span class="text-danger">*</span>
    </label>

    <input type="text"
        name="name"
        value="{{ old('name', $ledger->name ?? '') }}"
        class="form-control">

</div>

<div class="form-group">

    <button type="submit"
        class="btn btn-primary">

        <i class="fas fa-save"></i>
        Save

    </button>

    <a href="{{ route('accounting.ledgers.index') }}"
        class="btn btn-secondary">

        Cancel

    </a>

</div>