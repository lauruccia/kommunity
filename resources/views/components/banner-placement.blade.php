@props(['key'])

@php
    $banner = app(\App\Services\BannerService::class)->forPlacement((string) $key, auth()->user());
@endphp

@if ($banner)
    @php
        $campaign = $banner['campaign'];
        $creative = $banner['creative'];
        $desktopUrl = $creative->desktopImageUrl();
        $mobileUrl = $creative->mobileImageUrl();
    @endphp

    @if ($desktopUrl)
        <a href="{{ $banner['click_url'] }}"
           @if ($campaign->open_in_new_tab) target="_blank" rel="noopener sponsored" @else rel="sponsored" @endif
           class="block overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-[0_14px_34px_rgba(66,87,103,0.08)] transition hover:-translate-y-0.5 hover:shadow-[0_22px_44px_rgba(66,87,103,0.14)]">
            <picture>
                @if ($mobileUrl)
                    <source media="(max-width: 640px)" srcset="{{ $mobileUrl }}">
                @endif
                <img src="{{ $desktopUrl }}"
                     alt="{{ $creative->alt_text ?: $campaign->name }}"
                     class="w-full object-cover"
                     loading="lazy">
            </picture>
            @if ($creative->headline)
                <span class="sr-only">{{ $creative->headline }}</span>
            @endif
        </a>
    @endif
@endif
