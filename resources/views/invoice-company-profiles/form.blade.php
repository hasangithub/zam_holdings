
<div class="form-group">
    <label for="title">Company Title <span class="text-danger">*</span></label>
    <input type="text"
           name="title"
           id="title"
           class="form-control @error('title') is-invalid @enderror"
           value="{{ old('title', $profile->title ?? '') }}"
           maxlength="255"
           required>
    @error('title')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="sub_title_1">Sub Title 1</label>
    <input type="text"
           name="sub_title_1"
           id="sub_title_1"
           class="form-control @error('sub_title_1') is-invalid @enderror"
           value="{{ old('sub_title_1', $profile->sub_title_1 ?? '') }}"
           maxlength="255">
    @error('sub_title_1')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="sub_title_2">Sub Title 2</label>
    <input type="text"
           name="sub_title_2"
           id="sub_title_2"
           class="form-control @error('sub_title_2') is-invalid @enderror"
           value="{{ old('sub_title_2', $profile->sub_title_2 ?? '') }}"
           maxlength="255">
    @error('sub_title_2')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="address">Address</label>
    <textarea name="address"
              id="address"
              rows="3"
              class="form-control @error('address') is-invalid @enderror"
              maxlength="2000">{{ old('address', $profile->address ?? '') }}</textarea>
    @error('address')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="status">Status <span class="text-danger">*</span></label>
    <select name="status" id="status" class="form-control" required>
        <option value="1"
            @selected((string) old('status', $profile->status ?? '1') === '1')>
            Active
        </option>
        <option value="0"
            @selected((string) old('status', $profile->status ?? '1') === '0')>
            Inactive
        </option>
    </select>
</div>
