<?php

namespace App\Console\Commands;

use App\Models\Blog;
use App\Services\FeedService;
use Exception;
use Illuminate\Console\Command;

class CheckBlogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blogs:check {--blog= : ID do blog específico para verificar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica os feeds dos blogs cadastrados e envia notificações de novas publicações';

    /**
     * Execute the console command.
     */
    public function handle(FeedService $feedService): int
    {
        $blogId = $this->option('blog');

        $query = Blog::query();
        if ($blogId) {
            $query->where('id', $blogId);
        } else {
            $query->where('is_active', true);
        }

        $blogs = $query->get();

        if ($blogs->isEmpty()) {
            $this->info('Nenhum blog ativo encontrado para verificar.');

            return self::SUCCESS;
        }

        $this->info("Iniciando verificação de {$blogs->count()} blog(s)...");

        $totalNew = 0;

        foreach ($blogs as $blog) {
            $this->line("→ Verificando: {$blog->title} ({$blog->feed_url})...");
            try {
                $result = $feedService->checkBlog($blog, notify: true);
                $newCount = $result['new_posts_count'];
                $totalNew += $newCount;

                if ($newCount > 0) {
                    $this->info("  ✓ {$newCount} novo(s) artigo(s) encontrado(s) e notificação enviada para {$blog->notify_email}!");
                } else {
                    $this->comment('  • Nenhum artigo novo. Feed em dia.');
                }
            } catch (Exception $e) {
                $this->error("  ✗ Erro: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Verificação concluída! Total de novos artigos detectados: {$totalNew}");

        return self::SUCCESS;
    }
}
