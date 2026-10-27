<?php

use Laravel\Dusk\Browser;

test('homepage loads correctly with editorial title and elements', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->waitForText('Larablogs')
            ->assertSee('Larablogs')
            ->assertPresent('form')
            ->assertPresent('@input-url')
            ->assertPresent('@input-email');
    });
});

test('dark mode is default and can be toggled to light mode', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->waitForText('Larablogs')
            // O tema escuro é padrão
            ->assertScript("document.documentElement.classList.contains('dark')", true)
            // Clica no botão para alternar para tema claro
            ->click('@theme-toggle')
            ->pause(300)
            ->assertScript("document.documentElement.classList.contains('dark')", false)
            // Clica para voltar ao tema escuro
            ->click('@theme-toggle')
            ->pause(300)
            ->assertScript("document.documentElement.classList.contains('dark')", true);
    });
});

test('can switch languages dynamically in the interface', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->waitForText('Larablogs')
            // Muda para Inglês
            ->select('@locale-select', 'en')
            ->pause(300)
            ->assertSee('Subscribe a new publication')
            ->assertSee('Add to Registry', true)
            // Muda para Espanhol
            ->select('@locale-select', 'es')
            ->pause(300)
            ->assertSee('Inscribir nueva publicación')
            ->assertSee('Agregar al Registro', true)
            // Volta para Português
            ->select('@locale-select', 'pt-BR')
            ->pause(300)
            ->assertSee('Inscrever nova publicação')
            ->assertSee('Adicionar ao Registro', true);
    });
});

test('can switch between publications and recent articles tabs', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->waitForText('Larablogs')
            // Clica na aba de Artigos Recentes
            ->click('@tab-posts')
            ->pause(300)
            ->assertPresent('div')
            // Volta para Publicações Cadastradas
            ->click('@tab-blogs')
            ->pause(300);
    });
});

test('quick suggestion buttons prefill the form', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->waitForText('Larablogs')
            ->click('@suggest-laravel-news')
            ->pause(200)
            ->assertInputValue('@input-url', 'https://laravel-news.com')
            ->assertInputValue('@input-email', 'reader@example.com');
    });
});

test('theme selection applies immediately and survives a reload', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')->script("localStorage.removeItem('larablogs_theme')");
        $browser->refresh()
            ->waitFor('@design-menu')
            ->click('@design-menu')
            ->click('@design-submenu')
            ->select('@design-select', 'terminal')
            ->waitForText('REGISTER_FEED_TARGET')
            ->assertScript("localStorage.getItem('larablogs_theme')", 'terminal')
            ->refresh()
            ->waitForText('REGISTER_FEED_TARGET')
            ->click('@design-menu')
            ->click('@design-submenu')
            ->select('@design-select', 'editorial')
            ->waitFor('@input-url');
    });
});
