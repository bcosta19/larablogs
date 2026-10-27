import { ref } from 'vue';

export type LocaleCode = 'pt-BR' | 'en' | 'es' | 'fr' | 'de';

export interface LocaleInfo {
    code: LocaleCode;
    name: string;
    flag: string;
}

export const availableLocales: LocaleInfo[] = [
    { code: 'pt-BR', name: 'Português', flag: '🇧🇷' },
    { code: 'en', name: 'English', flag: '🇺🇸' },
    { code: 'es', name: 'Español', flag: '🇪🇸' },
    { code: 'fr', name: 'Français', flag: '🇫🇷' },
    { code: 'de', name: 'Deutsch', flag: '🇩🇪' },
];

export const translations = {
    'pt-BR': {
        header: {
            badge: 'Boletim Digital • Monitor de Feeds',
            title: 'Larablogs',
            subtitle:
                'Curadoria contínua de publicações da web com avisos diretos na sua caixa postal.',
            stats_registered: '{count} publicações inscritas',
            stats_active: '{count} ativas',
            stats_archived: '{count} artigos arquivados',
        },
        mail: {
            title: 'E-mail',
            provider: 'Serviço',
            key: 'Chave de API',
            from: 'Remetente',
            help: 'Use um remetente de domínio verificado. O endereço de teste do Resend só envia para o e-mail da própria conta.',
            save: 'Salvar',
            recipient: 'E-mail para o teste',
            send_test: 'Enviar e-mail de teste',
            saved: 'Configuração de e-mail salva.',
            invalid_key: 'Informe uma chave válida.',
            invalid_from: 'Informe um remetente válido.',
            invalid_recipient: 'Informe um destinatário válido.',
            setup_required: 'Salve a configuração de e-mail antes de enviar o teste.',
            queue_error: 'Não foi possível iniciar o envio.',
            status_pending: 'Enviando...',
            status_sent: 'E-mail enviado.',
            status_failed: 'Não foi possível enviar o e-mail.',
            status_unknown: 'Não foi possível confirmar o envio.',
        },
        reader: {
            navigation: 'Navegação do leitor',
            language: 'Idioma',
            home: 'Todos os blogs',
            blog_posts: 'Artigos deste blog',
            posts_count: '{count} artigo(s)',
            read: 'Ler no Larablogs',
            pagination: 'Páginas de artigos',
            previous: 'Anterior',
            next: 'Próxima',
            more_from: 'Mais de {title}',
            original: 'Abrir original',
            font_size: 'Tamanho do texto',
            smaller: 'Diminuir texto',
            larger: 'Aumentar texto',
            unavailable:
                'Não foi possível extrair o conteúdo deste artigo. Você pode abrir a publicação original pelo link acima.',
        },
        theme: {
            name: 'Tema',
            choose: 'Escolher tema',
            light: 'Tema Claro',
            dark: 'Tema Escuro',
        },
        form: {
            title: 'Inscrever nova publicação',
            subtitle:
                'Insira o endereço do blog ou feed RSS para monitoramento e receba novas matérias.',
            url_label: 'Endereço Web (URL) *',
            url_placeholder: 'https://exemplo.com ou feed RSS',
            email_label: 'E-mail Destinatário *',
            email_placeholder: 'seu@email.com',
            title_label: 'Título (Opcional)',
            title_placeholder: 'Automático',
            suggestions: 'Sugestões:',
            submit: 'Adicionar ao Registro',
            submitting: 'Consultando Feed...',
        },
        tabs: {
            blogs: 'Publicações Cadastradas',
            posts: 'Artigos Recentes',
        },
        blogs: {
            empty: 'Nenhuma publicação cadastrada no momento. Adicione a primeira acima.',
            active: 'Ativo',
            paused: 'Pausado',
            notify_to: 'Notificar:',
            checked: 'Verificado:',
            articles_count: '{count} artigo(s) arquivados',
            btn_check: 'Verificar agora',
            btn_checking: 'Consultando...',
            btn_pause: 'Pausar',
            btn_resume: 'Reativar',
            btn_remove: 'Remover',
            latest_article: 'Último artigo registrado:',
            published_at: 'Publicado em {date}',
            confirm_delete:
                'Deseja remover "{title}" do monitoramento? Todos os artigos associados serão removidos.',
            error_prefix: 'Aviso na última checagem: {error}',
        },
        posts: {
            empty: 'Nenhum artigo arquivado ainda.',
            notified: 'Notificado',
            read_more: 'Ler artigo →',
        },
        time: {
            never: 'Nunca verificado',
            just_now: 'Agora',
            minutes_ago: '{count} min atrás',
            hours_ago: '{count} h atrás',
            date_unknown: 'Data não informada',
        },
        footer: {
            title: 'Larablogs • Edição Pessoal • Desenvolvido com Laravel 13, Vue.js 3 e Inertia.js',
            command_info: 'Verificação automática em segundo plano via',
        },
    },
    en: {
        header: {
            badge: 'Digital Gazette • Feed Monitor',
            title: 'Larablogs',
            subtitle:
                'Continuous curation of web publications with instant notices delivered to your inbox.',
            stats_registered: '{count} registered publications',
            stats_active: '{count} active',
            stats_archived: '{count} archived articles',
        },
        mail: {
            title: 'Email',
            provider: 'Service',
            key: 'API key',
            from: 'Sender',
            help: 'Use a sender from a verified domain. Resend’s test address only sends to your account email.',
            save: 'Save',
            recipient: 'Test recipient',
            send_test: 'Send test email',
            saved: 'Email settings saved.',
            invalid_key: 'Enter a valid API key.',
            invalid_from: 'Enter a valid sender.',
            invalid_recipient: 'Enter a valid recipient.',
            setup_required: 'Save your email settings before sending a test.',
            queue_error: 'Could not start sending.',
            status_pending: 'Sending...',
            status_sent: 'Email sent.',
            status_failed: 'Could not send the email.',
            status_unknown: 'Could not confirm delivery.',
        },
        reader: {
            navigation: 'Reader navigation',
            language: 'Language',
            home: 'All blogs',
            blog_posts: 'Posts from this blog',
            posts_count: '{count} article(s)',
            read: 'Read in Larablogs',
            pagination: 'Article pages',
            previous: 'Previous',
            next: 'Next',
            more_from: 'More from {title}',
            original: 'Open original',
            font_size: 'Text size',
            smaller: 'Smaller text',
            larger: 'Larger text',
            unavailable:
                'We could not extract this article. You can open the original publication using the link above.',
        },
        theme: {
            name: 'Theme',
            choose: 'Choose theme',
            light: 'Light Mode',
            dark: 'Dark Mode',
        },
        form: {
            title: 'Subscribe a new publication',
            subtitle:
                'Enter the blog or RSS feed URL to monitor and receive notifications for new posts.',
            url_label: 'Web Address (URL) *',
            url_placeholder: 'https://example.com or RSS feed',
            email_label: 'Recipient Email *',
            email_placeholder: 'you@email.com',
            title_label: 'Title (Optional)',
            title_placeholder: 'Automatic',
            suggestions: 'Suggestions:',
            submit: 'Add to Registry',
            submitting: 'Checking Feed...',
        },
        tabs: {
            blogs: 'Registered Publications',
            posts: 'Recent Articles',
        },
        blogs: {
            empty: 'No publications registered yet. Add the first one above.',
            active: 'Active',
            paused: 'Paused',
            notify_to: 'Notify:',
            checked: 'Checked:',
            articles_count: '{count} article(s) archived',
            btn_check: 'Check now',
            btn_checking: 'Checking...',
            btn_pause: 'Pause',
            btn_resume: 'Resume',
            btn_remove: 'Remove',
            latest_article: 'Latest recorded article:',
            published_at: 'Published on {date}',
            confirm_delete:
                'Do you really want to remove "{title}" from monitoring? All associated articles will be deleted.',
            error_prefix: 'Warning on last check: {error}',
        },
        posts: {
            empty: 'No articles archived yet.',
            notified: 'Notified',
            read_more: 'Read article →',
        },
        time: {
            never: 'Never checked',
            just_now: 'Just now',
            minutes_ago: '{count}m ago',
            hours_ago: '{count}h ago',
            date_unknown: 'Unknown date',
        },
        footer: {
            title: 'Larablogs • Personal Edition • Built with Laravel 13, Vue.js 3 and Inertia.js',
            command_info: 'Automatic background check via',
        },
    },
    es: {
        header: {
            badge: 'Boletín Digital • Monitor de Feeds',
            title: 'Larablogs',
            subtitle: 'Curaduría continua de publicaciones web con avisos directos en su buzón.',
            stats_registered: '{count} publicaciones inscritas',
            stats_active: '{count} activas',
            stats_archived: '{count} artículos archivados',
        },
        mail: {
            title: 'Correo',
            provider: 'Servicio',
            key: 'Clave de API',
            from: 'Remitente',
            help: 'Use un remitente de dominio verificado. La dirección de prueba de Resend solo envía al correo de la cuenta.',
            save: 'Guardar',
            recipient: 'Correo para la prueba',
            send_test: 'Enviar correo de prueba',
            saved: 'Configuración de correo guardada.',
            invalid_key: 'Introduzca una clave válida.',
            invalid_from: 'Introduzca un remitente válido.',
            invalid_recipient: 'Introduzca un destinatario válido.',
            setup_required: 'Guarde la configuración antes de enviar una prueba.',
            queue_error: 'No se pudo iniciar el envío.',
            status_pending: 'Enviando...',
            status_sent: 'Correo enviado.',
            status_failed: 'No se pudo enviar el correo.',
            status_unknown: 'No se pudo confirmar el envío.',
        },
        reader: {
            navigation: 'Navegación del lector',
            language: 'Idioma',
            home: 'Todos los blogs',
            blog_posts: 'Artículos de este blog',
            posts_count: '{count} artículo(s)',
            read: 'Leer en Larablogs',
            pagination: 'Páginas de artículos',
            previous: 'Anterior',
            next: 'Siguiente',
            more_from: 'Más de {title}',
            original: 'Abrir original',
            font_size: 'Tamaño del texto',
            smaller: 'Reducir texto',
            larger: 'Ampliar texto',
            unavailable:
                'No se pudo extraer este artículo. Puede abrir la publicación original en el enlace de arriba.',
        },
        theme: {
            name: 'Tema',
            choose: 'Elegir tema',
            light: 'Modo Claro',
            dark: 'Modo Oscuro',
        },
        form: {
            title: 'Inscribir nueva publicación',
            subtitle:
                'Ingrese la dirección del blog o feed RSS para monitorear y recibir nuevos artículos.',
            url_label: 'Dirección Web (URL) *',
            url_placeholder: 'https://ejemplo.com o feed RSS',
            email_label: 'Correo Destinatario *',
            email_placeholder: 'su@correo.com',
            title_label: 'Título (Opcional)',
            title_placeholder: 'Automático',
            suggestions: 'Sugerencias:',
            submit: 'Agregar al Registro',
            submitting: 'Consultando Feed...',
        },
        tabs: {
            blogs: 'Publicaciones Inscritas',
            posts: 'Artículos Recientes',
        },
        blogs: {
            empty: 'Ninguna publicación registrada todavía. Agregue la primera arriba.',
            active: 'Activo',
            paused: 'Pausado',
            notify_to: 'Notificar:',
            checked: 'Verificado:',
            articles_count: '{count} artículo(s) archivados',
            btn_check: 'Verificar ahora',
            btn_checking: 'Consultando...',
            btn_pause: 'Pausar',
            btn_resume: 'Reanudar',
            btn_remove: 'Eliminar',
            latest_article: 'Último artículo registrado:',
            published_at: 'Publicado el {date}',
            confirm_delete:
                '¿Desea eliminar "{title}" del monitoreo? Todos los artículos asociados serán eliminados.',
            error_prefix: 'Aviso en la última verificación: {error}',
        },
        posts: {
            empty: 'Ningún artículo archivado todavía.',
            notified: 'Notificado',
            read_more: 'Leer artículo →',
        },
        time: {
            never: 'Nunca verificado',
            just_now: 'Ahora',
            minutes_ago: 'Hace {count} min',
            hours_ago: 'Hace {count} h',
            date_unknown: 'Fecha no informada',
        },
        footer: {
            title: 'Larablogs • Edición Personal • Desarrollado con Laravel 13, Vue.js 3 e Inertia.js',
            command_info: 'Verificación automática en segundo plano mediante',
        },
    },
    fr: {
        header: {
            badge: 'Gazette Numérique • Moniteur de Flux',
            title: 'Larablogs',
            subtitle:
                'Sélection continue de publications web avec alertes directes dans votre boîte de réception.',
            stats_registered: '{count} publications inscrites',
            stats_active: '{count} actives',
            stats_archived: '{count} articles archivés',
        },
        mail: {
            title: 'E-mail',
            provider: 'Service',
            key: 'Clé API',
            from: 'Expéditeur',
            help: 'Utilisez un expéditeur dont le domaine est vérifié. L’adresse de test Resend envoie uniquement à votre compte.',
            save: 'Enregistrer',
            recipient: 'Destinataire du test',
            send_test: 'Envoyer un e-mail de test',
            saved: 'Configuration e-mail enregistrée.',
            invalid_key: 'Saisissez une clé valide.',
            invalid_from: 'Saisissez un expéditeur valide.',
            invalid_recipient: 'Saisissez un destinataire valide.',
            setup_required: 'Enregistrez la configuration avant le test.',
            queue_error: 'Impossible de démarrer l’envoi.',
            status_pending: 'Envoi en cours...',
            status_sent: 'E-mail envoyé.',
            status_failed: 'Impossible d’envoyer l’e-mail.',
            status_unknown: 'Impossible de confirmer l’envoi.',
        },
        reader: {
            navigation: 'Navigation du lecteur',
            language: 'Langue',
            home: 'Tous les blogs',
            blog_posts: 'Articles de ce blog',
            posts_count: '{count} article(s)',
            read: 'Lire dans Larablogs',
            pagination: 'Pages des articles',
            previous: 'Précédente',
            next: 'Suivante',
            more_from: 'Plus de {title}',
            original: 'Ouvrir l’original',
            font_size: 'Taille du texte',
            smaller: 'Réduire le texte',
            larger: 'Agrandir le texte',
            unavailable:
                'Impossible d’extraire cet article. Vous pouvez ouvrir la publication originale avec le lien ci-dessus.',
        },
        theme: {
            name: 'Thème',
            choose: 'Choisir un thème',
            light: 'Mode Clair',
            dark: 'Mode Sombre',
        },
        form: {
            title: 'Abonner une nouvelle publication',
            subtitle:
                "Entrez l'adresse du blog ou du flux RSS pour surveiller et recevoir les nouveaux articles.",
            url_label: 'Adresse Web (URL) *',
            url_placeholder: 'https://exemple.com ou flux RSS',
            email_label: 'E-mail Destinataire *',
            email_placeholder: 'vous@email.com',
            title_label: 'Titre (Optionnel)',
            title_placeholder: 'Automatique',
            suggestions: 'Suggestions :',
            submit: 'Ajouter au Registre',
            submitting: 'Vérification du flux...',
        },
        tabs: {
            blogs: 'Publications Inscrites',
            posts: 'Articles Récents',
        },
        blogs: {
            empty: "Aucune publication enregistrée pour l'instant. Ajoutez la première ci-dessus.",
            active: 'Actif',
            paused: 'En pause',
            notify_to: 'Notifier :',
            checked: 'Vérifié :',
            articles_count: '{count} article(s) archivés',
            btn_check: 'Vérifier maintenant',
            btn_checking: 'Vérification...',
            btn_pause: 'Suspendre',
            btn_resume: 'Reprendre',
            btn_remove: 'Supprimer',
            latest_article: 'Dernier article enregistré :',
            published_at: 'Publié le {date}',
            confirm_delete:
                'Voulez-vous vraiment supprimer "{title}" de la surveillance ? Tous les articles associés seront supprimés.',
            error_prefix: 'Avertissement lors de la dernière vérification : {error}',
        },
        posts: {
            empty: 'Aucun article archivé pour le moment.',
            notified: 'Notifié',
            read_more: "Lire l'article →",
        },
        time: {
            never: 'Jamais vérifié',
            just_now: "À l'instant",
            minutes_ago: 'Il y a {count} min',
            hours_ago: 'Il y a {count} h',
            date_unknown: 'Date inconnue',
        },
        footer: {
            title: 'Larablogs • Édition Personnelle • Développé avec Laravel 13, Vue.js 3 et Inertia.js',
            command_info: 'Vérification automatique en arrière-plan via',
        },
    },
    de: {
        header: {
            badge: 'Digitales Bulletin • Feed-Monitor',
            title: 'Larablogs',
            subtitle:
                'Kontinuierliche Kuration von Web-Publikationen mit Benachrichtigungen direkt in Ihr Postfach.',
            stats_registered: '{count} registrierte Publikationen',
            stats_active: '{count} aktiv',
            stats_archived: '{count} archivierte Artikel',
        },
        mail: {
            title: 'E-Mail',
            provider: 'Dienst',
            key: 'API-Schlüssel',
            from: 'Absender',
            help: 'Verwenden Sie einen Absender einer verifizierten Domain. Die Resend-Testadresse sendet nur an Ihr Konto.',
            save: 'Speichern',
            recipient: 'Testempfänger',
            send_test: 'Test-E-Mail senden',
            saved: 'E-Mail-Einstellungen gespeichert.',
            invalid_key: 'Geben Sie einen gültigen Schlüssel ein.',
            invalid_from: 'Geben Sie einen gültigen Absender ein.',
            invalid_recipient: 'Geben Sie einen gültigen Empfänger ein.',
            setup_required: 'Speichern Sie die Einstellungen vor dem Test.',
            queue_error: 'Der Versand konnte nicht gestartet werden.',
            status_pending: 'Wird gesendet...',
            status_sent: 'E-Mail gesendet.',
            status_failed: 'E-Mail konnte nicht gesendet werden.',
            status_unknown: 'Versand konnte nicht bestätigt werden.',
        },
        reader: {
            navigation: 'Lesernavigation',
            language: 'Sprache',
            home: 'Alle Blogs',
            blog_posts: 'Beiträge dieses Blogs',
            posts_count: '{count} Artikel',
            read: 'In Larablogs lesen',
            pagination: 'Artikelseiten',
            previous: 'Zurück',
            next: 'Weiter',
            more_from: 'Mehr von {title}',
            original: 'Original öffnen',
            font_size: 'Textgröße',
            smaller: 'Kleinerer Text',
            larger: 'Größerer Text',
            unavailable:
                'Dieser Artikel konnte nicht extrahiert werden. Über den Link oben können Sie das Original öffnen.',
        },
        theme: {
            name: 'Design',
            choose: 'Design wählen',
            light: 'Heller Modus',
            dark: 'Dunkler Modus',
        },
        form: {
            title: 'Neue Publikation abonnieren',
            subtitle: 'Geben Sie die Blog- oder RSS-Feed-URL ein, um neue Artikel zu überwachen.',
            url_label: 'Web-Adresse (URL) *',
            url_placeholder: 'https://beispiel.de oder RSS-Feed',
            email_label: 'Empfänger-E-Mail *',
            email_placeholder: 'sie@email.de',
            title_label: 'Titel (Optional)',
            title_placeholder: 'Automatisch',
            suggestions: 'Vorschläge:',
            submit: 'Zum Register hinzufügen',
            submitting: 'Prüfe Feed...',
        },
        tabs: {
            blogs: 'Registrierte Publikationen',
            posts: 'Neueste Artikel',
        },
        blogs: {
            empty: 'Noch keine Publikationen registriert. Fügen Sie oben die erste hinzu.',
            active: 'Aktiv',
            paused: 'Pausiert',
            notify_to: 'Benachrichtigen:',
            checked: 'Geprüft:',
            articles_count: '{count} archivierte(r) Artikel',
            btn_check: 'Jetzt prüfen',
            btn_checking: 'Wird geprüft...',
            btn_pause: 'Pausieren',
            btn_resume: 'Fortsetzen',
            btn_remove: 'Entfernen',
            latest_article: 'Zuletzt erfasster Artikel:',
            published_at: 'Veröffentlicht am {date}',
            confirm_delete:
                'Möchten Sie "{title}" wirklich aus der Überwachung entfernen? Alle zugehörigen Artikel werden gelöscht.',
            error_prefix: 'Warnung bei der letzten Überprüfung: {error}',
        },
        posts: {
            empty: 'Noch keine Artikel archiviert.',
            notified: 'Benachrichtigt',
            read_more: 'Artikel lesen →',
        },
        time: {
            never: 'Noch nie geprüft',
            just_now: 'Gerade eben',
            minutes_ago: 'Vor {count} Min.',
            hours_ago: 'Vor {count} Std.',
            date_unknown: 'Unbekanntes Datum',
        },
        footer: {
            title: 'Larablogs • Persönliche Edition • Entwickelt mit Laravel 13, Vue.js 3 und Inertia.js',
            command_info: 'Automatische Hintergrundprüfung via',
        },
    },
};

export function useI18n(initialLocale: LocaleCode = 'en') {
    const currentLocale = ref<LocaleCode>(initialLocale);
    const initLocale = () => {
        document.documentElement.lang = currentLocale.value;
    };

    const setLocale = (code: LocaleCode) => {
        if (code in translations) {
            currentLocale.value = code;
            localStorage.setItem('larablogs_locale', code);
            document.cookie = `larablogs_locale=${encodeURIComponent(code)}; Path=/; Max-Age=31536000; SameSite=Lax${location.protocol === 'https:' ? '; Secure' : ''}`;
            document.documentElement.lang = code;
        }
    };

    const t = (path: string, params: Record<string, string | number> = {}): string => {
        const dict = translations[currentLocale.value] || translations['pt-BR'];
        const keys = path.split('.');
        let val: any = dict;

        for (const k of keys) {
            if (val && typeof val === 'object' && k in val) {
                val = val[k];
            } else {
                // Fallback para inglês ou português
                let fallbackVal: any = translations['en'] as any;
                for (const fk of keys) {
                    if (fallbackVal && typeof fallbackVal === 'object' && fk in fallbackVal) {
                        fallbackVal = fallbackVal[fk];
                    } else {
                        return path;
                    }
                }
                val = fallbackVal;
                break;
            }
        }

        if (typeof val !== 'string') return path;

        // Interpolação de {variáveis}
        return val.replace(/\{(\w+)\}/g, (_, key) => {
            return params[key] !== undefined ? String(params[key]) : `{${key}}`;
        });
    };

    const formatDate = (dateStr: string | null): string => {
        if (!dateStr) return t('time.date_unknown');
        try {
            const d = new Date(dateStr);
            const intlLocale = currentLocale.value === 'pt-BR' ? 'pt-BR' : currentLocale.value;
            return d.toLocaleDateString(intlLocale, {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        } catch {
            return dateStr;
        }
    };

    const formatRelative = (dateStr: string | null): string => {
        if (!dateStr) return t('time.never');
        try {
            const d = new Date(dateStr);
            const diff = Math.floor((Date.now() - d.getTime()) / 1000);
            if (diff < 60) return t('time.just_now');
            if (diff < 3600) return t('time.minutes_ago', { count: Math.floor(diff / 60) });
            if (diff < 86400) return t('time.hours_ago', { count: Math.floor(diff / 3600) });
            const intlLocale = currentLocale.value === 'pt-BR' ? 'pt-BR' : currentLocale.value;
            return d.toLocaleDateString(intlLocale, { day: '2-digit', month: 'short' });
        } catch {
            return dateStr;
        }
    };

    return {
        currentLocale,
        availableLocales,
        initLocale,
        setLocale,
        t,
        formatDate,
        formatRelative,
    };
}
