<div class="max-h-[60vh] overflow-y-auto pr-2 space-y-2 text-sm text-gray-600 dark:text-gray-400">
    <p class="font-medium text-red-600 dark:text-red-400 mb-3 text-base">
        Пожалуйста, исправьте следующие ошибки в вашем файле перед повторной отправкой:
    </p>
    <ul class="list-disc pl-5 space-y-2 text-base">
        @foreach($errors as $error)
            <li class="border-b border-gray-50 py-1 dark:border-gray-800/50 last:border-0 text-gray-900 dark:text-white">
                {{ $error }}
            </li>
        @endforeach
    </ul>
</div>

