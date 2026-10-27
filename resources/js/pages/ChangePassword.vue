<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AccountLayout from '@/components/AccountLayout.vue';
import { useI18n, type LocaleCode } from '@/i18n';
const props = defineProps<{ locale: LocaleCode }>();
const { currentLocale, setLocale, t } = useI18n(props.locale);
const form = useForm({ current_password: '', password: '', password_confirmation: '' });
const submit = () => form.put('/account/password', { onFinish: () => form.reset() });
const error = (message: string) => t(message.startsWith('auth.') ? message : 'auth.password_min');
</script>

<template>
    <Head :title="t('auth.change_password')" />
    <AccountLayout :locale="currentLocale" @locale-change="setLocale">
        <h1>{{ t('auth.change_password') }}</h1>
        <p class="account-intro">{{ t('auth.change_intro') }}</p>
        <form @submit.prevent="submit">
            <label for="current-password">{{ t('auth.current_password') }}</label>
            <input
                id="current-password"
                v-model="form.current_password"
                type="password"
                autocomplete="current-password"
                required
            />
            <p v-if="form.errors.current_password" role="alert">
                {{ error(form.errors.current_password) }}
            </p>
            <label for="new-password">{{ t('auth.new_password') }}</label>
            <input
                id="new-password"
                v-model="form.password"
                type="password"
                autocomplete="new-password"
                required
                minlength="12"
            />
            <p v-if="form.errors.password" role="alert">{{ error(form.errors.password) }}</p>
            <label for="confirm-password">{{ t('auth.confirm_password') }}</label>
            <input
                id="confirm-password"
                v-model="form.password_confirmation"
                type="password"
                autocomplete="new-password"
                required
                minlength="12"
            />
            <button dusk="password-submit" type="submit" :disabled="form.processing">
                {{ t('auth.save_password') }}
            </button>
        </form>
        <button type="button" class="account-signout" @click="router.post('/logout')">
            {{ t('auth.logout') }}
        </button>
    </AccountLayout>
</template>
