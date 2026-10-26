<script setup lang="ts">
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import type { BlogItem, BlogPostItem, BlogStats, FlashMessages } from '@/types';

interface Props {
    blogs: BlogItem[];
    recentPosts: BlogPostItem[];
    stats: BlogStats;
}

const props = defineProps<Props>();

const page = usePage();
const flash = computed<FlashMessages>(() => (page.props as any).flash || {});

const form = useForm({
    url: '',
    notify_email: '',
    title: '',
});

const checkingBlogId = ref<number | null>(null);
const togglingBlogId = ref<number | null>(null);
const deletingBlogId = ref<number | null>(null);
const activeTab = ref<'blogs' | 'posts'>('blogs');

// Modo Escuro (Padrão: escuro)
const isDark = ref(true);

onMounted(() => {
    const saved = localStorage.getItem('larablogs_theme_mode');
    if (saved) {
        isDark.value = saved === 'dark';
    } else {
        isDark.value = true; // Padrão escuro
    }
    applyTheme();
});

const toggleTheme = () => {
    isDark.value = !isDark.value;
    localStorage.setItem('larablogs_theme_mode', isDark.value ? 'dark' : 'light');
    applyTheme();
};

const applyTheme = () => {
    if (isDark.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

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
    if (confirm(`Deseja remover "${blog.title}" do monitoramento? Todos os artigos associados serão removidos.`)) {
        deletingBlogId.value = blog.id;
        router.delete(`/blogs/${blog.id}`, {
            preserveScroll: true,
            onFinish: () => {
                deletingBlogId.value = null;
            },
        });
    }
};

const getDomain = (url: string): string => {
    try {
        const u = new URL(url.startsWith('http') ? url : `https://${url}`);
        return u.hostname;
    } catch {
        return url;
    }
};

const getFaviconUrl = (url: string): string => {
    const domain = getDomain(url);
    return `https://www.google.com/s2/favicons?domain=${domain}&sz=64`;
};

const handleImgError = (event: Event) => {
    const target = event.target as HTMLElement;
    target.style.display = 'none';
};

const formatDate = (dateStr: string | null): string => {
    if (!dateStr) return 'Data não informada';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return dateStr;
    }
};

const formatRelative = (dateStr: string | null): string => {
    if (!dateStr) return 'Nunca verificado';
    try {
        const d = new Date(dateStr);
        const diff = Math.floor((Date.now() - d.getTime()) / 1000);
        if (diff < 60) return 'Agora';
        if (diff < 3600) return `${Math.floor(diff / 60)} min atrás`;
        if (diff < 86400) return `${Math.floor(diff / 3600)} h atrás`;
        return d.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' });
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <Head title="Larablogs - Monitor Editorial de Blogs & RSS" />

    <div class="min-h-screen bg-[#FBF9F5] dark:bg-[#141210] text-[#2C2825] dark:text-[#DCD7CE] font-serif transition-colors duration-200 selection:bg-[#EAE5DC] dark:selection:bg-[#38332B] selection:text-[#1A1816] dark:selection:text-[#F5F2EB]">
        <!-- Cabeçalho Editorial com Borda Dupla Clássica -->
        <header class="border-b border-[#E6E1D8] dark:border-[#2D2A26] bg-[#FBF9F5] dark:bg-[#141210] transition-colors duration-200">
            <div class="max-w-4xl mx-auto px-6 pt-6 pb-8 relative">
                <!-- Botão Alternador de Tema Claro / Escuro -->
                <div class="flex justify-end mb-2">
                    <button
                        @click="toggleTheme"
                        type="button"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-[#D5CEC2] dark:border-[#38342E] bg-white dark:bg-[#1C1A18] hover:bg-[#FAF8F5] dark:hover:bg-[#25221E] text-xs font-sans text-[#544E46] dark:text-[#B5ADA3] transition shadow-2xs"
                        :title="isDark ? 'Mudar para tema claro' : 'Mudar para tema escuro'"
                    >
                        <!-- Ícone do Sol (quando em Dark) -->
                        <svg v-if="isDark" class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <!-- Ícone da Lua (quando em Light) -->
                        <svg v-else class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <span class="font-medium">{{ isDark ? 'Tema Claro' : 'Tema Escuro' }}</span>
                    </button>
                </div>

                <div class="text-center">
                    <div class="inline-block border-b-2 border-t-2 border-[#2C2825] dark:border-[#DCD7CE] py-1 px-4 mb-3">
                        <span class="font-sans text-[11px] tracking-[0.25em] uppercase font-bold text-[#6B635B] dark:text-[#A8A096]">
                            Boletim Digital &bull; Monitor de Feeds
                        </span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-normal tracking-tight text-[#1C1A18] dark:text-[#F5F2EB]">
                        Larablogs
                    </h1>
                    <p class="mt-2 text-base text-[#756D63] dark:text-[#9C9488] font-sans italic max-w-lg mx-auto">
                        Curadoria contínua de publicações da web com avisos diretos na sua caixa postal.
                    </p>

                    <!-- Estatísticas Estilo Folhetim -->
                    <div class="mt-6 flex items-center justify-center gap-6 text-xs font-sans text-[#756D63] dark:text-[#9C9488] border-t border-[#EAE5DC] dark:border-[#2A2723] pt-4">
                        <span><strong>{{ stats.total_blogs }}</strong> publicações inscritas</span>
                        <span>&bull;</span>
                        <span><strong>{{ stats.active_blogs }}</strong> ativas</span>
                        <span>&bull;</span>
                        <span><strong>{{ stats.total_posts }}</strong> artigos arquivados</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Mensagens de Alerta Flash -->
        <div class="max-w-4xl mx-auto px-6 pt-6 font-sans">
            <div v-if="flash.success" class="p-4 bg-[#F2F7F2] dark:bg-[#122E1B]/80 border-l-4 border-[#2E6F40] dark:border-[#3B8A52] text-[#1E4A2B] dark:text-[#A7F3D0] text-sm mb-4 shadow-xs">
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="p-4 bg-[#FDF2F2] dark:bg-[#3D1414]/80 border-l-4 border-[#A32D2D] dark:border-[#EF4444] text-[#6E1C1C] dark:text-[#FECDD3] text-sm mb-4 shadow-xs">
                {{ flash.error }}
            </div>
            <div v-if="flash.info" class="p-4 bg-[#F3F6FA] dark:bg-[#142338]/80 border-l-4 border-[#2D5A8A] dark:border-[#38BDF8] text-[#1C3A5A] dark:text-[#BAE6FD] text-sm mb-4 shadow-xs">
                {{ flash.info }}
            </div>
        </div>

        <main class="max-w-4xl mx-auto px-6 py-6 space-y-10">
            <!-- Formulário no Estilo Ficha Catalográfica -->
            <section class="bg-white dark:bg-[#1C1A18] border border-[#E4DFD5] dark:border-[#2D2A26] p-8 shadow-xs transition-colors duration-200">
                <h2 class="text-xl font-normal text-[#1A1816] dark:text-[#F5F2EB] mb-1">
                    Inscrever nova publicação
                </h2>
                <p class="text-xs font-sans text-[#756D63] dark:text-[#9C9488] mb-6">
                    Insira o endereço do blog ou feed RSS para monitoramento e receba novas matérias.
                </p>

                <form @submit.prevent="submitBlog" class="space-y-5 font-sans">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#544E46] dark:text-[#B5ADA3] mb-1">
                                Endereço Web (URL) *
                            </label>
                            <input
                                v-model="form.url"
                                type="text"
                                placeholder="https://exemplo.com ou feed RSS"
                                class="w-full bg-[#FAF8F5] dark:bg-[#141210] border border-[#D5CEC2] dark:border-[#38342E] px-3.5 py-2.5 text-sm text-[#2C2825] dark:text-[#F5F2EB] placeholder-[#9E978E] dark:placeholder-[#6B655D] focus:outline-none focus:border-[#2C2825] dark:focus:border-[#F5F2EB] focus:bg-white dark:focus:bg-[#181614] transition"
                            />
                            <p v-if="form.errors.url" class="text-xs text-[#A32D2D] dark:text-[#F87171] mt-1">{{ form.errors.url }}</p>
                        </div>

                        <div class="md:col-span-4">
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#544E46] dark:text-[#B5ADA3] mb-1">
                                E-mail Destinatário *
                            </label>
                            <input
                                v-model="form.notify_email"
                                type="email"
                                placeholder="seu@email.com"
                                class="w-full bg-[#FAF8F5] dark:bg-[#141210] border border-[#D5CEC2] dark:border-[#38342E] px-3.5 py-2.5 text-sm text-[#2C2825] dark:text-[#F5F2EB] placeholder-[#9E978E] dark:placeholder-[#6B655D] focus:outline-none focus:border-[#2C2825] dark:focus:border-[#F5F2EB] focus:bg-white dark:focus:bg-[#181614] transition"
                            />
                            <p v-if="form.errors.notify_email" class="text-xs text-[#A32D2D] dark:text-[#F87171] mt-1">{{ form.errors.notify_email }}</p>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#756D63] dark:text-[#9C9488] mb-1">
                                Título (Opcional)
                            </label>
                            <input
                                v-model="form.title"
                                type="text"
                                placeholder="Automático"
                                class="w-full bg-[#FAF8F5] dark:bg-[#141210] border border-[#D5CEC2] dark:border-[#38342E] px-3.5 py-2.5 text-sm text-[#2C2825] dark:text-[#F5F2EB] placeholder-[#9E978E] dark:placeholder-[#6B655D] focus:outline-none focus:border-[#2C2825] dark:focus:border-[#F5F2EB] focus:bg-white dark:focus:bg-[#181614] transition"
                            />
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2 border-t border-[#EFEBE4] dark:border-[#2A2723]">
                        <div class="flex items-center gap-2 text-xs text-[#756D63] dark:text-[#9C9488]">
                            <span>Sugestões:</span>
                            <button type="button" @click="prefill('Laravel News', 'https://laravel-news.com')" class="underline hover:text-[#1A1816] dark:hover:text-[#F5F2EB]">Laravel News</button>
                            <span>&bull;</span>
                            <button type="button" @click="prefill('Overreacted', 'https://overreacted.io')" class="underline hover:text-[#1A1816] dark:hover:text-[#F5F2EB]">Overreacted</button>
                            <span>&bull;</span>
                            <button type="button" @click="prefill('GitHub Blog', 'https://github.blog')" class="underline hover:text-[#1A1816] dark:hover:text-[#F5F2EB]">GitHub Blog</button>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 bg-[#2C2825] dark:bg-[#F5F2EB] hover:bg-[#1A1816] dark:hover:bg-white text-[#FBF9F5] dark:text-[#141210] text-xs font-bold uppercase tracking-widest transition disabled:opacity-50"
                        >
                            {{ form.processing ? 'Consultando Feed...' : 'Adicionar ao Registro' }}
                        </button>
                    </div>
                </form>
            </section>

            <!-- Abas com Estilo Editorial -->
            <div class="flex items-center gap-6 border-b border-[#E0DACE] dark:border-[#2D2A26] font-sans text-sm">
                <button
                    @click="activeTab = 'blogs'"
                    class="pb-2.5 font-bold transition border-b-2 -mb-px flex items-center gap-2"
                    :class="activeTab === 'blogs'
                        ? 'border-[#2C2825] dark:border-[#F5F2EB] text-[#1A1816] dark:text-[#F5F2EB]'
                        : 'border-transparent text-[#756D63] dark:text-[#9C9488] hover:text-[#2C2825] dark:hover:text-[#F5F2EB]'"
                >
                    <span>Publicações Cadastradas</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-[#EAE5DC] dark:bg-[#2A2723] text-[#4A443D] dark:text-[#DCD7CE]">{{ blogs.length }}</span>
                </button>
                <button
                    @click="activeTab = 'posts'"
                    class="pb-2.5 font-bold transition border-b-2 -mb-px flex items-center gap-2"
                    :class="activeTab === 'posts'
                        ? 'border-[#2C2825] dark:border-[#F5F2EB] text-[#1A1816] dark:text-[#F5F2EB]'
                        : 'border-transparent text-[#756D63] dark:text-[#9C9488] hover:text-[#2C2825] dark:hover:text-[#F5F2EB]'"
                >
                    <span>Artigos Recentes</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-[#EAE5DC] dark:bg-[#2A2723] text-[#4A443D] dark:text-[#DCD7CE]">{{ recentPosts.length }}</span>
                </button>
            </div>

            <!-- Aba 1: Publicações Cadastradas com Ícones e Tipografia -->
            <div v-if="activeTab === 'blogs'" class="space-y-4">
                <div v-if="blogs.length === 0" class="text-center py-16 bg-white dark:bg-[#1C1A18] border border-[#E4DFD5] dark:border-[#2D2A26] text-[#756D63] dark:text-[#9C9488] font-sans text-sm">
                    Nenhuma publicação cadastrada no momento. Adicione a primeira acima.
                </div>

                <div
                    v-for="blog in blogs"
                    :key="blog.id"
                    class="bg-white dark:bg-[#1C1A18] border border-[#E4DFD5] dark:border-[#2D2A26] p-6 transition hover:border-[#D5CEC2] dark:hover:border-[#403B35]"
                >
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <!-- Identificação do Blog com Ícone -->
                        <div class="flex items-start gap-3.5 min-w-0">
                            <!-- Ícone do Blog (Favicon com Fallback em Monograma) -->
                            <div class="relative w-11 h-11 rounded-lg border border-[#E0DACE] dark:border-[#38342E] bg-[#FAF8F5] dark:bg-[#141210] p-1.5 flex items-center justify-center shrink-0 overflow-hidden shadow-xs">
                                <span class="font-serif text-lg font-bold text-[#6B635B] dark:text-[#A8A096] select-none">
                                    {{ blog.title ? blog.title.charAt(0).toUpperCase() : 'B' }}
                                </span>
                                <img
                                    :src="getFaviconUrl(blog.url)"
                                    :alt="blog.title"
                                    class="absolute inset-0 w-full h-full object-contain p-1.5 rounded-lg bg-[#FAF8F5] dark:bg-[#141210]"
                                    loading="lazy"
                                    @error="handleImgError"
                                />
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <a
                                        :href="blog.url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-xl font-normal text-[#1A1816] dark:text-[#F5F2EB] hover:underline truncate"
                                    >
                                        {{ blog.title }}
                                    </a>

                                    <span
                                        class="font-sans text-[10px] uppercase tracking-wider px-2 py-0.5 border"
                                        :class="blog.is_active
                                            ? 'border-[#2E6F40] dark:border-[#3B8A52] text-[#2E6F40] dark:text-[#6EE7B7] bg-[#F4F9F4] dark:bg-[#122E1B]'
                                            : 'border-[#948B80] dark:border-[#5A534B] text-[#948B80] dark:text-[#A8A096] bg-[#FAF8F5] dark:bg-[#201D1A]'"
                                    >
                                        {{ blog.is_active ? 'Ativo' : 'Pausado' }}
                                    </span>
                                </div>

                                <p class="font-sans text-xs text-[#756D63] dark:text-[#9C9488] mt-1 truncate">
                                    Notificar: <strong class="text-[#2C2825] dark:text-[#F5F2EB]">{{ blog.notify_email }}</strong>
                                    <span class="mx-1">&bull;</span>
                                    <span>Verificado: {{ formatRelative(blog.last_checked_at) }}</span>
                                    <span class="mx-1">&bull;</span>
                                    <span>{{ blog.posts_count }} artigo(s) arquivados</span>
                                </p>
                            </div>
                        </div>

                        <!-- Barra de Ações -->
                        <div class="flex items-center gap-2 font-sans text-xs shrink-0 self-end sm:self-start">
                            <button
                                @click="checkNow(blog.id)"
                                :disabled="checkingBlogId === blog.id"
                                class="px-3 py-1.5 border border-[#D5CEC2] dark:border-[#38342E] bg-[#FAF8F5] dark:bg-[#141210] hover:bg-white dark:hover:bg-[#25221E] text-[#2C2825] dark:text-[#EAE6DF] transition disabled:opacity-50"
                                title="Verificar agora se há novos artigos no feed"
                            >
                                {{ checkingBlogId === blog.id ? 'Consultando...' : 'Verificar agora' }}
                            </button>

                            <button
                                @click="toggleBlog(blog.id)"
                                :disabled="togglingBlogId === blog.id"
                                class="px-3 py-1.5 text-[#756D63] dark:text-[#9C9488] hover:text-[#1A1816] dark:hover:text-[#F5F2EB] transition"
                            >
                                {{ blog.is_active ? 'Pausar' : 'Reativar' }}
                            </button>

                            <button
                                @click="deleteBlog(blog)"
                                :disabled="deletingBlogId === blog.id"
                                class="px-2.5 py-1.5 text-[#A32D2D] dark:text-[#F87171] hover:underline transition"
                            >
                                Remover
                            </button>
                        </div>
                    </div>

                    <!-- Último Post Registrado -->
                    <div v-if="blog.latest_post" class="mt-4 pt-3 border-t border-[#F0EBE3] dark:border-[#2A2723] font-sans text-xs">
                        <span class="uppercase tracking-wider text-[10px] text-[#948B80] dark:text-[#7A7368] font-bold">Último artigo registrado:</span>
                        <a
                            :href="blog.latest_post.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="block font-serif text-sm text-[#1A1816] dark:text-[#F5F2EB] hover:underline mt-0.5"
                        >
                            {{ blog.latest_post.title }}
                        </a>
                        <span class="text-[11px] text-[#948B80] dark:text-[#7A7368] block mt-0.5">
                            Publicado em {{ formatDate(blog.latest_post.published_at) }}
                        </span>
                    </div>

                    <!-- Mensagem de Erro se houver -->
                    <div v-if="blog.last_error" class="mt-3 p-2 bg-[#FDF2F2] dark:bg-[#3D1414]/70 border border-[#F5C2C2] dark:border-[#EF4444]/40 text-xs font-sans text-[#A32D2D] dark:text-[#FECDD3]">
                        Aviso na última checagem: {{ blog.last_error }}
                    </div>
                </div>
            </div>

            <!-- Aba 2: Artigos Recentes com Ícone da Publicação -->
            <div v-if="activeTab === 'posts'" class="space-y-3">
                <div v-if="recentPosts.length === 0" class="text-center py-16 bg-white dark:bg-[#1C1A18] border border-[#E4DFD5] dark:border-[#2D2A26] text-[#756D63] dark:text-[#9C9488] font-sans text-sm">
                    Nenhum artigo arquivado ainda.
                </div>

                <div
                    v-for="post in recentPosts"
                    :key="post.id"
                    class="bg-white dark:bg-[#1C1A18] border border-[#E4DFD5] dark:border-[#2D2A26] p-5 transition hover:border-[#D5CEC2] dark:hover:border-[#403B35] flex items-start justify-between gap-4"
                >
                    <div class="flex items-start gap-3 min-w-0">
                        <!-- Mini Favicon do Blog no Artigo -->
                        <div v-if="post.blog" class="relative w-6 h-6 rounded border border-[#E0DACE] dark:border-[#38342E] bg-[#FAF8F5] dark:bg-[#141210] p-0.5 flex items-center justify-center shrink-0 mt-0.5 overflow-hidden">
                            <span class="font-serif text-[10px] font-bold text-[#6B635B] dark:text-[#A8A096] select-none">
                                {{ post.blog.title.charAt(0).toUpperCase() }}
                            </span>
                            <img
                                :src="getFaviconUrl(post.blog.url)"
                                :alt="post.blog.title"
                                class="absolute inset-0 w-full h-full object-contain p-0.5 rounded bg-[#FAF8F5] dark:bg-[#141210]"
                                loading="lazy"
                                @error="handleImgError"
                            />
                        </div>

                        <div class="min-w-0">
                            <div class="font-sans text-xs text-[#756D63] dark:text-[#9C9488] flex items-center gap-2 flex-wrap mb-1">
                                <span v-if="post.blog" class="font-bold text-[#2C2825] dark:text-[#F5F2EB]">
                                    {{ post.blog.title }}
                                </span>
                                <span>&bull;</span>
                                <span>{{ formatDate(post.published_at) }}</span>
                                <span v-if="post.notified_at" class="text-[10px] uppercase font-mono px-1.5 py-0.2 bg-[#F2F7F2] dark:bg-[#122E1B] text-[#2E6F40] dark:text-[#6EE7B7] border border-[#CDE5D1] dark:border-[#1E4D2B]">
                                    ✉ Notificado
                                </span>
                            </div>

                            <a
                                :href="post.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="block font-serif text-base text-[#1A1816] dark:text-[#F5F2EB] hover:underline"
                            >
                                {{ post.title }}
                            </a>
                        </div>
                    </div>

                    <a
                        :href="post.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="font-sans text-xs px-3 py-1.5 border border-[#D5CEC2] dark:border-[#38342E] bg-[#FAF8F5] dark:bg-[#141210] hover:bg-white dark:hover:bg-[#25221E] text-[#2C2825] dark:text-[#EAE6DF] shrink-0 transition"
                    >
                        Ler artigo &rarr;
                    </a>
                </div>
            </div>

            <!-- Rodapé Explicativo -->
            <footer class="pt-8 border-t border-[#EAE5DC] dark:border-[#2A2723] text-center font-sans text-xs text-[#948B80] dark:text-[#7A7368] space-y-1">
                <p>Larablogs &bull; Edição Pessoal &bull; Desenvolvido com Laravel 13, Vue.js 3 e Inertia.js</p>
                <p>Verificação automática em segundo plano via <code class="text-[#2C2825] dark:text-[#DCD7CE] bg-[#EFEBE4] dark:bg-[#201D1A] px-1.5 py-0.5 rounded">php artisan blogs:check</code></p>
            </footer>
        </main>
    </div>
</template>
