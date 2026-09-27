import { ref, onMounted } from 'vue';

const isDark = ref(false);

export function useTheme() {
    const applyTheme = (dark) => {
        isDark.value = dark;
        if (dark) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    };

    const initTheme = () => {
        const storedTheme = localStorage.getItem('theme');
        if (storedTheme) {
            applyTheme(storedTheme === 'dark');
        } else {
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            applyTheme(systemPrefersDark);
        }
    };

    const toggleTheme = () => {
        applyTheme(!isDark.value);
    };

    onMounted(initTheme);

    return {
        isDark,
        initTheme,
        toggleTheme,
    };
}