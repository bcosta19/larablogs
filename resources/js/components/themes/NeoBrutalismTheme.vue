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
        return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
    } catch {
        return dateStr;
    }
};

const formatDate = (dateStr: string | null): string => {
    if (!dateStr) return '';
    try {
        return new Date(dateStr).toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
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
        class="min-h-screen bg-[#FFFDF0] text-[#111827] font-sans selection:bg-[#FACC15] selection:text-black p-4 sm:p-8"
    >
        <div class="max-w-4xl mx-auto space-y-8">
            <!-- Header Neo-Brutalista -->
            <header
                class="border-[3px] border-black bg-[#FDE047] p-6 shadow-[5px_5px_0px_0px_#000] flex flex-col sm:flex-row sm:items-center justify-between gap-4"
            >
                <div>
                    <div
                        class="inline-block bg-black text-white px-2 py-0.5 text-xs font-black uppercase tracking-wider mb-1"
                    >
                        RSS Radar v1
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-black">
                        LARABLOGS!
                    </h1>
                    <p class="text-xs font-bold text-black/80 mt-1">
                        Cadastre o blog &bull; Receba o post assim que sair do forno.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div
                        class="border-2 border-black bg-white px-3 py-1.5 shadow-[3px_3px_0px_0px_#000] text-xs font-black"
                    >
                        {{ stats.active_blogs }} ATIVOS
                    </div>
                    <div
                        class="border-2 border-black bg-[#A7F3D0] px-3 py-1.5 shadow-[3px_3px_0px_0px_#000] text-xs font-black"
                    >
                        {{ stats.total_posts }} POSTS
                    </div>
                </div>
            </header>

            <!-- Alerts Neo-Brutalistas -->
            <div
                v-if="flash.success"
                class="border-[3px] border-black bg-[#BBF7D0] p-4 shadow-[4px_4px_0px_0px_#000] text-xs font-black"
            >
                🎉 {{ flash.success }}
            </div>
            <div
                v-if="flash.error"
                class="border-[3px] border-black bg-[#FECDD3] p-4 shadow-[4px_4px_0px_0px_#000] text-xs font-black"
            >
                💥 {{ flash.error }}
            </div>
            <div
                v-if="flash.info"
                class="border-[3px] border-black bg-[#BAE6FD] p-4 shadow-[4px_4px_0px_0px_#000] text-xs font-black"
            >
                ℹ {{ flash.info }}
            </div>

            <!-- Form Card Neo-Brutalista -->
            <section class="border-[3px] border-black bg-white p-6 shadow-[6px_6px_0px_0px_#000]">
                <h2 class="text-lg font-black uppercase tracking-wide mb-1">Adicionar Novo Alvo</h2>
                <p class="text-xs font-medium text-slate-600 mb-5">
                    Cole a URL e nós encontramos o feed RSS num piscar de olhos.
                </p>

                <form class="space-y-4" @submit.prevent="emit('submit')">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <div class="md:col-span-5">
                            <label class="block text-xs font-black uppercase mb-1"
                                >URL do Blog *</label
                            >
                            <input
                                :value="form.url"
                                type="text"
                                placeholder="https://..."
                                class="w-full border-2 border-black bg-[#F8FAFC] px-3 py-2 text-xs font-bold focus:bg-white focus:outline-none shadow-[2px_2px_0px_0px_#000]"
                                @input="
                                    emit(
                                        'updateForm',
                                        'url',
                                        ($event.target as HTMLInputElement).value,
                                    )
                                "
                            />
                            <p v-if="form.errors.url" class="text-xs text-rose-600 font-bold mt-1">
                                {{ form.errors.url }}
                            </p>
                        </div>

                        <div class="md:col-span-4">
                            <label class="block text-xs font-black uppercase mb-1"
                                >Seu E-mail *</label
                            >
                            <input
                                :value="form.notify_email"
                                type="email"
                                placeholder="eu@dominio.com"
                                class="w-full border-2 border-black bg-[#F8FAFC] px-3 py-2 text-xs font-bold focus:bg-white focus:outline-none shadow-[2px_2px_0px_0px_#000]"
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
                                class="text-xs text-rose-600 font-bold mt-1"
                            >
                                {{ form.errors.notify_email }}
                            </p>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-xs font-black uppercase mb-1"
                                >Nome (opcional)</label
                            >
                            <input
                                :value="form.title"
                                type="text"
                                placeholder="Apelido"
                                class="w-full border-2 border-black bg-[#F8FAFC] px-3 py-2 text-xs font-bold focus:bg-white focus:outline-none shadow-[2px_2px_0px_0px_#000]"
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

                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2"
                    >
                        <div class="flex items-center gap-2 text-xs font-bold">
                            <span>Exemplos:</span>
                            <button
                                type="button"
                                class="underline hover:bg-[#FEF08A] px-1"
                                @click="emit('prefill', 'Laravel News', 'https://laravel-news.com')"
                            >
                                Laravel News
                            </button>
                            <button
                                type="button"
                                class="underline hover:bg-[#FEF08A] px-1"
                                @click="emit('prefill', 'Overreacted', 'https://overreacted.io')"
                            >
                                Overreacted
                            </button>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 border-[2.5px] border-black bg-[#F43F5E] hover:bg-[#E11D48] text-white font-black text-xs uppercase tracking-wider shadow-[4px_4px_0px_0px_#000] active:translate-x-[2px] active:translate-y-[2px] active:shadow-[2px_2px_0px_0px_#000] transition disabled:opacity-50"
                        >
                            {{ form.processing ? 'Descobrindo...' : 'Cadastrar Blog Agora' }}
                        </button>
                    </div>
                </form>
            </section>

            <!-- Tabs Neo-Brutalistas -->
            <div class="flex gap-2 text-xs font-black">
                <button
                    class="px-4 py-2 border-[2.5px] border-black shadow-[3px_3px_0px_0px_#000] transition"
                    :class="
                        activeTab === 'blogs'
                            ? 'bg-[#38BDF8] text-black translate-x-[1px] translate-y-[1px]'
                            : 'bg-white hover:bg-[#F1F5F9]'
                    "
                    @click="activeTab = 'blogs'"
                >
                    BLOGS MONITORADOS ({{ blogs.length }})
                </button>
                <button
                    class="px-4 py-2 border-[2.5px] border-black shadow-[3px_3px_0px_0px_#000] transition"
                    :class="
                        activeTab === 'posts'
                            ? 'bg-[#38BDF8] text-black translate-x-[1px] translate-y-[1px]'
                            : 'bg-white hover:bg-[#F1F5F9]'
                    "
                    @click="activeTab = 'posts'"
                >
                    POSTS DETECTADOS ({{ recentPosts.length }})
                </button>
            </div>

            <!-- Lista de Blogs Neo-Brutalista -->
            <div v-if="activeTab === 'blogs'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                    v-if="blogs.length === 0"
                    class="col-span-2 border-[3px] border-black border-dashed bg-white p-10 text-center text-xs font-black"
                >
                    NENHUM BLOG CADASTRADO AINDA! COMECE ACIMA.
                </div>

                <div
                    v-for="blog in blogs"
                    :key="blog.id"
                    class="border-[3px] border-black bg-white p-5 shadow-[4px_4px_0px_0px_#000] flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <a
                                    :href="blog.url"
                                    target="_blank"
                                    class="text-base font-black hover:underline block"
                                >
                                    {{ blog.title }}
                                </a>
                                <span
                                    class="text-[11px] font-bold text-slate-500 truncate block mt-0.5"
                                >
                                    ✉ {{ blog.notify_email }}
                                </span>
                            </div>
                            <span
                                class="text-[10px] font-black border-2 border-black px-2 py-0.5 shadow-[2px_2px_0px_0px_#000]"
                                :class="blog.is_active ? 'bg-[#86EFAC]' : 'bg-[#E2E8F0]'"
                            >
                                {{ blog.is_active ? 'ATIVO' : 'PAUSADO' }}
                            </span>
                        </div>

                        <div
                            v-if="blog.latest_post"
                            class="mt-3 p-2.5 border-2 border-black bg-[#FEF9C3] text-xs"
                        >
                            <span class="text-[10px] font-black uppercase block">Último post:</span>
                            <a
                                :href="blog.latest_post.url"
                                target="_blank"
                                class="font-bold underline line-clamp-1"
                            >
                                {{ blog.latest_post.title }}
                            </a>
                        </div>
                    </div>

                    <div
                        class="mt-4 pt-3 border-t-2 border-black flex items-center justify-between text-xs font-black"
                    >
                        <span class="text-slate-500 text-[10px]"
                            >Checado: {{ formatRelative(blog.last_checked_at) }}</span
                        >

                        <div class="flex items-center gap-2">
                            <button
                                :disabled="checkingBlogId === blog.id"
                                class="px-2.5 py-1 border-2 border-black bg-[#E0E7FF] hover:bg-[#C7D2FE] shadow-[2px_2px_0px_0px_#000] text-[11px]"
                                @click="emit('checkNow', blog.id)"
                            >
                                {{ checkingBlogId === blog.id ? '...' : 'Checar' }}
                            </button>
                            <button
                                class="px-2 py-1 text-slate-700 hover:underline text-[11px]"
                                @click="emit('toggle', blog.id)"
                            >
                                {{ blog.is_active ? 'Pausar' : 'Ativar' }}
                            </button>
                            <button
                                class="px-2 py-1 text-rose-600 hover:underline text-[11px]"
                                @click="emit('delete', blog)"
                            >
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Posts Neo-Brutalista -->
            <div v-if="activeTab === 'posts'" class="space-y-3">
                <div
                    v-for="post in recentPosts"
                    :key="post.id"
                    class="border-[2.5px] border-black bg-white p-4 shadow-[3px_3px_0px_0px_#000] flex items-center justify-between gap-4 text-xs"
                >
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span
                                v-if="post.blog"
                                class="font-black bg-[#FDE047] border border-black px-1.5 py-0.2"
                            >
                                {{ post.blog.title }}
                            </span>
                            <span class="text-slate-500 font-bold">{{
                                formatDate(post.published_at)
                            }}</span>
                        </div>
                        <a
                            :href="post.url"
                            target="_blank"
                            class="font-black text-sm hover:underline block"
                        >
                            {{ post.title }}
                        </a>
                    </div>
                    <a
                        :href="post.url"
                        target="_blank"
                        class="border-2 border-black bg-[#C4B5FD] px-3 py-1.5 font-black shadow-[2px_2px_0px_0px_#000] hover:bg-[#A78BFA] shrink-0"
                    >
                        LER
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
