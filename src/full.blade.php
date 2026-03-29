@props(['size' => 'full'])
@php
    // Build entity_id -> custom name map from settings
    $entityIds = array_map('trim', explode(',', $config['todo_entities'] ?? ''));
    $customNames = array_map('trim', explode(',', $config['todo_names'] ?? ''));
    $nameMap = [];
    foreach ($entityIds as $i => $eid) {
        if (!empty($customNames[$i])) {
            $nameMap[$eid] = $customNames[$i];
        }
    }

    // Extract todo lists from service_response
    $todoLists = collect();
    $serviceResponse = $data['IDX_0']['service_response']
        ?? $data['service_response']
        ?? $data['IDX_0']
        ?? [];

    foreach ($serviceResponse as $entityId => $listData) {
        if (!is_array($listData) || !isset($listData['items'])) continue;

        $items = collect($listData['items'])->map(function ($item) {
            return [
                'summary' => $item['summary'] ?? '',
                'due' => $item['due'] ?? null,
            ];
        });

        // Sort by due date ascending (nulls last)
        $items = $items->sort(function ($a, $b) {
            if ($a['due'] === $b['due']) return 0;
            if ($a['due'] === null) return 1;
            if ($b['due'] === null) return -1;
            return strcmp($a['due'], $b['due']);
        })->values();

        if ($items->isEmpty()) continue;

        // Use custom name if provided, otherwise derive from entity_id
        $name = $nameMap[$entityId]
            ?? ucwords(str_replace(['todo.', '_'], ['', ' '], $entityId));

        $todoLists->push([
            'name' => $name,
            'entity_id' => $entityId,
            'items' => $items,
        ]);
    }

    $totalItems = $todoLists->sum(fn($l) => $l['items']->count());

    // Limit items for smaller sizes
    $maxLists = match($size) {
        'quadrant' => 1,
        'half_horizontal' => 2,
        'half_vertical' => 2,
        default => 10,
    };
    $maxItemsPerList = match($size) {
        'quadrant' => 4,
        'half_horizontal' => 5,
        'half_vertical' => 6,
        default => 12,
    };
    $todoLists = $todoLists->take($maxLists)->map(function ($list) use ($maxItemsPerList) {
        $list['items'] = $list['items']->take($maxItemsPerList);
        return $list;
    });
@endphp

<x-trmnl::view size="{{ $size }}">
    <x-trmnl::layout>
        <div class="columns">
            <div class="column"
                 data-list-limit="true"
                 data-list-max-height="{{ $size === 'quadrant' ? '150' : ($size === 'half_horizontal' ? '170' : '390') }}">
                @forelse($todoLists as $list)
                    @if($todoLists->count() > 1)
                        <div class="item">
                            <div class="content">
                                <span class="label label--large font--bold">{{ $list['name'] }}</span>
                            </div>
                        </div>
                    @endif
                    @foreach($list['items'] as $item)
                        <div class="item">
                            @if($size !== 'quadrant')
                                <div class="meta"><span class="index"></span></div>
                            @endif
                            <div class="content">
                                @if($size === 'quadrant')
                                    <span class="description clamp--1">{{ $item['summary'] }}</span>
                                @else
                                    <span class="title title--small">{{ $item['summary'] }}</span>
                                    @if($item['due'])
                                        <span class="description">Due {{ $item['due'] }}</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                @empty
                    <div class="item">
                        <div class="content text--center">
                            <span class="title">No tasks</span>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </x-trmnl::layout>

    <x-trmnl::title-bar title="{{ $trmnl['plugin_settings']['instance_name'] ?? 'Todos' }}" instance="{{ $totalItems }} tasks"/>
</x-trmnl::view>
