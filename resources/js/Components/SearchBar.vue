<script setup>
</script>

<template>
    <div class="relative hidden sm:block group">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-3.5 w-3.5 text-black/40 group-focus-within:text-black transition-colors duration-500"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1"
                    d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"
                />
            </svg>
        </div>

        <input
            type="text"
            placeholder="What are you looking for?"
            class="border border-gray-200 bg-transparent rounded-none outline-none focus:outline-none focus:ring-0 focus:border-gray-900 text-xs w-80 focus:w-[350px] transition-all duration-500 py-1.5 pl-9 pr-4 placeholder:text-black/30"
        />
    </div>
</template>