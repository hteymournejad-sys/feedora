@if($node->role == 'holding')
    <li>
        <details>
            <summary>{{ $node->company_alias }} (هلدینگ)</summary>
            <ul>
                @if(isset($node->children) && count($node->children) > 0)
                    @foreach($node->children as $child)
                        @include('tree-node', ['node' => $child, 'level' => $level + 1])
                    @endforeach
                @else
                    <li class="subsidiary">بدون زیرمجموعه</li>
                @endif
            </ul>
        </details>
    </li>
@else
    <li class="subsidiary">{{ $node->company_alias }} (شرکتی)</li>
@endif