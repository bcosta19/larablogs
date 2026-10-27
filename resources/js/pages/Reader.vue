<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ReadingLayout from '@/components/ReadingLayout.vue';
import { useI18n, type LocaleCode } from '@/i18n';

const props = defineProps<{
    locale: LocaleCode;
    post: { id: number; title: string; url: string; published_at: string | null };
    blog: { id: number; title: string; url: string };
    blocks: { type: string; text: string }[];
    unavailable: boolean;
}>();
const { currentLocale, setLocale, t, formatDate } = useI18n(props.locale);
const fontSize = ref(20);
const sourceUrl = computed(() => {
    try {
        const url = new URL(props.post.url);
        return ['http:', 'https:'].includes(url.protocol) ? url.href : null;
    } catch {
        return null;
    }
});
</script>

<template>
    <Head :title="post.title" />
    <ReadingLayout :locale="currentLocale" @locale-change="setLocale">
        <Link :href="`/blogs/${blog.id}`" class="reader-back">{{
            t('reader.more_from', { title: blog.title })
        }}</Link>
        <article class="reader-article">
            <header>
                <h1>{{ post.title }}</h1>
                <p class="reading-muted">{{ blog.title }} · {{ formatDate(post.published_at) }}</p>
                <div class="reader-tools">
                    <a
                        v-if="sourceUrl"
                        :href="sourceUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        >{{ t('reader.original') }}</a
                    >
                    <div class="reader-font" :aria-label="t('reader.font_size')">
                        <button
                            type="button"
                            :disabled="fontSize <= 16"
                            :aria-label="t('reader.smaller')"
                            @click="fontSize -= 2"
                        >
                            A−
                        </button>
                        <button
                            type="button"
                            :disabled="fontSize >= 28"
                            :aria-label="t('reader.larger')"
                            @click="fontSize += 2"
                        >
                            A+
                        </button>
                    </div>
                </div>
            </header>
            <div v-if="unavailable" class="reader-unavailable" role="status">
                {{ t('reader.unavailable') }}
            </div>
            <div
                v-else
                class="reader-body"
                :style="{ fontSize: `${fontSize}px` }"
                dusk="article-content"
            >
                <template v-for="(block, index) in blocks" :key="index">
                    <h2 v-if="block.type === 'heading'">{{ block.text }}</h2>
                    <pre v-else-if="block.type === 'code'"><code>{{ block.text }}</code></pre>
                    <blockquote v-else-if="block.type === 'quote'">{{ block.text }}</blockquote>
                    <p v-else :class="{ 'reader-list-item': block.type === 'list' }">
                        {{ block.text }}
                    </p>
                </template>
            </div>
        </article>
    </ReadingLayout>
</template>

<style scoped>
.reader-back {
    font-size: 0.875rem;
    text-decoration: underline;
    text-underline-offset: 3px;
}
.reader-article {
    margin-top: 2rem;
}
.reader-article h1 {
    font:
        normal clamp(2rem, 5vw, 3.5rem)/1.15 Georgia,
        serif;
    margin: 0 0 1rem;
    overflow-wrap: anywhere;
}
.reader-tools {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: center;
    margin: 1.5rem 0 2rem;
    padding: 1rem 0;
    border-block: 1px solid #d5cec2;
    font-size: 0.875rem;
}
.reader-tools a {
    text-decoration: underline;
}
.reader-font {
    display: flex;
    gap: 0.5rem;
}
.reader-font button {
    cursor: pointer;
    border: 1px solid #9c9488;
    border-radius: 0.3rem;
    padding: 0.35rem 0.65rem;
}
.reader-font button:disabled {
    opacity: 0.4;
    cursor: default;
}
.reader-body {
    font-family: Georgia, serif;
    line-height: 1.8;
    overflow-wrap: anywhere;
}
.reader-body p,
.reader-body blockquote,
.reader-body pre {
    margin: 0 0 1.4em;
}
.reader-body h2 {
    font-size: 1.4em;
    line-height: 1.4;
    margin: 1.8em 0 0.75em;
}
.reader-body pre {
    font:
        0.8em/1.6 ui-monospace,
        monospace;
    overflow-x: auto;
    padding: 1rem;
    background: #eae5dc;
    border-radius: 0.3rem;
}
.reader-body blockquote {
    border-left: 3px solid #9c9488;
    padding-left: 1.25rem;
}
.reader-list-item {
    padding-left: 1rem;
    border-left: 2px solid #d5cec2;
}
.reader-unavailable {
    padding: 2rem 0;
    line-height: 1.7;
}
:global(.dark) .reader-tools {
    border-color: #38342e;
}
:global(.dark) .reader-body pre {
    background: #24211e;
}
</style>
