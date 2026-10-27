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
            ->assertScript('document.documentElement.lang', 'en')
            ->assertSee('Subscribe a new publication')
            ->assertSee('Add to Registry', true)
            // Muda para Espanhol
            ->select('@locale-select', 'es')
            ->pause(300)
            ->assertScript('document.documentElement.lang', 'es')
            ->assertSee('Inscribir nueva publicación')
            ->assertSee('Agregar al Registro', true)
            // Volta para Português
            ->select('@locale-select', 'pt-BR')
            ->pause(300)
            ->assertScript('document.documentElement.lang', 'pt-BR')
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

test('local user can configure email without exposing the key again', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->waitFor('@design-menu')
            ->select('@locale-select', 'en')
            ->click('@design-menu')
            ->click('@mail-menu')
            ->type('#mail-recipient', 'recipient@example.com')
            ->click('@mail-test')
            ->waitForText('Save your email settings before sending a test.')
            ->type('#mail-key', 'browser-test-key')
            ->type('#mail-from', 'reader@example.com')
            ->click('@mail-save')
            ->waitForText('Email settings saved.')
            ->refresh()
            ->waitFor('@design-menu')
            ->click('@design-menu')
            ->click('@mail-menu')
            ->assertInputValue('#mail-from', 'reader@example.com')
            ->assertInputValue('#mail-key', '');
    });
});

test('blog navigation keeps posts separate and opens the internal reader', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')->waitForText('Reader fixture')
            ->click('a[href="/blogs/1"]')->waitForText('An article for reading')
            ->assertDontSee('Other blog article')
            ->click('a[href="/posts/1/read"]')->waitFor('@article-content')
            ->assertPathIs('/posts/1/read')->assertSee('This is the isolated reader content.');
    });
});

test('first login requires changing the temporary password', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/account/password')->waitFor('#current-password');
        $browser->script("document.querySelector('.account-signout').click()");
        $browser->waitFor('#login-email')
            ->type('#login-email', 'temporary@example.com')
            ->type('#login-password', 'temporary-fixture-password')
            ->click('@login-submit')->waitFor('#current-password')
            ->assertPathIs('/account/password')
            ->type('#current-password', 'temporary-fixture-password')
            ->type('#new-password', 'new-browser-fixture-password')
            ->type('#confirm-password', 'new-browser-fixture-password')
            ->click('@password-submit')->waitFor('@design-menu')->assertPathIs('/');
    });
});
