<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n, type LocaleCode } from '@/i18n';

const props = defineProps<{ locale: LocaleCode }>();
const emit = defineEmits<{ localeChange: [locale: LocaleCode] }>();
const { currentLocale, availableLocales, setLocale, t } = useI18n(props.locale);
const changeLocale = () => {
    setLocale(currentLocale.value);
    emit('localeChange', currentLocale.value);
};
</script>

<template>
    <div class="reading-layout">
        <nav class="reading-nav" :aria-label="t('reader.navigation')">
            <Link href="/" class="reading-brand">Larablogs</Link>
            <div class="reading-nav-actions">
                <select
                    v-model="currentLocale"
                    :aria-label="t('reader.language')"
                    @change="changeLocale"
                >
                    <option
                        v-for="language in availableLocales"
                        :key="language.code"
                        :value="language.code"
                    >
                        {{ language.flag }} {{ language.name }}
                    </option>
                </select>
                <Link href="/">{{ t('reader.home') }}</Link>
            </div>
        </nav>
        <main class="reading-main"><slot /></main>
    </div>
</template>

<style>
.reading-layout {
    min-height: 100vh;
    background: #fbf9f5;
    color: #2c2825;
    font-family: var(--font-sans);
}
.dark .reading-layout {
    background: #141210;
    color: #dcd7ce;
}
.reading-nav {
    max-width: 72rem;
    margin: auto;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    border-bottom: 1px solid #d5cec2;
}
.dark .reading-nav {
    border-color: #38342e;
}
.reading-brand {
    font-family: Georgia, serif;
    font-size: 1.5rem;
}
.reading-nav-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.875rem;
}
.reading-nav select {
    border: 1px solid #9c9488;
    border-radius: 2rem;
    padding: 0.3rem 0.6rem;
    background: inherit;
    color: inherit;
}
.reading-nav select option {
    background: #fbf9f5;
    color: #2c2825;
}
.reading-main {
    max-width: 54rem;
    margin: auto;
    padding: 2.5rem 1.5rem 5rem;
}
.reading-layout a:hover {
    text-decoration: underline;
}
.reading-layout :focus-visible {
    outline: 3px solid #c68e24;
    outline-offset: 4px;
}
.reading-muted {
    color: #756d63;
}
.dark .reading-muted {
    color: #b5ada3;
}
@media (max-width: 480px) {
    .reading-nav {
        align-items: flex-start;
    }
    .reading-nav-actions {
        flex-direction: column;
        align-items: flex-end;
        gap: 0.5rem;
    }
}
</style>
