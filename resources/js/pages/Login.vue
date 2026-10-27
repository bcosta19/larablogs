<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AccountLayout from '@/components/AccountLayout.vue';
import { useI18n, type LocaleCode } from '@/i18n';
const props = defineProps<{ locale: LocaleCode }>();
const { currentLocale, setLocale, t } = useI18n(props.locale);
const form = useForm({ email: '', password: '' });
const submit = () => form.post('/login', { onFinish: () => form.reset('password') });
</script>

<template>
    <Head :title="t('auth.login')" />
    <AccountLayout :locale="currentLocale" @locale-change="setLocale">
        <h1>{{ t('auth.login') }}</h1>
        <p class="account-intro">{{ t('auth.intro') }}</p>
        <form @submit.prevent="submit">
            <label for="login-email">{{ t('auth.email') }}</label>
            <input
                id="login-email"
                v-model="form.email"
                type="email"
                autocomplete="username"
                required
                autofocus
            />
            <label for="login-password">{{ t('auth.password') }}</label>
            <input
                id="login-password"
                v-model="form.password"
                type="password"
                autocomplete="current-password"
                required
            />
            <p v-if="form.errors.email || form.errors.password" role="alert">
                {{ t(form.errors.email?.startsWith('auth.') ? form.errors.email : 'auth.failed') }}
            </p>
            <button dusk="login-submit" type="submit" :disabled="form.processing">
                {{ t('auth.login') }}
            </button>
        </form>
    </AccountLayout>
</template>
