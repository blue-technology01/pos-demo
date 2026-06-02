<tr id="row-{{ $user->id }}">

    <td>#{{ $user->id }}</td>

    <td>
        <div class="user-cell">
            @if($user->avatar)
                <img src="{{ asset('storage/'.$user->avatar) }}" class="user-avatar">
            @else
                <div class="user-avatar-initials">
                    {{ strtoupper(substr($user->name,0,2)) }}
                </div>
            @endif

            <div>
                <div class="user-name">{{ $user->name }}</div>
                <div class="user-id">#{{ $user->id }}</div>
            </div>
        </div>
    </td>

    <td>{{ $user->email }}</td>
    <td>{{ $user->phone ?? '—' }}</td>

    <td>{{ $user->roles->first()?->name }}</td>

    <td>
        <button class="btn-delete"
            data-id="{{ $user->id }}"
            data-name="{{ $user->name }}">
            Delete
        </button>
    </td>

</tr>
