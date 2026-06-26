@props(['label' => null, 'name', 'type' => 'text'])
<div class="ks-field">
    @if($label)
        <label class="ks-field__label" for="{{ $name }}">{{ $label }}</label>
    @endif
    <div {{ $attributes->only('class')->merge(['class' => 'ks-field__control' . ($errors->has($name) ? ' has-error' : '')]) }}>
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
               @if($errors->has($name)) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
               {{ $attributes->except('class') }}>
        {{ $slot }}
    </div>
    @error($name)
        <span class="ks-field__error" id="{{ $name }}-error" role="alert">{{ $message }}</span>
    @enderror
</div>
