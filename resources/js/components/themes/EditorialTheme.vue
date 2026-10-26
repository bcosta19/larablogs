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
    (e: 'prefill', title: string, url: string): void;
    (e: 'checkNow', id: number): void;
    (e: 'toggle', id: number): void;
    (e: 'delete', blog: BlogItem): void;
}>();

const activeTab = ref<'blogs' | 'posts'>('blogs');

const formatDate = (dateStr: string | null): string => {
    if (!dateStr) return 'Nunca';
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
    if (!dateStr) return 'Nunca checado';
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
    <div class="min-h-screen bg-[#FBF9F5] text-[#2C2825] font-serif selection:bg-[#E2DDD5] selection:text-[#1A1816]">
        <!-- Cabeçalho Editorial com borda dupla clássica -->
        <header class="border-b border-[#E6E1D8] bg-[#FBF9F5]">
            <div class="max-w-4xl mx-auto px-6 py-8 text-center">
                <div class="inline-block border-b-2 border-t-2 border-[#2C2825] py-1 px-4 mb-3">
                    <span class="font-sans text-[11px] tracking-[0.25em] uppercase font-bold text-[#6B635B]">Boletim Periódico Digital</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-normal tracking-tight text-[#1C1A18]">Larablogs</h1>
                <p class="mt-2 text-base text-[#756D63] font-sans italic max-w-lg mx-auto">
                    Curadoria contínua de publicações da web com avisos diretos na sua caixa postal.
                </p>

                <!-- Estatísticas no estilo folhetim -->
                <div class="mt-6 flex items-center justify-center gap-6 text-xs font-sans text-[#756D63] border-t border-[#EAE5DC] pt-4">
                    <span><strong>{{ stats.total_blogs }}</strong> publicações inscritas</span>
                    <span>&bull;</span>
                    <span><strong>{{ stats.active_blogs }}</strong> ativas</span>
                    <span>&bull;</span>
                    <span><strong>{{ stats.total_posts }}</strong> artigos arquivados</span>
                </div>
            </div>
        </header>

        <!-- Mensagens de Alerta Estilo Editorial -->
        <div class="max-w-4xl mx-auto px-6 pt-6 font-sans">
            <div v-if="flash.success" class="p-4 bg-[#F2F7F2] border-l-4 border-[#2E6F40] text-[#1E4A2B] text-sm mb-4">
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="p-4 bg-[#FDF2F2] border-l-4 border-[#A32D2D] text-[#6E1C1C] text-sm mb-4">
                {{ flash.error }}
            </div>
            <div v-if="flash.info" class="p-4 bg-[#F3F6FA] border-l-4 border-[#2D5A8A] text-[#1C3A5A] text-sm mb-4">
                {{ flash.info }}
            </div>
        </div>

        <main class="max-w-4xl mx-auto px-6 py-6 space-y-10">
            <!-- Formulário no Estilo Ficha Catalográfica -->
            <section class="bg-white border border-[#E4DFD5] p-8 shadow-sm">
                <h2 class="text-xl font-normal text-[#1A1816] mb-1">Inscrever nova publicação</h2>
                <p class="text-xs font-sans text-[#756D63] mb-6">
                    Insira o endereço do blog ou feed RSS para monitoramento e receba novas matérias.
                </p>

                <form @submit.prevent="emit('submit')" class="space-y-5 font-sans">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#544E46] mb-1">
                                Endereço Web (URL) *
                            </label>
                            <input
                                v-model="form.url"
                                type="text"
                                placeholder="https://exemplo.com"
                                class="w-full bg-[#FAF8F5] border border-[#D5CEC2] px-3 py-2 text-sm text-[#2C2825] focus:outline-none focus:border-[#2C2825] focus:bg-white transition"
                            />
                            <p v-if="form.errors.url" class="text-xs text-[#A32D2D] mt-1">{{ form.errors.url }}</p>
                        </div>

                        <div class="md:col-span-4">
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#544E46] mb-1">
                                E-mail Destinatário *
                            </label>
                            <input
                                v-model="form.notify_email"
                                type="email"
                                placeholder="seu@email.com"
                                class="w-full bg-[#FAF8F5] border border-[#D5CEC2] px-3 py-2 text-sm text-[#2C2825] focus:outline-none focus:border-[#2C2825] focus:bg-white transition"
                            />
                            <p v-if="form.errors.notify_email" class="text-xs text-[#A32D2D] mt-1">{{ form.errors.notify_email }}</p>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#756D63] mb-1">
                                Título (Opcional)
                            </label>
                            <input
                                v-model="form.title"
                                type="text"
                                placeholder="Automático"
                                class="w-full bg-[#FAF8F5] border border-[#D5CEC2] px-3 py-2 text-sm text-[#2C2825] focus:outline-none focus:border-[#2C2825] focus:bg-white transition"
                            />
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2 border-t border-[#EFEBE4]">
                        <div class="flex items-center gap-2 text-xs text-[#756D63]">
                            <span>Sugestões:</span>
                            <button type="button" @click="emit('prefill', 'Laravel News', 'https://laravel-news.com')" class="underline hover:text-[#1A1816]">Laravel News</button>
                            <span>&bull;</span>
                            <button type="button" @click="emit('prefill', 'Overreacted', 'https://overreacted.io')" class="underline hover:text-[#1A1816]">Overreacted</button>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-[#2C2825] hover:bg-[#1A1816] text-[#FBF9F5] text-xs font-bold uppercase tracking-widest transition disabled:opacity-50"
                        >
                            {{ form.processing ? 'Consultando...' : 'Adicionar ao Registro' }}
                        </button>
                    </div>
                </form>
            </section>

            <!-- Abas -->
            <div class="flex items-center gap-6 border-b border-[#E0DACE] font-sans text-sm">
                <button
                    @click="activeTab = 'blogs'"
                    class="pb-2 font-bold transition border-b-2 -mb-px"
                    :class="activeTab === 'blogs' ? 'border-[#2C2825] text-[#1A1816]' : 'border-transparent text-[#756D63] hover:text-[#2C2825]'"
                >
                    Publicações Cadastradas ({{ blogs.length }})
                </button>
                <button
                    @click="activeTab = 'posts'"
                    class="pb-2 font-bold transition border-b-2 -mb-px"
                    :class="activeTab === 'posts' ? 'border-[#2C2825] text-[#1A1816]' : 'border-transparent text-[#756D63] hover:text-[#2C2825]'"
                >
                    Artigos Recentes ({{ recentPosts.length }})
                </button>
            </div>

            <!-- Lista de Blogs -->
            <div v-if="activeTab === 'blogs'" class="space-y-4">
                <div v-if="blogs.length === 0" class="text-center py-12 text-[#756D63] font-sans text-sm">
                    Nenhuma publicação cadastrada no momento.
                </div>

                <div
                    v-for="blog in blogs"
                    :key="blog.id"
                    class="bg-white border border-[#E4DFD5] p-6 transition"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="flex items-baseline gap-3">
                                <a :href="blog.url" target="_blank" class="text-xl font-normal text-[#1A1816] hover:underline">
                                    {{ blog.title }}
                                </a>
                                <span class="font-sans text-[10px] uppercase tracking-wider px-2 py-0.5 border"
                                    :class="blog.is_active ? 'border-[#2E6F40] text-[#2E6F40]' : 'border-[#948B80] text-[#948B80]'">
                                    {{ blog.is_active ? 'Ativo' : 'Pausado' }}
                                </span>
                            </div>
                            <p class="font-sans text-xs text-[#756D63] mt-1">
                                Destino: <strong class="text-[#2C2825]">{{ blog.notify_email }}</strong> &bull; Checado: {{ formatRelative(blog.last_checked_at) }}
                            </p>
                        </div>

                        <!-- Ações -->
                        <div class="flex items-center gap-2 font-sans text-xs">
                            <button
                                @click="emit('checkNow', blog.id)"
                                :disabled="checkingBlogId === blog.id"
                                class="px-3 py-1 border border-[#D5CEC2] hover:border-[#2C2825] transition"
                            >
                                {{ checkingBlogId === blog.id ? 'Verificando...' : 'Verificar' }}
                            </button>
                            <button
                                @click="emit('toggle', blog.id)"
                                class="px-3 py-1 text-[#756D63] hover:text-[#1A1816] transition"
                            >
                                {{ blog.is_active ? 'Pausar' : 'Reativar' }}
                            </button>
                            <button
                                @click="emit('delete', blog)"
                                class="px-2 py-1 text-[#A32D2D] hover:underline transition"
                            >
                                Remover
                            </button>
                        </div>
                    </div>

                    <!-- Último Post -->
                    <div v-if="blog.latest_post" class="mt-4 pt-3 border-t border-[#F0EBE3] font-sans text-xs text-[#756D63]">
                        <span class="uppercase tracking-wider text-[10px] text-[#948B80]">Último artigo registrado:</span>
                        <a :href="blog.latest_post.url" target="_blank" class="block font-serif text-sm text-[#1A1816] hover:underline mt-0.5">
                            {{ blog.latest_post.title }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Últimos Posts -->
            <div v-if="activeTab === 'posts'" class="space-y-4 font-sans">
                <div v-for="post in recentPosts" :key="post.id" class="bg-white border border-[#E4DFD5] p-5">
                    <span v-if="post.blog" class="text-xs uppercase tracking-wider text-[#756D63] font-bold">
                        {{ post.blog.title }} &bull; {{ formatDate(post.published_at) }}
                    </span>
                    <a :href="post.url" target="_blank" class="block font-serif text-lg text-[#1A1816] hover:underline mt-1">
                        {{ post.title }}
                    </a>
                </div>
            </div>
        </main>
    </div>
</template>
