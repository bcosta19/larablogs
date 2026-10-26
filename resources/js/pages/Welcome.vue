<script setup lang="ts">
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface BlogPostItem {
    id: number;
    blog_id: number;
    guid: string;
    title: string;
    url: string;
    published_at: string | null;
    notified_at: string | null;
    created_at: string;
    blog?: {
        id: number;
        title: string;
        url: string;
    };
}

interface BlogItem {
    id: number;
    title: string;
    url: string;
    feed_url: string;
    notify_email: string;
    is_active: boolean;
    last_checked_at: string | null;
    last_error: string | null;
    created_at: string;
    posts_count: number;
    latest_post?: BlogPostItem | null;
}

interface Props {
    blogs: BlogItem[];
    recentPosts: BlogPostItem[];
    stats: {
        total_blogs: number;
        active_blogs: number;
        total_posts: number;
    };
}

const props = defineProps<Props>();

const page = usePage();
const flash = computed(() => (page.props as any).flash || {});

const form = useForm({
    url: '',
    notify_email: '',
    title: '',
});

const checkingBlogId = ref<number | null>(null);
const togglingBlogId = ref<number | null>(null);
const deletingBlogId = ref<number | null>(null);
const activeTab = ref<'blogs' | 'posts'>('blogs');

const submitBlog = () => {
    form.post('/blogs', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const prefill = (title: string, url: string) => {
    form.title = title;
    form.url = url;
    if (!form.notify_email) {
        form.notify_email = 'leitor@exemplo.com';
    }
};

const checkNow = (blogId: number) => {
    checkingBlogId.value = blogId;
    router.post(`/blogs/${blogId}/check`, {}, {
        preserveScroll: true,
        onFinish: () => {
            checkingBlogId.value = null;
        },
    });
};

const toggleBlog = (blogId: number) => {
    togglingBlogId.value = blogId;
    router.patch(`/blogs/${blogId}/toggle`, {}, {
        preserveScroll: true,
        onFinish: () => {
            togglingBlogId.value = null;
        },
    });
};

const deleteBlog = (blog: BlogItem) => {
    if (confirm(`Deseja realmente parar de acompanhar o blog "${blog.title}"? Todos os artigos associados serão removidos.`)) {
        deletingBlogId.value = blog.id;
        router.delete(`/blogs/${blog.id}`, {
            preserveScroll: true,
            onFinish: () => {
                deletingBlogId.value = null;
            },
        });
    }
};

const formatDate = (dateStr: string | null): string => {
    if (!dateStr) return 'Nunca';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return dateStr;
    }
};

const formatRelativeTime = (dateStr: string | null): string => {
    if (!dateStr) return 'Nunca verificado';
    try {
        const d = new Date(dateStr);
        const now = new Date();
        const diffInSeconds = Math.floor((now.getTime() - d.getTime()) / 1000);

        if (diffInSeconds < 60) return 'Agora mesmo';
        if (diffInSeconds < 3600) return `Há ${Math.floor(diffInSeconds / 60)} min`;
        if (diffInSeconds < 86400) return `Há ${Math.floor(diffInSeconds / 3600)} h`;
        return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <Head title="Larablogs - Monitor de Blogs e Feeds RSS" />

    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans selection:bg-amber-500 selection:text-slate-950">
        <!-- Barra de Navegação Superior -->
        <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur sticky top-0 z-30">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-600 to-orange-500 flex items-center justify-center text-slate-950 shadow-lg shadow-orange-500/20 font-black">
                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                            <path d="M6.18 15.64a2.18 2.18 0 0 1 2.18 2.18C8.36 19 7.38 20 6.18 20C5 20 4 19 4 17.82a2.18 2.18 0 0 1 2.18-2.18M4 4.44A15.56 15.56 0 0 1 19.56 20h-2.83A12.73 12.73 0 0 0 4 7.27V4.44m0 5.66a9.9 9.9 0 0 1 9.9 9.9h-2.83A7.07 7.07 0 0 0 4 12.93v-2.83Z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xl font-bold tracking-tight bg-gradient-to-r from-amber-400 via-orange-400 to-amber-200 bg-clip-text text-transparent">Larablogs</span>
                        <span class="ml-2 text-xs font-mono uppercase px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20">RSS Radar</span>
                    </div>
                </div>

                <!-- Estatísticas Rápidas -->
                <div class="flex items-center space-x-4 text-xs">
                    <div class="hidden sm:flex items-center space-x-2 bg-slate-800/60 border border-slate-700/60 rounded-full px-3 py-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-slate-400">Ativos:</span>
                        <span class="font-semibold text-emerald-400">{{ stats.active_blogs }} / {{ stats.total_blogs }}</span>
                    </div>
                    <div class="hidden md:flex items-center space-x-2 bg-slate-800/60 border border-slate-700/60 rounded-full px-3 py-1.5">
                        <span class="text-slate-400">Artigos monitorados:</span>
                        <span class="font-semibold text-amber-400">{{ stats.total_posts }}</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Mensagens Flash de Sucesso / Erro / Info -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 w-full pt-4">
            <div v-if="flash.success" class="mb-4 p-4 rounded-xl bg-emerald-950/80 border border-emerald-500/30 text-emerald-300 flex items-start gap-3 shadow-lg shadow-emerald-950/40">
                <svg class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1 text-sm font-medium">{{ flash.success }}</div>
            </div>

            <div v-if="flash.error" class="mb-4 p-4 rounded-xl bg-rose-950/80 border border-rose-500/30 text-rose-300 flex items-start gap-3 shadow-lg shadow-rose-950/40">
                <svg class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1 text-sm font-medium">{{ flash.error }}</div>
            </div>

            <div v-if="flash.info" class="mb-4 p-4 rounded-xl bg-sky-950/80 border border-sky-500/30 text-sky-300 flex items-start gap-3 shadow-lg shadow-sky-950/40">
                <svg class="w-5 h-5 text-sky-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1 text-sm font-medium">{{ flash.info }}</div>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <main class="max-w-6xl mx-auto px-4 sm:px-6 w-full py-6 flex-1 space-y-8">
            <!-- Hero / Chamada com Formulário de Adicionar Blog -->
            <section class="relative overflow-hidden rounded-2xl bg-gradient-to-b from-slate-900 to-slate-900/90 border border-slate-800 p-6 sm:p-8 shadow-2xl">
                <div class="absolute -top-24 -right-24 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-orange-600/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 max-w-3xl">
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                        Acompanhe blogs e receba notificações instantâneas
                    </h1>
                    <p class="mt-2 text-sm sm:text-base text-slate-400 leading-relaxed">
                        Basta colar a URL do site ou blog que você curte. O Larablogs detecta o feed RSS/Atom automaticamente e envia um e-mail para você assim que uma nova postagem for ao ar.
                    </p>

                    <!-- Formulário -->
                    <form @submit.prevent="submitBlog" class="mt-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                            <!-- URL do Blog -->
                            <div class="sm:col-span-5">
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                    URL do Blog ou Site <span class="text-amber-400">*</span>
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="form.url"
                                        type="text"
                                        placeholder="https://exemplo.com ou https://exemplo.com/feed"
                                        class="w-full rounded-xl bg-slate-950/80 border px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 transition-all"
                                        :class="form.errors.url ? 'border-rose-500 ring-rose-500/20' : 'border-slate-700/80 focus:border-amber-500'"
                                    />
                                </div>
                                <p v-if="form.errors.url" class="mt-1 text-xs text-rose-400 font-medium">
                                    {{ form.errors.url }}
                                </p>
                            </div>

                            <!-- E-mail para Notificação -->
                            <div class="sm:col-span-4">
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                    Notificar no e-mail <span class="text-amber-400">*</span>
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="form.notify_email"
                                        type="email"
                                        placeholder="seu-email@dominio.com"
                                        class="w-full rounded-xl bg-slate-950/80 border px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 transition-all"
                                        :class="form.errors.notify_email ? 'border-rose-500 ring-rose-500/20' : 'border-slate-700/80 focus:border-amber-500'"
                                    />
                                </div>
                                <p v-if="form.errors.notify_email" class="mt-1 text-xs text-rose-400 font-medium">
                                    {{ form.errors.notify_email }}
                                </p>
                            </div>

                            <!-- Nome Opcional -->
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-semibold text-slate-400 mb-1.5">
                                    Apelido / Título <span class="text-slate-500 text-[10px]">(opcional)</span>
                                </label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    placeholder="Ex: Laravel News"
                                    class="w-full rounded-xl bg-slate-950/80 border border-slate-700/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500 transition-all"
                                />
                            </div>
                        </div>

                        <!-- Botão de Ação e Sugestões Rápidas -->
                        <div class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <!-- Sugestões para teste rápido -->
                            <div class="flex items-center gap-2 flex-wrap text-xs text-slate-400">
                                <span>Experimente:</span>
                                <button
                                    type="button"
                                    @click="prefill('Laravel News', 'https://laravel-news.com')"
                                    class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition"
                                >
                                    Laravel News
                                </button>
                                <button
                                    type="button"
                                    @click="prefill('Overreacted', 'https://overreacted.io')"
                                    class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition"
                                >
                                    Overreacted
                                </button>
                                <button
                                    type="button"
                                    @click="prefill('GitHub Blog', 'https://github.blog')"
                                    class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition"
                                >
                                    GitHub Blog
                                </button>
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-bold text-sm shadow-lg shadow-orange-500/25 transition-all disabled:opacity-50 disabled:cursor-not-allowed shrink-0"
                            >
                                <svg v-if="form.processing" class="animate-spin w-4 h-4" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>{{ form.processing ? 'Descobrindo Feed...' : 'Acompanhar Blog' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Abas de Navegação Entre Blogs e Histórico de Artigos -->
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                    <button
                        @click="activeTab = 'blogs'"
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition flex items-center gap-2"
                        :class="activeTab === 'blogs' ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'text-slate-400 hover:text-slate-200'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                        <span>Blogs Monitorados ({{ blogs.length }})</span>
                    </button>

                    <button
                        @click="activeTab = 'posts'"
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition flex items-center gap-2"
                        :class="activeTab === 'posts' ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'text-slate-400 hover:text-slate-200'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Últimas Publicações Detectadas ({{ recentPosts.length }})</span>
                    </button>
                </div>
            </div>

            <!-- Aba 1: Lista de Blogs Cadastrados -->
            <div v-if="activeTab === 'blogs'" class="space-y-4">
                <!-- Estado Vazio -->
                <div v-if="blogs.length === 0" class="text-center py-16 px-4 border border-dashed border-slate-800 rounded-2xl bg-slate-900/30">
                    <div class="w-16 h-16 rounded-full bg-slate-800 flex items-center justify-center mx-auto text-slate-500 mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 5c7.18 0 13 5.82 13 13M6 11a7 7 0 017 7m-6 0a1 1 0 11-2 0 1 1 0 012 0z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-200">Nenhum blog cadastrado ainda</h3>
                    <p class="mt-1 text-sm text-slate-400 max-w-sm mx-auto">
                        Adicione a URL do primeiro blog no formulário acima para começar a receber notificações automáticas.
                    </p>
                </div>

                <!-- Grid de Blogs -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="blog in blogs"
                        :key="blog.id"
                        class="rounded-2xl border bg-slate-900/80 p-5 transition flex flex-col justify-between"
                        :class="blog.is_active ? 'border-slate-800 hover:border-slate-700' : 'border-slate-800/50 opacity-75'"
                    >
                        <div>
                            <!-- Cabeçalho do Card -->
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <a
                                            :href="blog.url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-base font-bold text-white hover:text-amber-400 transition truncate inline-flex items-center gap-1.5"
                                        >
                                            <span class="truncate">{{ blog.title }}</span>
                                            <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                    </div>
                                    <p class="text-xs text-slate-400 truncate mt-0.5">
                                        Feed: <a :href="blog.feed_url" target="_blank" class="text-slate-500 hover:underline">{{ blog.feed_url }}</a>
                                    </p>
                                </div>

                                <!-- Badge de Status -->
                                <button
                                    @click="toggleBlog(blog.id)"
                                    :disabled="togglingBlogId === blog.id"
                                    class="shrink-0 text-[11px] font-semibold px-2.5 py-1 rounded-full border transition flex items-center gap-1.5"
                                    :class="blog.is_active
                                        ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/20'
                                        : 'bg-slate-800 text-slate-400 border-slate-700 hover:bg-slate-700'"
                                    :title="blog.is_active ? 'Clique para pausar monitoramento' : 'Clique para retomar monitoramento'"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full" :class="blog.is_active ? 'bg-emerald-400' : 'bg-slate-500'"></span>
                                    <span>{{ blog.is_active ? 'Ativo' : 'Pausado' }}</span>
                                </button>
                            </div>

                            <!-- Informações de Monitoramento -->
                            <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                                <div class="bg-slate-950/60 rounded-xl p-2.5 border border-slate-800/80">
                                    <span class="text-slate-500 block text-[10px] uppercase font-mono tracking-wider">Notificar em</span>
                                    <span class="font-medium text-slate-200 truncate block mt-0.5" :title="blog.notify_email">
                                        ✉️ {{ blog.notify_email }}
                                    </span>
                                </div>
                                <div class="bg-slate-950/60 rounded-xl p-2.5 border border-slate-800/80">
                                    <span class="text-slate-500 block text-[10px] uppercase font-mono tracking-wider">Última checagem</span>
                                    <span class="font-medium text-slate-200 block mt-0.5">
                                        ⏱ {{ formatRelativeTime(blog.last_checked_at) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Último Post Detectado -->
                            <div class="mt-3 bg-slate-950/40 rounded-xl p-3 border border-slate-800/60">
                                <span class="text-[10px] text-slate-400 font-mono uppercase tracking-wider block">Último artigo registrado:</span>
                                <div v-if="blog.latest_post" class="mt-1">
                                    <a
                                        :href="blog.latest_post.url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-xs font-medium text-amber-300 hover:text-amber-200 hover:underline line-clamp-1"
                                    >
                                        {{ blog.latest_post.title }}
                                    </a>
                                    <span class="text-[10px] text-slate-500 block mt-0.5">
                                        Publicado em {{ formatDate(blog.latest_post.published_at) }}
                                    </span>
                                </div>
                                <div v-else class="text-xs text-slate-500 italic mt-0.5">
                                    Nenhum artigo arquivado ainda
                                </div>
                            </div>

                            <!-- Alerta de Erro se houver -->
                            <div v-if="blog.last_error" class="mt-2 text-xs text-rose-400 bg-rose-950/50 border border-rose-900/50 rounded-lg p-2">
                                ⚠ Erro na última verificação: {{ blog.last_error }}
                            </div>
                        </div>

                        <!-- Rodapé de Ações do Card -->
                        <div class="mt-5 pt-3 border-t border-slate-800 flex items-center justify-between text-xs">
                            <span class="text-slate-500">
                                {{ blog.posts_count }} post(s) registrados
                            </span>

                            <div class="flex items-center gap-2">
                                <!-- Botão Verificar Agora -->
                                <button
                                    @click="checkNow(blog.id)"
                                    :disabled="checkingBlogId === blog.id"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition disabled:opacity-50"
                                    title="Forçar verificação imediata do feed"
                                >
                                    <svg
                                        class="w-3.5 h-3.5 text-amber-400"
                                        :class="checkingBlogId === blog.id ? 'animate-spin' : ''"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span>{{ checkingBlogId === blog.id ? 'Checando...' : 'Verificar agora' }}</span>
                                </button>

                                <!-- Botão Excluir -->
                                <button
                                    @click="deleteBlog(blog)"
                                    :disabled="deletingBlogId === blog.id"
                                    class="p-1.5 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-950/30 transition"
                                    title="Excluir blog"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aba 2: Lista dos Últimos Posts Detectados -->
            <div v-if="activeTab === 'posts'" class="space-y-4">
                <div v-if="recentPosts.length === 0" class="text-center py-16 px-4 border border-dashed border-slate-800 rounded-2xl bg-slate-900/30">
                    <p class="text-sm text-slate-400">Nenhum post registrado ainda.</p>
                </div>

                <div v-else class="space-y-2.5">
                    <div
                        v-for="post in recentPosts"
                        :key="post.id"
                        class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 hover:border-slate-700/80 transition flex items-center justify-between gap-4"
                    >
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <span v-if="post.blog" class="text-[11px] font-semibold px-2 py-0.5 rounded bg-slate-800 text-amber-400 border border-slate-700">
                                    {{ post.blog.title }}
                                </span>
                                <span class="text-xs text-slate-500">
                                    {{ formatDate(post.published_at) }}
                                </span>
                                <span v-if="post.notified_at" class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-emerald-950 text-emerald-400 border border-emerald-900">
                                    ✉ Notificado
                                </span>
                            </div>
                            <a
                                :href="post.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-sm font-semibold text-slate-200 hover:text-amber-300 transition line-clamp-1"
                            >
                                {{ post.title }}
                            </a>
                        </div>

                        <a
                            :href="post.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-300 transition"
                        >
                            <span>Ler artigo</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Dicas de Operação e Verificação Automática -->
            <section class="rounded-xl border border-slate-800/80 bg-slate-900/40 p-5 text-xs text-slate-400 space-y-2">
                <div class="flex items-center gap-2 font-semibold text-slate-200">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Como funcionam as notificações automáticas no Larablogs:</span>
                </div>
                <p>
                    1. <strong>Descoberta Inteligente:</strong> Ao colar uma URL como <code class="text-amber-300 bg-slate-950 px-1 py-0.5 rounded">https://laravel-news.com</code>, o sistema lê as tags do site para localizar o endereço do feed RSS ou Atom correspondente.
                </p>
                <p>
                    2. <strong>Linha de Base Inicial:</strong> Ao cadastrar um blog, as postagens já publicadas são salvas como ponto de partida (baseline) para não sobrecarregar sua caixa de entrada. Apenas <em>novas</em> postagens publicadas a partir de então geram e-mails de alerta.
                </p>
                <p>
                    3. <strong>Agendador em Segundo Plano:</strong> No servidor ou ambiente de produção, execute o scheduler do Laravel (<code class="text-amber-300 bg-slate-950 px-1 py-0.5 rounded">php artisan schedule:run</code>) ou manualmente <code class="text-amber-300 bg-slate-950 px-1 py-0.5 rounded">php artisan blogs:check</code> para checar periodicamente. Em desenvolvimento local, os e-mails são registrados em <code class="text-amber-300 bg-slate-950 px-1 py-0.5 rounded">storage/logs/laravel.log</code>.
                </p>
            </section>
        </main>

        <!-- Rodapé -->
        <footer class="border-t border-slate-800/80 bg-slate-950 py-6 text-center text-xs text-slate-500">
            <div class="max-w-6xl mx-auto px-4">
                <span>Larablogs &bull; Construído com Laravel, Vue.js e Inertia.js</span>
            </div>
        </footer>
    </div>
</template>
