<template>
    <section class="pt-12 pb-12 bg-zinc-950">
        <div class="max-w-full mx-auto px-6 lg:px-10">
            <div class="flex flex-col space-y-2">
                <h2 class="text-[10px] uppercase tracking-[0.3em] text-zinc-500 font-semibold">
                    Website Banner
                </h2>
                <div class="flex items-center space-x-3">
                    <p class="text-[11px] text-zinc-400 italic font-light tracking-wide">
                        Section under development — Expected on April 7, 2026
                    </p>
                    <div class="flex space-x-1">
                        <div class="w-1 h-1 bg-zinc-600 rounded-full animate-pulse"></div>
                        <div class="w-1 h-1 bg-zinc-600 rounded-full animate-pulse [animation-delay:200ms]"></div>
                        <div class="w-1 h-1 bg-zinc-600 rounded-full animate-pulse [animation-delay:400ms]"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes pulse {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.3;
    }
}
</style>