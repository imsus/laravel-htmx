@fragment('todo')
<li id="todo-1" hx-swap-oob="true">{{ $todo }}</li>
@endfragment

@fragment('todo-count')
<span id="todo-count" hx-swap-oob="true"><strong>{{ $left }} items left</strong></span>
@endfragment
