<select class="form-control form-control-sm parent-item"
        data-id="{{ $item->id }}"
        data-parent-id="{{ $item->parent_id }}">

    @if($item->parent)

        <option value="{{ $item->parent->id }}" selected>
            {{ $item->parent->name }}
        </option>

    @endif

</select>