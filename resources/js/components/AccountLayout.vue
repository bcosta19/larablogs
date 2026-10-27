<script setup lang="ts">
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
    <div class="account-layout">
        <header>
            <span class="account-brand">Larablogs</span>
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
        </header>
        <main class="account-card"><slot /></main>
    </div>
</template>

<style>
.account-layout {
    min-height: 100vh;
    padding: 1.5rem;
    background: #fbf9f5;
    color: #2c2825;
    font-family: var(--font-sans);
}
.dark .account-layout {
    background: #141210;
    color: #eae6df;
}
.account-layout header {
    max-width: 60rem;
    margin: auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.account-brand {
    font:
        normal 1.8rem Georgia,
        serif;
}
.account-layout select {
    border: 1px solid #948b80;
    padding: 0.35rem 0.65rem;
    border-radius: 2rem;
    background: inherit;
    color: inherit;
}
.account-layout select option {
    background: #fbf9f5;
    color: #2c2825;
}
.account-card {
    max-width: 26rem;
    margin: clamp(3rem, 10vh, 7rem) auto;
}
.account-card h1 {
    font:
        normal 2.5rem/1.2 Georgia,
        serif;
    margin-bottom: 1rem;
}
.account-intro {
    color: #756d63;
    line-height: 1.7;
    margin-bottom: 2rem;
}
.dark .account-intro {
    color: #b5ada3;
}
.account-card form {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}
.account-card label {
    margin-top: 0.65rem;
    font-size: 0.875rem;
}
.account-card input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #948b80;
    border-radius: 0.3rem;
    background: transparent;
    color: inherit;
}
.account-card button[type='submit'] {
    margin-top: 1rem;
    padding: 0.8rem;
    border-radius: 0.3rem;
    background: #2c2825;
    color: #fbf9f5;
    cursor: pointer;
}
.dark .account-card button[type='submit'] {
    background: #f5f2eb;
    color: #1a1816;
}
.account-card button:disabled {
    opacity: 0.5;
    cursor: default;
}
.account-card [role='alert'] {
    color: #a32d2d;
    line-height: 1.5;
    font-size: 0.875rem;
}
.dark .account-card [role='alert'] {
    color: #fca5a5;
}
.account-layout :focus-visible {
    outline: 3px solid #c68e24;
    outline-offset: 3px;
}
.account-signout {
    display: block;
    margin: 1.5rem auto 0;
    text-decoration: underline;
    cursor: pointer;
}
</style>
