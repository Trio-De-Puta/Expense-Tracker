@props([
    'frequency' => 'one_time',
    'payDay' => 0,
    'payDay2' => 0,
    'idPrefix' => 'income',
])

@php
    $showPrimary = in_array($frequency, ['monthly', 'bi_monthly'], true);
    $showSecondary = $frequency === 'bi_monthly';
    $days = range(0, 31);
@endphp

<div class="space-y-4" data-income-schedule>
    <div>
        <label for="{{ $idPrefix }}-frequency" class="label">Frequency</label>
        <select
            id="{{ $idPrefix }}-frequency"
            name="frequency"
            class="field"
            data-income-frequency
        >
            @foreach (\App\Models\Income::FREQUENCIES as $value => $label)
                <option value="{{ $value }}" @selected($frequency === $value)>{{ $label }}</option>
            @endforeach
        </select>

        @error('frequency')
            <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div @class(['hidden' => ! $showPrimary]) data-payday="primary">
            <label for="{{ $idPrefix }}-pay-day" class="label">Payday</label>
            <select id="{{ $idPrefix }}-pay-day" name="pay_day" class="field">
                @foreach ($days as $day)
                    <option value="{{ $day }}" @selected((int) $payDay === $day)>
                        {{ $day === 0 ? 'Last day' : $day }}
                    </option>
                @endforeach
            </select>

            @error('pay_day')
                <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div @class(['hidden' => ! $showSecondary]) data-payday="secondary">
            <label for="{{ $idPrefix }}-pay-day-2" class="label">Second payday</label>
            <select id="{{ $idPrefix }}-pay-day-2" name="pay_day_2" class="field">
                @foreach ($days as $day)
                    <option value="{{ $day }}" @selected((int) $payDay2 === $day)>
                        {{ $day === 0 ? 'Last day' : $day }}
                    </option>
                @endforeach
            </select>

            @error('pay_day_2')
                <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <p class="text-xs text-muted">Monthly and bi-monthly income can use paydays to suggest the date automatically.</p>
</div>
