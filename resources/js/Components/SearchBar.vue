<script setup>
</script>

<template>
    <div class="relative group w-full md:w-auto">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4 text-black/40 group-focus-within:text-black transition-colors duration-500" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                    d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
            </svg>
        </div>

        <input type="text" placeholder="What are you looking for?" class="border border-gray-200 bg-transparent rounded-none outline-none focus:outline-none focus:ring-0 focus:border-gray-900 transition-all duration-500
                   /* Mobile Styles (Thicker) */
                   w-full py-3 pl-11 pr-4 text-sm
                   /* Desktop Styles (Original) */
                   md:w-80 md:py-1.5 md:pl-9 md:text-xs md:focus:w-[350px]
                   placeholder:text-black/30" />
    </div>
</template>