@props(['value' => null, 'name' => 'enrollment_year', 'label' => 'Год набора'])

<label class="text-sm font-medium text-stone-700">{{ $label }}</label>
<select name="{{ $name }}" {{ $attributes->merge(['class' => 'field']) }} required>
    <option value="" disabled @selected($value === null && old('enrollment_year') === null)>Выберите год</option>
    @foreach (\App\Support\StudyGroupOptions::enrollmentYears() as $year)
        <option value="{{ $year }}" @selected((int) old('enrollment_year', $value) === $year)>{{ $year }}</option>
    @endforeach
</select>
