
<div class="form-group">
    <label for="name">Employee Name <span class="text-danger">*</span></label>

    <input type="text"
           name="name"
           id="name"
           class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', isset($employee) ? $employee->name : '') }}"
           maxlength="255"
           required>

    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="phone">Phone</label>

    <input type="text"
           name="phone"
           id="phone"
           class="form-control @error('phone') is-invalid @enderror"
           value="{{ old('phone', isset($employee) ? $employee->phone : '') }}"
           maxlength="30">

    @error('phone')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="address">Address</label>

    <textarea name="address"
              id="address"
              rows="3"
              maxlength="2000"
              class="form-control @error('address') is-invalid @enderror">{{ old('address', isset($employee) ? $employee->address : '') }}</textarea>

    @error('address')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>
