<script setup lang="ts">
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

const formatRelative = (dateStr: string | null): string => {
    if (!dateStr) return 'Nunca';
    try {
        const d = new Date(dateStr);
        const diff = Math.floor((Date.now() - d.getTime()) / 1000);
        if (diff < 60) return 'Agora';
        if (diff < 3600) return `${Math.floor(diff / 60)} min`;
        if (diff < 86400) return `${Math.floor(diff / 3600)} h`;
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
        class="min-h-screen bg-[#F5F5F7] text-[#1D1D1F] font-sans selection:bg-[#0071E3] selection:text-white p-4 sm:p-10"
    >
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Top Bento Navigation -->
            <header
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white/80 backdrop-blur-xl border border-black/5 rounded-3xl p-6 shadow-sm"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="w-11 h-11 rounded-2xl bg-gradient-to-b from-[#0071E3] to-[#0058B0] flex items-center justify-center text-white shadow-md shadow-blue-500/20 font-bold"
                    >
                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                            <path
                                d="M6.18 15.64a2.18 2.18 0 0 1 2.18 2.18C8.36 19 7.38 20 6.18 20C5 20 4 19 4 17.82a2.18 2.18 0 0 1 2.18-2.18M4 4.44A15.56 15.56 0 0 1 19.56 20h-2.83A12.73 12.73 0 0 0 4 7.27V4.44m0 5.66a9.9 9.9 0 0 1 9.9 9.9h-2.83A7.07 7.07 0 0 0 4 12.93v-2.83Z"
                            />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-[#1D1D1F]">Larablogs</h1>
                        <p class="text-xs text-[#86868B]">
                            Monitoramento pessoal de feeds RSS &bull; macOS Style
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div
                        class="bg-[#F5F5F7] px-4 py-2 rounded-2xl text-xs font-semibold text-[#1D1D1F]"
                    >
                        <span class="text-[#86868B]">Ativos: </span>
                        <span>{{ stats.active_blogs }} / {{ stats.total_blogs }}</span>
                    </div>
                    <div
                        class="bg-[#F5F5F7] px-4 py-2 rounded-2xl text-xs font-semibold text-[#1D1D1F]"
                    >
                        <span class="text-[#86868B]">Artigos: </span>
                        <span>{{ stats.total_posts }}</span>
                    </div>
                </div>
            </header>

            <!-- Alerts -->
            <div
                v-if="flash.success"
                class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3"
            >
                <span>✓</span>
                <span>{{ flash.success }}</span>
            </div>
            <div
                v-if="flash.error"
                class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3"
            >
                <span>⚠</span>
                <span>{{ flash.error }}</span>
            </div>
            <div
                v-if="flash.info"
                class="p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-800 text-sm flex items-center gap-3"
            >
                <span>ℹ</span>
                <span>{{ flash.info }}</span>
            </div>

            <!-- Bento Grid Layout Principal -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Bento Tile 1: Form de Cadastro (7 colunas) -->
                <section
                    class="lg:col-span-7 bg-white rounded-3xl p-7 border border-black/5 shadow-sm flex flex-col justify-between"
                >
                    <div>
                        <span
                            class="text-[11px] font-bold uppercase tracking-wider text-[#0071E3] block mb-1"
                            >Novo Rastreamento</span
                        >
                        <h2 class="text-2xl font-bold tracking-tight text-[#1D1D1F] mb-1">
                            Adicione uma fonte
                        </h2>
                        <p class="text-xs text-[#86868B] mb-6">
                            Cole a URL do blog para receber avisos imediatos por e-mail a cada nova
                            postagem.
                        </p>

                        <form class="space-y-4" @submit.prevent="emit('submit')">
                            <div class="space-y-3">
                                <div>
                                    <input
                                        :value="form.url"
                                        type="text"
                                        placeholder="URL do blog ou feed RSS"
                                        class="w-full bg-[#F5F5F7] border border-transparent focus:border-[#0071E3] focus:bg-white rounded-2xl px-4 py-3 text-sm text-[#1D1D1F] outline-none transition"
                                        @input="
                                            emit(
                                                'updateForm',
                                                'url',
                                                ($event.target as HTMLInputElement).value,
                                            )
                                        "
                                    />
                                    <p
                                        v-if="form.errors.url"
                                        class="text-xs text-rose-600 mt-1 pl-2"
                                    >
                                        {{ form.errors.url }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <input
                                            :value="form.notify_email"
                                            type="email"
                                            placeholder="Seu e-mail"
                                            class="w-full bg-[#F5F5F7] border border-transparent focus:border-[#0071E3] focus:bg-white rounded-2xl px-4 py-3 text-sm text-[#1D1D1F] outline-none transition"
                                            @input="
                                                emit(
                                                    'updateForm',
                                                    'notify_email',
                                                    ($event.target as HTMLInputElement).value,
                                                )
                                            "
                                        />
                                        <p
                                            v-if="form.errors.notify_email"
                                            class="text-xs text-rose-600 mt-1 pl-2"
                                        >
                                            {{ form.errors.notify_email }}
                                        </p>
                                    </div>
                                    <div>
                                        <input
                                            :value="form.title"
                                            type="text"
                                            placeholder="Título (opcional)"
                                            class="w-full bg-[#F5F5F7] border border-transparent focus:border-[#0071E3] focus:bg-white rounded-2xl px-4 py-3 text-sm text-[#1D1D1F] outline-none transition"
                                            @input="
                                                emit(
                                                    'updateForm',
                                                    'title',
                                                    ($event.target as HTMLInputElement).value,
                                                )
                                            "
                                        />
                                    </div>
                                </div>
                            </div>

                            <div
                                class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                            >
                                <div class="flex items-center gap-1.5 text-xs text-[#86868B]">
                                    <span>Exemplos:</span>
                                    <button
                                        type="button"
                                        class="px-2.5 py-1 rounded-full bg-[#F5F5F7] hover:bg-[#E8E8ED] text-[#1D1D1F] transition"
                                        @click="
                                            emit(
                                                'prefill',
                                                'Laravel News',
                                                'https://laravel-news.com',
                                            )
                                        "
                                    >
                                        Laravel News
                                    </button>
                                    <button
                                        type="button"
                                        class="px-2.5 py-1 rounded-full bg-[#F5F5F7] hover:bg-[#E8E8ED] text-[#1D1D1F] transition"
                                        @click="
                                            emit('prefill', 'Overreacted', 'https://overreacted.io')
                                        "
                                    >
                                        Overreacted
                                    </button>
                                </div>

                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-3 rounded-full bg-[#0071E3] hover:bg-[#0077ED] text-white font-semibold text-xs tracking-wide transition shadow-sm disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Descobrindo...' : 'Monitorar Blog' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                <!-- Bento Tile 2: Stream dos Últimos Artigos Detectados (5 colunas) -->
                <section
                    class="lg:col-span-5 bg-white rounded-3xl p-6 border border-black/5 shadow-sm flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#86868B]"
                                >Últimos Artigos</span
                            >
                            <span class="text-xs font-semibold text-[#0071E3]"
                                >{{ recentPosts.length }} detectados</span
                            >
                        </div>

                        <div
                            v-if="recentPosts.length === 0"
                            class="text-center py-12 text-xs text-[#86868B]"
                        >
                            Nenhum artigo detectado ainda.
                        </div>

                        <div v-else class="space-y-3 max-h-[300px] overflow-y-auto pr-1">
                            <div
                                v-for="post in recentPosts.slice(0, 5)"
                                :key="post.id"
                                class="p-3.5 rounded-2xl bg-[#F5F5F7] hover:bg-[#EAEAEA] transition flex items-center justify-between gap-3 text-xs"
                            >
                                <div class="min-w-0">
                                    <span
                                        v-if="post.blog"
                                        class="text-[10px] font-bold text-[#86868B] block truncate"
                                    >
                                        {{ post.blog.title }} &bull;
                                        {{ formatDate(post.published_at) }}
                                    </span>
                                    <a
                                        :href="post.url"
                                        target="_blank"
                                        class="font-semibold text-[#1D1D1F] hover:text-[#0071E3] transition line-clamp-1 mt-0.5"
                                    >
                                        {{ post.title }}
                                    </a>
                                </div>
                                <a
                                    :href="post.url"
                                    target="_blank"
                                    class="p-2 rounded-xl bg-white text-[#0071E3] shadow-xs shrink-0"
                                >
                                    ↗
                                </a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Bento Row 2: Blogs Cadastrados -->
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-[#1D1D1F]">
                        Seus Blogs Monitorados ({{ blogs.length }})
                    </h3>
                </div>

                <div
                    v-if="blogs.length === 0"
                    class="bg-white rounded-3xl p-12 text-center text-sm text-[#86868B] border border-black/5"
                >
                    Nenhum blog cadastrado no momento. Cadastre um link acima para iniciar.
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        v-for="blog in blogs"
                        :key="blog.id"
                        class="bg-white rounded-3xl p-6 border border-black/5 shadow-sm hover:shadow-md transition flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <a
                                        :href="blog.url"
                                        target="_blank"
                                        class="font-bold text-base text-[#1D1D1F] hover:text-[#0071E3] transition block line-clamp-1"
                                    >
                                        {{ blog.title }}
                                    </a>
                                    <span class="text-xs text-[#86868B] block truncate mt-0.5"
                                        >✉ {{ blog.notify_email }}</span
                                    >
                                </div>
                                <span
                                    class="text-[11px] font-semibold px-2.5 py-1 rounded-full shrink-0"
                                    :class="
                                        blog.is_active
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-slate-100 text-slate-500'
                                    "
                                >
                                    {{ blog.is_active ? 'Ativo' : 'Pausado' }}
                                </span>
                            </div>

                            <div
                                v-if="blog.latest_post"
                                class="mt-4 p-3 rounded-2xl bg-[#F5F5F7] text-xs"
                            >
                                <span
                                    class="text-[10px] text-[#86868B] uppercase font-bold tracking-wider block"
                                    >Mais recente:</span
                                >
                                <a
                                    :href="blog.latest_post.url"
                                    target="_blank"
                                    class="font-medium text-[#1D1D1F] hover:text-[#0071E3] line-clamp-1 mt-0.5"
                                >
                                    {{ blog.latest_post.title }}
                                </a>
                            </div>
                        </div>

                        <div
                            class="mt-5 pt-4 border-t border-black/5 flex items-center justify-between text-xs"
                        >
                            <span class="text-[11px] text-[#86868B]">
                                Checado {{ formatRelative(blog.last_checked_at) }}
                            </span>

                            <div class="flex items-center gap-2">
                                <button
                                    :disabled="checkingBlogId === blog.id"
                                    class="px-3 py-1.5 rounded-full bg-[#F5F5F7] hover:bg-[#E8E8ED] font-semibold text-xs text-[#1D1D1F] transition"
                                    @click="emit('checkNow', blog.id)"
                                >
                                    {{ checkingBlogId === blog.id ? '...' : 'Verificar' }}
                                </button>
                                <button
                                    class="text-xs text-[#86868B] hover:text-[#1D1D1F] transition"
                                    @click="emit('toggle', blog.id)"
                                >
                                    {{ blog.is_active ? 'Pausar' : 'Ativar' }}
                                </button>
                                <button
                                    class="text-xs text-rose-500 hover:text-rose-700 transition"
                                    @click="emit('delete', blog)"
                                >
                                    Excluir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
