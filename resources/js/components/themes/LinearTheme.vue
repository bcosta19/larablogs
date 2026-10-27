<script setup lang="ts">
import { ref } from 'vue';
import type { BlogItem, BlogPostItem, BlogStats, FlashMessages } from '@/types';

defineProps<{
    blogs: BlogItem[];
    recentPosts: BlogPostItem[];
    stats: BlogStats;
    flash: FlashMessages;
    form: any;
    checkingBlogId: number | null;
    togglingBlogId: number | null;
    deletingBlogId: number | null;
}>();

const emit = defineEmits<{
    (e: 'submit'): void;
    (e: 'updateForm', field: 'url' | 'notify_email' | 'title', value: string): void;
    (e: 'prefill', title: string, url: string): void;
    (e: 'checkNow', id: number): void;
    (e: 'toggle', id: number): void;
    (e: 'delete', blog: BlogItem): void;
}>();

const activeTab = ref<'blogs' | 'posts'>('blogs');

const formatRelative = (dateStr: string | null): string => {
    if (!dateStr) return 'Nunca';
    try {
        const d = new Date(dateStr);
        const diff = Math.floor((Date.now() - d.getTime()) / 1000);
        if (diff < 60) return 'Agora';
        if (diff < 3600) return `${Math.floor(diff / 60)}m atrás`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h atrás`;
        return d.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' });
    } catch {
        return dateStr;
    }
};

const formatDate = (dateStr: string | null): string => {
    if (!dateStr) return '';
    try {
        return new Date(dateStr).toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: 'short',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <div
        class="min-h-screen bg-[#030712] text-slate-200 font-sans selection:bg-indigo-500 selection:text-white relative overflow-hidden"
    >
        <!-- Ambient Glow Elements -->
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[400px] bg-gradient-to-b from-indigo-500/10 via-purple-500/5 to-transparent blur-3xl pointer-events-none"
        ></div>

        <div class="max-w-5xl mx-auto px-6 py-10 relative z-10 space-y-8">
            <!-- Header -->
            <header
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 border-b border-white/5 pb-8"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-pink-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25"
                    >
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path
                                d="M6.18 15.64a2.18 2.18 0 0 1 2.18 2.18C8.36 19 7.38 20 6.18 20C5 20 4 19 4 17.82a2.18 2.18 0 0 1 2.18-2.18M4 4.44A15.56 15.56 0 0 1 19.56 20h-2.83A12.73 12.73 0 0 0 4 7.27V4.44m0 5.66a9.9 9.9 0 0 1 9.9 9.9h-2.83A7.07 7.07 0 0 0 4 12.93v-2.83Z"
                            />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-semibold tracking-tight text-white">
                                Larablogs
                            </h1>
                            <span
                                class="text-[10px] uppercase font-mono px-2 py-0.5 rounded-full bg-white/5 border border-white/10 text-slate-400"
                                >Radar</span
                            >
                        </div>
                        <p class="text-xs text-slate-400">
                            Stream contínuo de publicações com alertas instantâneos.
                        </p>
                    </div>
                </div>

                <!-- Stats Badges -->
                <div class="flex items-center gap-3 text-xs">
                    <div
                        class="px-3 py-1.5 rounded-lg bg-white/[0.03] border border-white/[0.08] flex items-center gap-2"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span class="text-slate-400">Monitorando:</span>
                        <span class="text-white font-medium">{{ stats.active_blogs }} blogs</span>
                    </div>
                    <div
                        class="px-3 py-1.5 rounded-lg bg-white/[0.03] border border-white/[0.08] flex items-center gap-2"
                    >
                        <span class="text-slate-400">Artigos:</span>
                        <span class="text-indigo-400 font-medium">{{ stats.total_posts }}</span>
                    </div>
                </div>
            </header>

            <!-- Alerts -->
            <div
                v-if="flash.success"
                class="p-4 rounded-xl bg-emerald-950/40 border border-emerald-500/20 text-emerald-300 text-sm flex items-center gap-3"
            >
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>{{ flash.success }}</span>
            </div>
            <div
                v-if="flash.error"
                class="p-4 rounded-xl bg-rose-950/40 border border-rose-500/20 text-rose-300 text-sm flex items-center gap-3"
            >
                <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                <span>{{ flash.error }}</span>
            </div>
            <div
                v-if="flash.info"
                class="p-4 rounded-xl bg-indigo-950/40 border border-indigo-500/20 text-indigo-300 text-sm flex items-center gap-3"
            >
                <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                <span>{{ flash.info }}</span>
            </div>

            <!-- Form Card com Frosted Glass -->
            <section
                class="rounded-2xl border border-white/10 bg-slate-900/40 backdrop-blur-xl p-6 sm:p-7 shadow-2xl relative overflow-hidden"
            >
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-base font-semibold text-white">Adicionar Novo Radar</h2>
                        <p class="text-xs text-slate-400">
                            Informe o link de qualquer blog para iniciar o rastreamento.
                        </p>
                    </div>
                    <!-- Presets -->
                    <div class="hidden sm:flex items-center gap-2 text-xs text-slate-400">
                        <span>Sugestões:</span>
                        <button
                            type="button"
                            class="px-2 py-0.5 rounded bg-white/5 hover:bg-white/10 border border-white/5 transition"
                            @click="emit('prefill', 'Laravel News', 'https://laravel-news.com')"
                        >
                            Laravel News
                        </button>
                        <button
                            type="button"
                            class="px-2 py-0.5 rounded bg-white/5 hover:bg-white/10 border border-white/5 transition"
                            @click="emit('prefill', 'Overreacted', 'https://overreacted.io')"
                        >
                            Overreacted
                        </button>
                    </div>
                </div>

                <form class="space-y-4" @submit.prevent="emit('submit')">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <div class="md:col-span-5">
                            <input
                                :value="form.url"
                                type="text"
                                placeholder="URL do blog (ex: https://site.com)"
                                class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"
                                @input="
                                    emit(
                                        'updateForm',
                                        'url',
                                        ($event.target as HTMLInputElement).value,
                                    )
                                "
                            />
                            <p v-if="form.errors.url" class="text-xs text-rose-400 mt-1">
                                {{ form.errors.url }}
                            </p>
                        </div>
                        <div class="md:col-span-4">
                            <input
                                :value="form.notify_email"
                                type="email"
                                placeholder="E-mail de notificação"
                                class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"
                                @input="
                                    emit(
                                        'updateForm',
                                        'notify_email',
                                        ($event.target as HTMLInputElement).value,
                                    )
                                "
                            />
                            <p v-if="form.errors.notify_email" class="text-xs text-rose-400 mt-1">
                                {{ form.errors.notify_email }}
                            </p>
                        </div>
                        <div class="md:col-span-3">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full h-full min-h-[42px] rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white font-medium text-sm transition shadow-lg shadow-indigo-500/20 disabled:opacity-50 flex items-center justify-center gap-2"
                            >
                                <svg
                                    v-if="form.processing"
                                    class="animate-spin w-4 h-4"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                        fill="none"
                                    />
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8v8H4z"
                                    />
                                </svg>
                                <span>{{
                                    form.processing ? 'Rastreando...' : 'Conectar Blog'
                                }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </section>

            <!-- Segmented Control Tabs -->
            <div
                class="flex items-center p-1 rounded-xl bg-white/[0.03] border border-white/[0.08] w-fit"
            >
                <button
                    class="px-4 py-1.5 rounded-lg text-xs font-medium transition"
                    :class="
                        activeTab === 'blogs'
                            ? 'bg-white/10 text-white shadow-sm'
                            : 'text-slate-400 hover:text-white'
                    "
                    @click="activeTab = 'blogs'"
                >
                    Fontes Ativas ({{ blogs.length }})
                </button>
                <button
                    class="px-4 py-1.5 rounded-lg text-xs font-medium transition"
                    :class="
                        activeTab === 'posts'
                            ? 'bg-white/10 text-white shadow-sm'
                            : 'text-slate-400 hover:text-white'
                    "
                    @click="activeTab = 'posts'"
                >
                    Publicações Capturadas ({{ recentPosts.length }})
                </button>
            </div>

            <!-- Blogs List -->
            <div v-if="activeTab === 'blogs'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                    v-if="blogs.length === 0"
                    class="col-span-2 text-center py-16 border border-dashed border-white/10 rounded-2xl text-slate-500 text-sm"
                >
                    Nenhum blog conectado. Adicione um link acima.
                </div>

                <div
                    v-for="blog in blogs"
                    :key="blog.id"
                    class="rounded-2xl border border-white/[0.08] bg-white/[0.02] hover:bg-white/[0.04] p-5 transition flex flex-col justify-between group"
                >
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <a
                                    :href="blog.url"
                                    target="_blank"
                                    class="text-sm font-semibold text-white hover:text-indigo-400 transition truncate block"
                                >
                                    {{ blog.title }}
                                </a>
                                <span class="text-[11px] text-slate-500 block truncate mt-0.5">{{
                                    blog.feed_url
                                }}</span>
                            </div>
                            <span
                                class="text-[10px] px-2 py-0.5 rounded-full border shrink-0"
                                :class="
                                    blog.is_active
                                        ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                                        : 'bg-slate-800 text-slate-500 border-slate-700'
                                "
                            >
                                {{ blog.is_active ? 'Ativo' : 'Pausado' }}
                            </span>
                        </div>

                        <!-- Snippet do Último Post -->
                        <div
                            v-if="blog.latest_post"
                            class="mt-4 p-3 rounded-xl bg-black/30 border border-white/5 text-xs"
                        >
                            <span class="text-[10px] text-slate-500 uppercase tracking-wider block"
                                >Último artigo detectado:</span
                            >
                            <a
                                :href="blog.latest_post.url"
                                target="_blank"
                                class="text-slate-200 hover:text-indigo-300 transition line-clamp-1 mt-0.5"
                            >
                                {{ blog.latest_post.title }}
                            </a>
                            <span class="text-[10px] text-slate-500 mt-0.5 block">{{
                                formatDate(blog.latest_post.published_at)
                            }}</span>
                        </div>
                    </div>

                    <div
                        class="mt-5 pt-3 border-t border-white/5 flex items-center justify-between text-xs"
                    >
                        <span class="text-[11px] text-slate-500">
                            Checado: {{ formatRelative(blog.last_checked_at) }}
                        </span>

                        <div class="flex items-center gap-1.5">
                            <button
                                :disabled="checkingBlogId === blog.id"
                                class="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-slate-300 text-xs transition"
                                @click="emit('checkNow', blog.id)"
                            >
                                {{ checkingBlogId === blog.id ? 'Checando...' : 'Verificar' }}
                            </button>
                            <button
                                class="px-2 py-1 text-slate-400 hover:text-white text-xs transition"
                                @click="emit('toggle', blog.id)"
                            >
                                {{ blog.is_active ? 'Pausar' : 'Ativar' }}
                            </button>
                            <button
                                class="px-2 py-1 text-rose-400/80 hover:text-rose-400 text-xs transition"
                                @click="emit('delete', blog)"
                            >
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Posts List -->
            <div v-if="activeTab === 'posts'" class="space-y-2">
                <div
                    v-for="post in recentPosts"
                    :key="post.id"
                    class="p-4 rounded-xl border border-white/[0.06] bg-white/[0.02] hover:bg-white/[0.04] transition flex items-center justify-between gap-4 text-xs"
                >
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span v-if="post.blog" class="font-medium text-indigo-400">{{
                                post.blog.title
                            }}</span>
                            <span class="text-slate-500">&bull;</span>
                            <span class="text-slate-500">{{ formatDate(post.published_at) }}</span>
                        </div>
                        <a
                            :href="post.url"
                            target="_blank"
                            class="text-white font-medium hover:text-indigo-300 transition truncate block"
                        >
                            {{ post.title }}
                        </a>
                    </div>
                    <a
                        :href="post.url"
                        target="_blank"
                        class="px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-slate-300 text-xs transition shrink-0"
                    >
                        Abrir
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
