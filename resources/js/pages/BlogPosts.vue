<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ReadingLayout from '@/components/ReadingLayout.vue';
import { useI18n, type LocaleCode } from '@/i18n';
import type { BlogPostItem } from '@/types';

interface Props {
    locale: LocaleCode;
    blog: { id: number; title: string; url: string };
    posts: {
        data: BlogPostItem[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
        total: number;
    };
}
const props = defineProps<Props>();
const { currentLocale, setLocale, t, formatDate } = useI18n(props.locale);
</script>

<template>
    <Head :title="blog.title" />
    <ReadingLayout :locale="currentLocale" @locale-change="setLocale">
        <header class="blog-posts-header">
            <p class="reading-muted">{{ t('reader.blog_posts') }}</p>
            <h1>{{ blog.title }}</h1>
            <p class="reading-muted">{{ t('reader.posts_count', { count: posts.total }) }}</p>
        </header>
        <p v-if="posts.data.length === 0" class="reading-muted">{{ t('posts.empty') }}</p>
        <ol class="blog-posts-list">
            <li v-for="post in posts.data" :key="post.id">
                <time class="reading-muted">{{ formatDate(post.published_at) }}</time>
                <h2>
                    <Link :href="`/posts/${post.id}/read`" :data-post-id="post.id">{{
                        post.title
                    }}</Link>
                </h2>
                <Link :href="`/posts/${post.id}/read`" class="blog-read-link">{{
                    t('reader.read')
                }}</Link>
            </li>
        </ol>
        <nav
            v-if="posts.last_page > 1"
            class="blog-pagination"
            :aria-label="t('reader.pagination')"
        >
            <Link v-if="posts.prev_page_url" :href="posts.prev_page_url">{{
                t('reader.previous')
            }}</Link>
            <span>{{ posts.current_page }} / {{ posts.last_page }}</span>
            <Link v-if="posts.next_page_url" :href="posts.next_page_url">{{
                t('reader.next')
            }}</Link>
        </nav>
    </ReadingLayout>
</template>

<style scoped>
.blog-posts-header {
    padding-bottom: 2rem;
}
.blog-posts-header h1 {
    font:
        normal clamp(2.5rem, 6vw, 4rem)/1.15 Georgia,
        serif;
    margin: 0.5rem 0 1rem;
    overflow-wrap: anywhere;
}
.blog-posts-list li {
    padding: 1.5rem 0;
    border-top: 1px solid #d5cec2;
}
:global(.dark) .blog-posts-list li {
    border-color: #38342e;
}
.blog-posts-list time,
.blog-read-link {
    font-size: 0.875rem;
}
.blog-posts-list h2 {
    font:
        normal 1.6rem/1.4 Georgia,
        serif;
    margin: 0.5rem 0 0.75rem;
    overflow-wrap: anywhere;
}
.blog-read-link {
    text-decoration: underline;
    text-underline-offset: 3px;
}
.blog-pagination {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding-top: 2rem;
}
</style>
