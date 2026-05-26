@props([
    'thesis',
    'canManage' => false,
])

@php
    $hasDocument = (bool) $thesis->document_path;
@endphp

<div {{ $attributes->merge(['class' => 'mt-6']) }}>
  <h3 class="text-sm font-medium text-stone-700">Прикреплённые файлы</h3>

  @if ($hasDocument)
    <ul class="mt-3 space-y-2">
      <li class="flex items-center justify-between gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
        <span class="min-w-0 truncate text-sm font-medium text-stone-900" title="{{ $thesis->document_name }}">
          {{ $thesis->document_name ?? 'Файл работы' }}
        </span>
        <div class="flex shrink-0 items-center gap-2">
          @can('downloadDocument', $thesis)
            <a
              href="{{ route('thesis.document.download', $thesis) }}"
              class="icon-btn"
              title="Скачать"
              aria-label="Скачать {{ $thesis->document_name ?? 'файл' }}"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12M12 16.5V3" />
              </svg>
            </a>
          @endcan
          @if ($canManage)
            @can('deleteDocument', $thesis)
              <form method="POST" action="{{ route('thesis.document.delete', $thesis) }}" onsubmit="return confirm('Удалить прикреплённый файл?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="icon-btn text-red-600 hover:border-red-200 hover:bg-red-50 hover:text-red-700" title="Удалить" aria-label="Удалить файл">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                  </svg>
                </button>
              </form>
            @endcan
          @endif
        </div>
      </li>
    </ul>
  @else
    <p class="mt-2 text-sm text-stone-500">Файлы ещё не прикреплены.</p>
  @endif

  @if ($canManage)
    @can('uploadDocument', $thesis)
      <form method="POST" action="{{ route('thesis.document.upload', $thesis) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
        @csrf
        <div>
          <label class="text-sm font-medium text-stone-700">
            {{ $hasDocument ? 'Заменить файл' : 'Загрузить файл работы' }}
          </label>
          <input type="file" name="document" class="field" required>
        </div>
        <button class="btn-primary">{{ $hasDocument ? 'Сохранить новый файл' : 'Загрузить' }}</button>
      </form>
    @endcan
  @endif
</div>
