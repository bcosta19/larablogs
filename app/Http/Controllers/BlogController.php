<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogPost;
use App\Services\FeedService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    /**
     * Renderiza a página única do Larablogs com a lista de blogs e novidades.
     */
    public function index(): Response
    {
        $blogs = Blog::query()
            ->withCount('posts')
            ->with(['latestPost'])
            ->latest('id')
            ->get();

        $recentPosts = BlogPost::query()
            ->with('blog:id,title,url')
            ->latest('published_at')
            ->take(12)
            ->get();

        $stats = [
            'total_blogs' => Blog::count(),
            'active_blogs' => Blog::where('is_active', true)->count(),
            'total_posts' => BlogPost::count(),
        ];

        return Inertia::render('Welcome', [
            'blogs' => $blogs,
            'recentPosts' => $recentPosts,
            'stats' => $stats,
        ]);
    }

    /**
     * Cadastra um novo blog para monitoramento.
     */
    public function store(Request $request, FeedService $feedService): RedirectResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'string', 'max:500'],
            'notify_email' => ['required', 'email', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
        ], [
            'url.required' => 'Informe a URL do blog ou feed.',
            'notify_email.required' => 'Informe o e-mail para receber as notificações.',
            'notify_email.email' => 'Informe um endereço de e-mail válido.',
        ]);

        $rawUrl = trim($validated['url']);
        if (! preg_match('~^https?://~i', $rawUrl)) {
            $rawUrl = 'https://'.$rawUrl;
        }

        try {
            $discovered = $feedService->discoverFeed($rawUrl);
        } catch (Exception $e) {
            return back()->withErrors([
                'url' => 'Erro ao identificar feed RSS/Atom: '.$e->getMessage(),
            ])->withInput();
        }

        // Verifica se já existe esse feed cadastrado para o mesmo email
        $existing = Blog::where('feed_url', $discovered['feed_url'])
            ->where('notify_email', $validated['notify_email'])
            ->first();

        if ($existing) {
            return back()->withErrors([
                'url' => "Este feed ('{$discovered['feed_url']}') já está cadastrado para o e-mail {$validated['notify_email']}.",
            ])->withInput();
        }

        $title = ! empty($validated['title']) ? trim($validated['title']) : ($discovered['title'] ?: parse_url($rawUrl, PHP_URL_HOST));

        $blog = Blog::create([
            'title' => $title,
            'url' => $discovered['website_url'] ?: $rawUrl,
            'feed_url' => $discovered['feed_url'],
            'notify_email' => $validated['notify_email'],
            'is_active' => true,
        ]);

        // Faz uma sincronização inicial de baseline (sem notificar os posts históricos)
        try {
            $baseline = $feedService->checkBlog($blog, notify: false);
            $count = $baseline['new_posts_count'];
            $msg = "Blog '{$blog->title}' adicionado com sucesso! Identificamos {$count} postagem(ns) existentes como ponto de partida.";
        } catch (Exception) {
            $msg = "Blog '{$blog->title}' adicionado com sucesso! O feed será sincronizado na primeira verificação.";
        }

        return redirect()->route('home')->with('success', $msg);
    }

    /**
     * Força a verificação imediata do feed do blog.
     */
    public function check(Blog $blog, FeedService $feedService): RedirectResponse
    {
        try {
            $result = $feedService->checkBlog($blog, notify: true);
            $newCount = $result['new_posts_count'];

            if ($newCount > 0) {
                return back()->with('success', "✓ {$newCount} novo(s) artigo(s) encontrado(s) em '{$blog->title}'! Notificação disparada para {$blog->notify_email}.");
            }

            return back()->with('info', "Verificação concluída para '{$blog->title}'. O feed já está atualizado (nenhum post novo).");
        } catch (Exception $e) {
            return back()->with('error', "Falha ao verificar '{$blog->title}': ".$e->getMessage());
        }
    }

    /**
     * Alterna o status ativo/pausado do monitoramento do blog.
     */
    public function toggle(Blog $blog): RedirectResponse
    {
        $blog->is_active = ! $blog->is_active;
        $blog->save();

        $status = $blog->is_active ? 'ativado' : 'pausado';

        return back()->with('success', "Monitoramento de '{$blog->title}' foi {$status}.");
    }

    /**
     * Remove o blog e suas postagens associadas.
     */
    public function destroy(Blog $blog): RedirectResponse
    {
        $title = $blog->title;
        $blog->delete();

        return back()->with('success', "Blog '{$title}' e seu histórico foram removidos.");
    }
}
