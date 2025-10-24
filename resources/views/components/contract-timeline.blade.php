@props(['contract'])

@php
    $activities = $contract->activities()->with('causer')->orderBy('created_at', 'asc')->get();

    // Build timeline events from both status changes and activity log
    $timeline = collect();

    // Add creation event
    $createdActivity = $activities->where('description', 'created')->first();
    $timeline->push([
        'title' => 'Contract Created',
        'description' => 'Initial contract draft created',
        'timestamp' => $contract->created_at,
        'user' => $createdActivity?->causer?->name ?? 'System',
        'icon' => 'file',
        'status' => 'completed'
    ]);

    // Add signed event if applicable
    if ($contract->status === 'signed' || $contract->status === 'finalized') {
        $signedActivity = $activities->where('description', 'updated')
            ->where('properties->attributes->status', 'signed')
            ->first();

        $timeline->push([
            'title' => 'Contract Signed',
            'description' => 'Customer signature captured',
            'timestamp' => $signedActivity?->created_at ?? $contract->updated_at,
            'user' => $signedActivity?->causer?->name ?? $contract->updatedBy?->name ?? 'System',
            'icon' => 'pen',
            'status' => 'completed'
        ]);
    } else {
        $timeline->push([
            'title' => 'Awaiting Signature',
            'description' => 'Contract needs to be signed by customer',
            'timestamp' => null,
            'user' => null,
            'icon' => 'pen',
            'status' => 'pending'
        ]);
    }

    // Add finalized event if applicable
    if ($contract->status === 'finalized') {
        $finalizedActivity = $activities->where('description', 'updated')
            ->where('properties->attributes->status', 'finalized')
            ->first();

        $timeline->push([
            'title' => 'Contract Finalized',
            'description' => 'Contract finalized and PDF generated',
            'timestamp' => $finalizedActivity?->created_at ?? $contract->updated_at,
            'user' => $finalizedActivity?->causer?->name ?? $contract->updatedBy?->name ?? 'System',
            'icon' => 'check',
            'status' => 'completed'
        ]);
    } elseif ($contract->status === 'signed') {
        $timeline->push([
            'title' => 'Awaiting Finalization',
            'description' => 'Contract needs to be finalized',
            'timestamp' => null,
            'user' => null,
            'icon' => 'check',
            'status' => 'pending'
        ]);
    } else {
        $timeline->push([
            'title' => 'Awaiting Finalization',
            'description' => 'Contract needs to be signed first',
            'timestamp' => null,
            'user' => null,
            'icon' => 'check',
            'status' => 'inactive'
        ]);
    }

    // Add vault upload event if applicable
    if ($contract->ftp_to_vault) {
        $timeline->push([
            'title' => 'Uploaded to Vault',
            'description' => $contract->vault_path ? "Uploaded to: {$contract->vault_path}" : 'Successfully uploaded to Vault FTP',
            'timestamp' => $contract->ftp_at,
            'user' => 'System',
            'icon' => 'cloud',
            'status' => 'completed'
        ]);
    } elseif ($contract->status === 'finalized') {
        $timeline->push([
            'title' => $contract->ftp_error ? 'Vault Upload Failed' : 'Awaiting Vault Upload',
            'description' => $contract->ftp_error ?? 'Will be uploaded to Vault FTP',
            'timestamp' => null,
            'user' => null,
            'icon' => 'cloud',
            'status' => $contract->ftp_error ? 'failed' : 'pending'
        ]);
    } else {
        $timeline->push([
            'title' => 'Awaiting Vault Upload',
            'description' => 'Contract must be finalized first',
            'timestamp' => null,
            'user' => null,
            'icon' => 'cloud',
            'status' => 'inactive'
        ]);
    }

    // Add revisions if any
    if ($contract->revision_of) {
        $timeline->prepend([
            'title' => 'Revision Created',
            'description' => "This is a revision of contract #{$contract->revision_of}",
            'timestamp' => $contract->created_at,
            'user' => $contract->updatedBy?->name ?? 'System',
            'icon' => 'copy',
            'status' => 'info'
        ]);
    }
@endphp

<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Contract Timeline
    </h3>

    <!-- Main Timeline -->
    <div class="flow-root">
        <ul class="-mb-8">
            @foreach($timeline as $index => $event)
                <li>
                    <div class="relative pb-8">
                        @if(!$loop->last)
                            <span class="absolute left-5 top-5 -ml-px h-full w-0.5
                                {{ $event['status'] === 'completed' ? 'bg-indigo-600' : 'bg-gray-300' }}"
                                aria-hidden="true"></span>
                        @endif
                        <div class="relative flex items-start space-x-3">
                            <!-- Icon -->
                            <div class="relative">
                                <div class="h-10 w-10 rounded-full flex items-center justify-center ring-8 ring-white
                                    {{ $event['status'] === 'completed' ? 'bg-indigo-600' : '' }}
                                    {{ $event['status'] === 'pending' ? 'bg-yellow-500' : '' }}
                                    {{ $event['status'] === 'inactive' ? 'bg-gray-300' : '' }}
                                    {{ $event['status'] === 'failed' ? 'bg-red-600' : '' }}
                                    {{ $event['status'] === 'info' ? 'bg-blue-500' : '' }}">

                                    @if($event['icon'] === 'file')
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    @elseif($event['icon'] === 'pen')
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                    @elseif($event['icon'] === 'check')
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @elseif($event['icon'] === 'cloud')
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                    @elseif($event['icon'] === 'copy')
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    @endif
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="min-w-0 flex-1">
                                <div>
                                    <div class="text-sm">
                                        <span class="font-medium text-gray-900">{{ $event['title'] }}</span>
                                    </div>
                                    @if($event['timestamp'])
                                        <p class="mt-0.5 text-xs text-gray-500">
                                            {{ $event['timestamp']->format('M d, Y \a\t g:i A') }}
                                            @if($event['user'])
                                                <span class="text-gray-400">•</span>
                                                <span class="text-indigo-600">{{ $event['user'] }}</span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="mt-1 text-sm text-gray-600">
                                    {{ $event['description'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Activity Log Details -->
    @if($activities->count() > 1)
        <div class="mt-8 pt-6 border-t border-gray-200">
            <button type="button"
                    x-data="{ expanded: false }"
                    @click="expanded = !expanded"
                    class="flex items-center justify-between w-full text-left">
                <h4 class="text-sm font-semibold text-gray-900">
                    Detailed Activity Log ({{ $activities->count() }} events)
                </h4>
                <svg class="w-5 h-5 text-gray-400 transition-transform"
                     :class="{ 'rotate-180': expanded }"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="expanded"
                 x-collapse
                 class="mt-4 space-y-3">
                @foreach($activities as $activity)
                    <div class="flex items-start space-x-3 text-xs text-gray-600 bg-gray-50 p-3 rounded-md">
                        <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-900">{{ ucfirst($activity->description) }}</span>
                                <span class="text-gray-500">{{ $activity->created_at->format('M d, Y g:i A') }}</span>
                            </div>
                            <div class="mt-1">
                                <span class="text-indigo-600">{{ $activity->causer?->name ?? 'System' }}</span>
                                @if($activity->properties && $activity->properties->has('attributes'))
                                    <span class="text-gray-400 mx-1">•</span>
                                    <span class="text-gray-500">
                                        {{ count($activity->properties->get('attributes', [])) }} field(s) changed
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
