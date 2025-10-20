<?php

namespace App\Middleware;

/**
 * Security Headers Middleware
 * Adds security-related HTTP headers to responses
 */
class SecurityHeadersMiddleware
{
    /**
     * Handle security headers
     */
    public function handle(callable $next)
    {
        // Add security headers
        $this->addSecurityHeaders();

        // Continue to next middleware
        return $next();
    }

    /**
     * Add security headers to response
     */
    private function addSecurityHeaders(): void
    {
        // Prevent clickjacking
        header("X-Frame-Options: SAMEORIGIN");

        // Enable XSS protection
        header("X-XSS-Protection: 1; mode=block");

        // Prevent MIME sniffing
        header("X-Content-Type-Options: nosniff");

        // Referrer policy
        header("Referrer-Policy: strict-origin-when-cross-origin");

        // Content Security Policy
        $csp = $this->buildCSP();
        header("Content-Security-Policy: {$csp}");

        // HSTS (HTTP Strict Transport Security) - only in production
        if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
        }

        // Permissions Policy (formerly Feature Policy)
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

        // Remove server signature
        header_remove("X-Powered-By");
    }

    /**
     * Build Content Security Policy
     */
    private function buildCSP(): string
    {
        $policies = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://code.jquery.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
            "font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com",
            "img-src 'self' data: https:",
            "connect-src 'self'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'"
        ];

        return implode('; ', $policies);
    }
}
