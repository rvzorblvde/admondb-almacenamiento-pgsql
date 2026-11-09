<?php

declare(strict_types=1);

/** Texto para imprimir de forma segura en HTML. */
function e(mixed $valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function iniciarSesion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
        ]);
    }
}

function tokenCsrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfValido(string $token): bool
{
    return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function mensajeFlash(string $tipo, string $texto): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
}

/** Devuelve el mensaje pendiente (una sola vez) o null. */
function obtenerFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function redirigir(string $url): never
{
    header('Location: ' . $url);
    exit;
}
