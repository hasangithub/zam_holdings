<div class="form-group">

    <label>
        Account Type
        <span class="text-danger">*</span>
    </label>

    <select name="account_type_id"
        class="form-control @error('account_type_id') is-invalid @enderror">

        <option value="">
            Select Account Type
        </option>

        @foreach($accountTypes as $accountType)

        <option value="{{ $accountType->id }}"
            {{ old(
                    'account_type_id',
                    $accountGroup->account_type_id ?? ''
                ) == $accountType->id ? 'selected' : '' }}>

            {{ $accountType->code }}
            -
            {{ $accountType->name }}

        </option>

        @endforeach

    </select>

    @error('account_type_id')
    <span class="invalid-feedback">
        {{ $message }}
    </span>
    @enderror

</div>


<div class="form-group">

    <label>
        Code
        <span class="text-danger">*</span>
    </label>

    <input type="text"
        name="code"
        value="{{ old('code', $accountGroup->code ?? '') }}"
        class="form-control @error('code') is-invalid @enderror">

    @error('code')
    <span class="invalid-feedback">
        {{ $message }}
    </span>
    @enderror

</div>


<div class="form-group">

    <label>
        Account Group Name
        <span class="text-danger">*</span>
    </label>

    <input type="text"
        name="name"
        value="{{ old('name', $accountGroup->name ?? '') }}"
        class="form-control @error('name') is-invalid @enderror">

    @error('name')
    <span class="invalid-feedback">
        {{ $message }}
    </span>
    @enderror

</div>

<div class="form-group">

    <button type="submit"
        class="btn btn-primary">

        <i class="fas fa-save"></i>

        Save

    </button>

    <a href="{{ route('accounting.account-groups.index') }}"
        class="btn btn-secondary">

        Cancel

    </a>

</div>