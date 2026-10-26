<?php

namespace App\Notifications;

use App\Models\Blog;
use App\Models\BlogPost;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBlogPostNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Blog $blog,
        public BlogPost $post
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $publishedAt = $this->post->published_at;

        $published = $publishedAt !== null
            ? $publishedAt->timezone('America/Sao_Paulo')->format('d/m/Y H:i')
            : 'Recentemente';

        return (new MailMessage)
            ->subject("[Larablogs] Novo post em {$this->blog->title}: {$this->post->title}")
            ->greeting('Olá!')
            ->line("Um novo artigo foi publicado no blog **{$this->blog->title}**, que você está acompanhando:")
            ->line("📝 **Título:** {$this->post->title}")
            ->line("📅 **Publicado em:** {$published}")
            ->action('Ler Artigo Completo', $this->post->url)
            ->line("Você está recebendo esta notificação porque cadastrou seu e-mail no Larablogs para acompanhar {$this->blog->url}.");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'blog_id' => $this->blog->id,
            'blog_title' => $this->blog->title,
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'post_url' => $this->post->url,
            'published_at' => $this->post->published_at?->toIso8601String(),
        ];
    }
}
