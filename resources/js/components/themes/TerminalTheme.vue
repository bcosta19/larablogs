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
    if (!dateStr) return 'never';
    try {
        const d = new Date(dateStr);
        const diff = Math.floor((Date.now() - d.getTime()) / 1000);
        if (diff < 60) return `${diff}s ago`;
        if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
        return d.toISOString().slice(0, 10);
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <div
        class="min-h-screen bg-[#070B12] text-[#94A3B8] font-mono selection:bg-[#10B981] selection:text-[#070B12] p-4 sm:p-8"
    >
        <div class="max-w-5xl mx-auto space-y-6">
            <!-- Terminal Header -->
            <header class="border border-[#1E293B] bg-[#0B132B]/80 rounded-lg p-4 shadow-xl">
                <div
                    class="flex items-center justify-between border-b border-[#1E293B] pb-3 mb-3 text-xs"
                >
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-[#EF4444]/80 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-[#F59E0B]/80 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-[#10B981]/80 inline-block"></span>
                        <span class="ml-2 text-[#64748B]">larablogs-daemon // v1.0.0</span>
                    </div>
                    <div class="flex items-center gap-4 text-[11px]">
                        <span class="text-[#10B981] flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#10B981] animate-ping"></span>
                            DAEMON_ACTIVE
                        </span>
                        <span class="text-[#64748B]">HOST: 127.0.0.1</span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="text-emerald-400 font-bold text-lg flex items-center gap-2">
                            <span>$</span>
                            <span class="text-white">larablogs</span>
                            <span class="text-xs text-[#64748B]">--watch-feeds --emit-smtp</span>
                        </div>
                        <p class="text-xs text-[#64748B] mt-1">
                            Autonomous RSS/Atom crawler and mail dispatcher.
                        </p>
                    </div>

                    <div class="flex items-center gap-3 text-xs">
                        <div class="border border-[#1E293B] px-3 py-1.5 rounded bg-[#070B12]">
                            <span class="text-[#64748B]">NODES: </span>
                            <span class="text-emerald-400 font-bold"
                                >{{ stats.active_blogs }}/{{ stats.total_blogs }}</span
                            >
                        </div>
                        <div class="border border-[#1E293B] px-3 py-1.5 rounded bg-[#070B12]">
                            <span class="text-[#64748B]">INDEXED: </span>
                            <span class="text-cyan-400 font-bold">{{ stats.total_posts }}</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Alerts -->
            <div
                v-if="flash.success"
                class="border border-emerald-500/40 bg-emerald-950/20 text-emerald-400 text-xs p-3 rounded"
            >
                [SUCCESS] >> {{ flash.success }}
            </div>
            <div
                v-if="flash.error"
                class="border border-rose-500/40 bg-rose-950/20 text-rose-400 text-xs p-3 rounded"
            >
                [ERROR] >> {{ flash.error }}
            </div>
            <div
                v-if="flash.info"
                class="border border-cyan-500/40 bg-cyan-950/20 text-cyan-400 text-xs p-3 rounded"
            >
                [INFO] >> {{ flash.info }}
            </div>

            <!-- Terminal Form -->
            <section class="border border-[#1E293B] bg-[#0A0F1D] rounded-lg p-5">
                <div class="text-xs text-[#38BDF8] font-bold mb-4 flex items-center gap-2">
                    <span>❯</span>
                    <span>REGISTER_FEED_TARGET</span>
                </div>

                <form class="space-y-4 text-xs" @submit.prevent="emit('submit')">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <div class="md:col-span-5">
                            <label class="block text-[#64748B] mb-1">$ target_url:</label>
                            <input
                                :value="form.url"
                                type="text"
                                placeholder="https://domain.tld/feed"
                                class="w-full bg-[#070B12] border border-[#1E293B] focus:border-cyan-400 px-3 py-2 text-slate-200 outline-none rounded"
                                @input="
                                    emit(
                                        'updateForm',
                                        'url',
                                        ($event.target as HTMLInputElement).value,
                                    )
                                "
                            />
                            <span
                                v-if="form.errors.url"
                                class="text-rose-400 text-[11px] mt-1 block"
                                >! {{ form.errors.url }}</span
                            >
                        </div>

                        <div class="md:col-span-4">
                            <label class="block text-[#64748B] mb-1">$ notify_email:</label>
                            <input
                                :value="form.notify_email"
                                type="email"
                                placeholder="sysadmin@local"
                                class="w-full bg-[#070B12] border border-[#1E293B] focus:border-cyan-400 px-3 py-2 text-slate-200 outline-none rounded"
                                @input="
                                    emit(
                                        'updateForm',
                                        'notify_email',
                                        ($event.target as HTMLInputElement).value,
                                    )
                                "
                            />
                            <span
                                v-if="form.errors.notify_email"
                                class="text-rose-400 text-[11px] mt-1 block"
                                >! {{ form.errors.notify_email }}</span
                            >
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-[#64748B] mb-1">$ alias (opt):</label>
                            <input
                                :value="form.title"
                                type="text"
                                placeholder="alias"
                                class="w-full bg-[#070B12] border border-[#1E293B] focus:border-cyan-400 px-3 py-2 text-slate-200 outline-none rounded"
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

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                        <div class="flex items-center gap-2 text-[11px] text-[#64748B]">
                            <span>presets:</span>
                            <button
                                type="button"
                                class="text-cyan-400 hover:underline"
                                @click="emit('prefill', 'Laravel News', 'https://laravel-news.com')"
                            >
                                [ln]
                            </button>
                            <button
                                type="button"
                                class="text-cyan-400 hover:underline"
                                @click="emit('prefill', 'Overreacted', 'https://overreacted.io')"
                            >
                                [ov]
                            </button>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded transition disabled:opacity-50"
                        >
                            {{ form.processing ? 'DISCOVERING...' : 'DISCOVER_AND_ATTACH' }}
                        </button>
                    </div>
                </form>
            </section>

            <!-- Navigation Tabs -->
            <div class="flex items-center gap-2 border-b border-[#1E293B] text-xs">
                <button
                    class="px-4 py-2 border-b-2 font-bold transition"
                    :class="
                        activeTab === 'blogs'
                            ? 'border-cyan-400 text-cyan-400 bg-cyan-950/20'
                            : 'border-transparent text-[#64748B] hover:text-slate-300'
                    "
                    @click="activeTab = 'blogs'"
                >
                    TARGET_TABLE ({{ blogs.length }})
                </button>
                <button
                    class="px-4 py-2 border-b-2 font-bold transition"
                    :class="
                        activeTab === 'posts'
                            ? 'border-cyan-400 text-cyan-400 bg-cyan-950/20'
                            : 'border-transparent text-[#64748B] hover:text-slate-300'
                    "
                    @click="activeTab = 'posts'"
                >
                    INGESTION_LOG ({{ recentPosts.length }})
                </button>
            </div>

            <!-- Target Table -->
            <div v-if="activeTab === 'blogs'" class="space-y-3">
                <div
                    v-if="blogs.length === 0"
                    class="border border-dashed border-[#1E293B] p-8 text-center text-xs text-[#64748B] rounded"
                >
                    NO TARGETS CONFIGURED. INSERT URL ABOVE.
                </div>

                <div
                    v-for="blog in blogs"
                    :key="blog.id"
                    class="border border-[#1E293B] bg-[#0A0F1D] p-4 rounded-lg flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs"
                >
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span
                                class="w-2 h-2 rounded-full"
                                :class="blog.is_active ? 'bg-emerald-400' : 'bg-slate-600'"
                            ></span>
                            <a
                                :href="`/blogs/${blog.id}`"
                                class="text-white font-bold hover:text-cyan-400 underline decoration-[#1E293B]"
                            >
                                {{ blog.title }}
                            </a>
                            <span
                                class="text-[10px] px-1.5 py-0.5 rounded bg-[#1E293B] text-[#94A3B8]"
                            >
                                {{ blog.posts_count }} posts
                            </span>
                        </div>
                        <div class="text-[11px] text-[#64748B]">
                            feed: {{ blog.feed_url }} &bull; notify:
                            <span class="text-slate-300">{{ blog.notify_email }}</span> &bull;
                            last_sync: {{ formatRelative(blog.last_checked_at) }}
                        </div>
                        <div
                            v-if="blog.latest_post"
                            class="text-[11px] text-slate-400 truncate max-w-md"
                        >
                            &gt; latest:
                            <a
                                :href="`/posts/${blog.latest_post.id}/read`"
                                class="text-cyan-300 hover:underline"
                                >{{ blog.latest_post.title }}</a
                            >
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            :disabled="checkingBlogId === blog.id"
                            class="px-2.5 py-1 border border-cyan-500/40 text-cyan-400 hover:bg-cyan-950/40 rounded transition"
                            @click="emit('checkNow', blog.id)"
                        >
                            {{ checkingBlogId === blog.id ? '[SYNCING]' : '[SYNC]' }}
                        </button>
                        <button
                            class="px-2.5 py-1 border border-[#1E293B] text-slate-400 hover:text-white rounded transition"
                            @click="emit('toggle', blog.id)"
                        >
                            {{ blog.is_active ? '[PAUSE]' : '[RESUME]' }}
                        </button>
                        <button
                            class="px-2.5 py-1 text-rose-400 hover:bg-rose-950/30 rounded transition"
                            @click="emit('delete', blog)"
                        >
                            [RM]
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ingestion Log -->
            <div v-if="activeTab === 'posts'" class="space-y-2 text-xs">
                <div
                    v-for="post in recentPosts"
                    :key="post.id"
                    class="border border-[#1E293B] bg-[#0A0F1D] p-3 rounded flex items-center justify-between gap-3"
                >
                    <div class="truncate">
                        <span class="text-emerald-400 font-bold mr-2">&gt;</span>
                        <span v-if="post.blog" class="text-cyan-400 mr-2"
                            >[{{ post.blog.title }}]</span
                        >
                        <a :href="`/posts/${post.id}/read`" class="text-white hover:underline">{{
                            post.title
                        }}</a>
                    </div>
                    <a
                        :href="`/posts/${post.id}/read`"
                        class="text-slate-500 hover:text-white shrink-0"
                        >[OPEN]</a
                    >
                </div>
            </div>
        </div>
    </div>
</template>
