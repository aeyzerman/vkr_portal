@props(['value' => null, 'name' => 'course', 'label' => 'Курс'])

<label class="text-sm font-medium text-stone-700">{{ $label }}</label>
<select name="{{ $name }}" {{ $attributes->merge(['class' => 'field']) }} required>
    <option value="" disabled @selected($value === null && old('course') === null)>Выберите курс</option>
    @foreach (\App\Support\StudyGroupOptions::courses() as $course => $courseLabel)
        <option value="{{ $course }}" @selected((int) old('course', $value) === $course)>{{ $courseLabel }}</option>
    @endforeach
</select>
