export interface BlogPostItem {
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

export interface BlogItem {
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

export interface BlogStats {
    total_blogs: number;
    active_blogs: number;
    total_posts: number;
}

export interface FlashMessages {
    success?: string | null;
    error?: string | null;
    info?: string | null;
}
