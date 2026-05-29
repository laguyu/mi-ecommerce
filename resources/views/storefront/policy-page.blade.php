@extends('layouts.app', ['title' => $pageTitle])

@section('content')
    @php
        $allowedTags = '<h1><h2><h3><h4><p><ul><ol><li><strong><b><em><i><u><blockquote><a><br><table><thead><tbody><tr><th><td><hr>';
        $safeHtml = strip_tags((string) $pageContent, $allowedTags);
    @endphp

    <style>
        .policy-page {
            max-width: 1120px;
            margin: 0 auto;
            padding: 1.2rem 1.25rem;
        }

        .policy-page h1 {
            margin: 0 0 0.45rem;
            font-size: clamp(1.45rem, 2.4vw, 2rem);
            color: #0f172a;
            text-align: left;
        }

        .policy-page__article {
            margin-top: 0.95rem;
            color: #334155;
            line-height: 1.78;
            font-size: 1.02rem;
            text-align: left;
            overflow-wrap: anywhere;
        }

        .policy-page__article h2,
        .policy-page__article h3,
        .policy-page__article h4 {
            color: #0f172a;
            margin: 1.2rem 0 0.55rem;
        }

        .policy-page__article p,
        .policy-page__article ul,
        .policy-page__article ol,
        .policy-page__article blockquote,
        .policy-page__article table {
            margin: 0.6rem 0;
        }

        .policy-page__article ul,
        .policy-page__article ol {
            padding-left: 1.25rem;
        }

        .policy-page__article blockquote {
            border-left: 3px solid #93c5fd;
            padding-left: 0.8rem;
            color: #475569;
            background: #f8fafc;
            border-radius: 0.45rem;
            padding-top: 0.4rem;
            padding-bottom: 0.4rem;
        }

        .policy-page__article a {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .policy-page__article table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
            background: #fff;
        }

        .policy-page__article th,
        .policy-page__article td {
            border: 1px solid #e2e8f0;
            text-align: left;
            padding: 0.5rem 0.6rem;
        }
    </style>

    <section class="card policy-page">
        <h1>{{ $pageTitle }}</h1>

        @if(trim($pageContent) === '')
            <p style="color:#64748b; margin-top:.6rem;">Esta página aún no tiene contenido. Puedes configurarla desde el panel en Sitio.</p>
        @else
            <article class="policy-page__article">
                {!! $safeHtml !!}
            </article>
        @endif
    </section>
@endsection
