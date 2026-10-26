<script setup lang="ts">
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import type { BlogItem, BlogPostItem, BlogStats, FlashMessages } from '@/types';

import EditorialTheme from '@/components/themes/EditorialTheme.vue';
import TerminalTheme from '@/components/themes/TerminalTheme.vue';
import LinearTheme from '@/components/themes/LinearTheme.vue';
import NeoBrutalismTheme from '@/components/themes/NeoBrutalismTheme.vue';
import BentoTheme from '@/components/themes/BentoTheme.vue';

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

// Design Theme Switcher
type ThemeKey = 'linear' | 'editorial' | 'terminal' | 'neobrutalism' | 'bento';
const currentTheme = ref<ThemeKey>('linear');

const themesList: Array<{ id: ThemeKey; number: string; name: string; style: string }> = [
    { id: 'linear', number: '1', name: 'Linear / Raycast', style: 'Dark Glassmorphism & Gradients' },
    { id: 'editorial', number: '2', name: 'Editorial', style: 'Jornal Minimalista em Papel' },
    { id: 'terminal', number: '3', name: 'Hacker TUI', style: 'Terminal Cyberpunk / Monospace' },
    { id: 'neobrutalism', number: '4', name: 'Neo-Brutalism', style: 'Pop Tech & Hard Shadows' },
    { id: 'bento', number: '5', name: 'Apple Bento', style: 'macOS Modern Bento Tiles' },
];

onMounted(() => {
    const saved = localStorage.getItem('larablogs_theme') as ThemeKey | null;
    if (saved && ['linear', 'editorial', 'terminal', 'neobrutalism', 'bento'].includes(saved)) {
        currentTheme.value = saved;
    }
});

const selectTheme = (theme: ThemeKey) => {
    currentTheme.value = theme;
    localStorage.setItem('larablogs_theme', theme);
};

const activeComponent = computed(() => {
    switch (currentTheme.value) {
        case 'editorial':
            return EditorialTheme;
        case 'terminal':
            return TerminalTheme;
        case 'neobrutalism':
            return NeoBrutalismTheme;
        case 'bento':
            return BentoTheme;
        case 'linear':
        default:
            return LinearTheme;
    }
});

// Ações de formulário e controle
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
    if (confirm(`Deseja remover "${blog.title}" do monitoramento?`)) {
        deletingBlogId.value = blog.id;
        router.delete(`/blogs/${blog.id}`, {
            preserveScroll: true,
            onFinish: () => {
                deletingBlogId.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Larablogs - Escolha o seu Design" />

    <!-- Barra Flutuante Fixa de Seleção de Design -->
    <aside class="sticky top-0 z-50 bg-black/90 backdrop-blur-xl border-b border-white/10 px-4 py-2.5 text-xs text-white">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                <span class="font-bold text-slate-300 uppercase tracking-wider text-[11px]">Seletor de Design:</span>
                <span class="text-slate-400 hidden sm:inline text-[11px]">(Alterne entre os 5 visuais com 1 clique)</span>
            </div>

            <!-- Botões dos 5 Designs -->
            <div class="flex items-center gap-1.5 flex-wrap justify-center">
                <button
                    v-for="t in themesList"
                    :key="t.id"
                    @click="selectTheme(t.id)"
                    class="px-2.5 py-1 rounded-lg font-medium transition text-[11px] flex items-center gap-1.5"
                    :class="currentTheme === t.id
                        ? 'bg-white text-black font-bold shadow-md ring-2 ring-white/20'
                        : 'bg-white/10 text-slate-300 hover:bg-white/20 hover:text-white'"
                    :title="t.style"
                >
                    <span class="opacity-60">{{ t.number }}.</span>
                    <span>{{ t.name }}</span>
                </button>
            </div>
        </div>
    </aside>

    <!-- Componente do Design Ativo -->
    <component
        :is="activeComponent"
        :blogs="props.blogs"
        :recent-posts="props.recentPosts"
        :stats="props.stats"
        :flash="flash"
        :form="form"
        :checking-blog-id="checkingBlogId"
        :toggling-blog-id="togglingBlogId"
        :deleting-blog-id="deletingBlogId"
        @submit="submitBlog"
        @prefill="prefill"
        @check-now="checkNow"
        @toggle="toggleBlog"
        @delete="deleteBlog"
    />
</template>
