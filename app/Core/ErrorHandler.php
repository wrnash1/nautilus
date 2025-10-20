<?php

namespace App\Core;

use Exception;
use Throwable;

/**
 * Centralized Error Handler
 * Handles all exceptions and errors in the application
 */
class ErrorHandler
{
    private Logger $logger;
    private bool $debug;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
    }

    /**
     * Register error and exception handlers
     */
    public function register(): void
    {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Handle PHP errors
     */
    public function handleError(int $level, string $message, string $file = '', int $line = 0): bool
    {
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }

        return false;
    }

    /**
     * Handle uncaught exceptions
     */
    public function handleException(Throwable $exception): void
    {
        // Log the exception
        $this->logException($exception);

        // Send error response
        $this->sendErrorResponse($exception);
    }

    /**
     * Handle fatal errors on shutdown
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->handleException(new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            ));
        }
    }

    /**
     * Log exception details
     */
    private function logException(Throwable $exception): void
    {
        $context = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'url' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'user_id' => $_SESSION['user_id'] ?? 'guest'
        ];

        $this->logger->error('Uncaught Exception: ' . get_class($exception), $context);
    }

    /**
     * Send error response to client
     */
    private function sendErrorResponse(Throwable $exception): void
    {
        // Clear any previous output
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Set HTTP status code
        $statusCode = $this->getHttpStatusCode($exception);
        http_response_code($statusCode);

        // Check if AJAX request
        if ($this->isAjaxRequest()) {
            $this->sendJsonErrorResponse($exception, $statusCode);
        } else {
            $this->sendHtmlErrorResponse($exception, $statusCode);
        }
    }

    /**
     * Determine HTTP status code from exception
     */
    private function getHttpStatusCode(Throwable $exception): int
    {
        if (method_exists($exception, 'getStatusCode')) {
            return $exception->getStatusCode();
        }

        return 500;
    }

    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Send JSON error response
     */
    private function sendJsonErrorResponse(Throwable $exception, int $statusCode): void
    {
        header('Content-Type: application/json');

        $response = [
            'success' => false,
            'error' => [
                'code' => $statusCode,
                'message' => $this->debug ? $exception->getMessage() : 'An error occurred'
            ]
        ];

        if ($this->debug) {
            $response['error']['file'] = $exception->getFile();
            $response['error']['line'] = $exception->getLine();
            $response['error']['trace'] = explode("\n", $exception->getTraceAsString());
        }

        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    /**
     * Send HTML error response
     */
    private function sendHtmlErrorResponse(Throwable $exception, int $statusCode): void
    {
        if ($this->debug) {
            $this->renderDebugErrorPage($exception, $statusCode);
        } else {
            $this->renderProductionErrorPage($statusCode);
        }
    }

    /**
     * Render debug error page
     */
    private function renderDebugErrorPage(Throwable $exception, int $statusCode): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error <?= $statusCode ?> - <?= htmlspecialchars(get_class($exception)) ?></title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
                .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
                .header { background: #dc3545; color: white; padding: 20px 30px; }
                .header h1 { margin: 0; font-size: 24px; }
                .header p { margin: 10px 0 0; opacity: 0.9; }
                .content { padding: 30px; }
                .error-details { background: #f8f9fa; border-left: 4px solid #dc3545; padding: 15px; margin-bottom: 20px; }
                .error-details h3 { margin-top: 0; color: #dc3545; }
                .file-line { font-family: 'Courier New', monospace; background: #fff; padding: 10px; border: 1px solid #dee2e6; border-radius: 4px; }
                .stack-trace { background: #212529; color: #f8f9fa; padding: 20px; border-radius: 4px; overflow-x: auto; font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.6; }
                .stack-trace pre { margin: 0; white-space: pre-wrap; word-wrap: break-word; }
                .context { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 20px; }
                .context-item { background: #e9ecef; padding: 15px; border-radius: 4px; }
                .context-item strong { display: block; margin-bottom: 5px; color: #495057; }
                .context-item span { font-family: 'Courier New', monospace; font-size: 13px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🚨 Error <?= $statusCode ?>: <?= htmlspecialchars(get_class($exception)) ?></h1>
                    <p><?= htmlspecialchars($exception->getMessage()) ?></p>
                </div>
                <div class="content">
                    <div class="error-details">
                        <h3>Location</h3>
                        <div class="file-line">
                            <?= htmlspecialchars($exception->getFile()) ?>:<strong><?= $exception->getLine() ?></strong>
                        </div>
                    </div>

                    <h3>Stack Trace</h3>
                    <div class="stack-trace">
                        <pre><?= htmlspecialchars($exception->getTraceAsString()) ?></pre>
                    </div>

                    <h3>Request Context</h3>
                    <div class="context">
                        <div class="context-item">
                            <strong>Request Method</strong>
                            <span><?= htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'N/A') ?></span>
                        </div>
                        <div class="context-item">
                            <strong>Request URI</strong>
                            <span><?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A') ?></span>
                        </div>
                        <div class="context-item">
                            <strong>IP Address</strong>
                            <span><?= htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'N/A') ?></span>
                        </div>
                        <div class="context-item">
                            <strong>User ID</strong>
                            <span><?= htmlspecialchars($_SESSION['user_id'] ?? 'Guest') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Render production error page
     */
    private function renderProductionErrorPage(int $statusCode): void
    {
        $errorMessages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Page Not Found',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable'
        ];

        $message = $errorMessages[$statusCode] ?? 'An Error Occurred';

        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error <?= $statusCode ?></title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 0; padding: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
                .error-container { text-align: center; color: white; padding: 40px; }
                .error-code { font-size: 120px; font-weight: bold; margin: 0; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
                .error-message { font-size: 32px; margin: 20px 0; }
                .error-description { font-size: 18px; opacity: 0.9; margin-bottom: 30px; }
                .btn { display: inline-block; padding: 12px 30px; background: white; color: #667eea; text-decoration: none; border-radius: 5px; font-weight: 500; transition: transform 0.2s; }
                .btn:hover { transform: translateY(-2px); }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-code"><?= $statusCode ?></div>
                <div class="error-message"><?= $message ?></div>
                <div class="error-description">We're sorry, but something went wrong.</div>
                <a href="/" class="btn">Go to Homepage</a>
            </div>
        </body>
        </html>
        <?php
    }
}
