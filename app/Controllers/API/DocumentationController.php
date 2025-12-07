<?php

namespace App\Controllers\API;

class DocumentationController
{
    /**
     * Display API documentation
     */
    public function index()
    {
        $pageTitle = 'API Documentation';
        $activeMenu = 'api';

        $content = $this->renderDocumentation();

        require BASE_PATH . '/app/Views/layouts/app.php';
    }

    /**
     * Render API documentation
     */
    private function renderDocumentation(): string
    {
        ob_start();
        ?>
        <div class="container-fluid">
            <h1 class="mb-4"><i class="bi bi-book"></i> API Documentation</h1>

            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Base URL:</strong> <code><?= htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']) ?>/api/v1</code>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Authentication</h4>
                </div>
                <div class="card-body">
                    <p>All API requests require authentication using a Bearer token:</p>
                    <pre class="bg-light p-3"><code>Authorization: Bearer YOUR_API_TOKEN</code></pre>
                    <p>Create an API token from the <a href="/api/tokens">Token Management</a> page.</p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Available Endpoints</h4>
                </div>
                <div class="card-body">
                    <div class="accordion" id="apiEndpoints">
                        <!-- Customers -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#customersEndpoint">
                                    <strong>Customers</strong>
                                </button>
                            </h2>
                            <div id="customersEndpoint" class="accordion-collapse collapse" data-bs-parent="#apiEndpoints">
                                <div class="accordion-body">
                                    <p><span class="badge bg-primary">GET</span> <code>/api/v1/customers</code> - List all customers</p>
                                    <p><span class="badge bg-primary">GET</span> <code>/api/v1/customers/{id}</code> - Get customer details</p>
                                    <p><span class="badge bg-success">POST</span> <code>/api/v1/customers</code> - Create customer</p>
                                    <p><span class="badge bg-warning">PUT</span> <code>/api/v1/customers/{id}</code> - Update customer</p>
                                    <p><span class="badge bg-danger">DELETE</span> <code>/api/v1/customers/{id}</code> - Delete customer</p>
                                </div>
                            </div>
                        </div>

                        <!-- Products -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#productsEndpoint">
                                    <strong>Products</strong>
                                </button>
                            </h2>
                            <div id="productsEndpoint" class="accordion-collapse collapse" data-bs-parent="#apiEndpoints">
                                <div class="accordion-body">
                                    <p><span class="badge bg-primary">GET</span> <code>/api/v1/products</code> - List all products</p>
                                    <p><span class="badge bg-primary">GET</span> <code>/api/v1/products/{id}</code> - Get product details</p>
                                    <p><span class="badge bg-success">POST</span> <code>/api/v1/products</code> - Create product</p>
                                    <p><span class="badge bg-warning">PUT</span> <code>/api/v1/products/{id}</code> - Update product</p>
                                    <p><span class="badge bg-danger">DELETE</span> <code>/api/v1/products/{id}</code> - Delete product</p>
                                </div>
                            </div>
                        </div>

                        <!-- Orders -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ordersEndpoint">
                                    <strong>Orders</strong>
                                </button>
                            </h2>
                            <div id="ordersEndpoint" class="accordion-collapse collapse" data-bs-parent="#apiEndpoints">
                                <div class="accordion-body">
                                    <p><span class="badge bg-primary">GET</span> <code>/api/v1/orders</code> - List all orders</p>
                                    <p><span class="badge bg-primary">GET</span> <code>/api/v1/orders/{id}</code> - Get order details</p>
                                    <p><span class="badge bg-success">POST</span> <code>/api/v1/orders</code> - Create order</p>
                                    <p><span class="badge bg-warning">PUT</span> <code>/api/v1/orders/{id}</code> - Update order</p>
                                </div>
                            </div>
                        </div>

                        <!-- Transactions -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#transactionsEndpoint">
                                    <strong>Transactions (POS)</strong>
                                </button>
                            </h2>
                            <div id="transactionsEndpoint" class="accordion-collapse collapse" data-bs-parent="#apiEndpoints">
                                <div class="accordion-body">
                                    <p><span class="badge bg-primary">GET</span> <code>/api/v1/transactions</code> - List all transactions</p>
                                    <p><span class="badge bg-primary">GET</span> <code>/api/v1/transactions/{id}</code> - Get transaction details</p>
                                    <p><span class="badge bg-success">POST</span> <code>/api/v1/transactions</code> - Create transaction</p>
                                </div>
                            </div>
                        </div>

                        <!-- Courses -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#coursesEndpoint">
                                    <strong>Courses</strong>
                                </button>
                            </h2>
                            <div id="coursesEndpoint" class="accordion-collapse collapse" data-bs-parent="#apiEndpoints">
                                <div class="accordion-body">
                                    <p><span class="badge bg-primary">GET</span> <code>/api/v1/courses</code> - List all courses</p>
                                    <p><span class="badge bg-primary">GET</span> <code>/api/v1/courses/{id}</code> - Get course details</p>
                                    <p><span class="badge bg-primary">GET</span> <code>/api/v1/courses/{id}/schedules</code> - Get course schedules</p>
                                </div>
                            </div>
                        </div>

                        <!-- Rentals -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rentalsEndpoint">
                                    <strong>Rentals</strong>
                                </button>
                            </h2>
                            <div id="rentalsEndpoint" class="accordion-collapse collapse" data-bs-parent="#apiEndpoints">
                                <div class="accordion-body">
                                    <p><span class="badge bg-primary">GET</span> <code>/api/v1/rentals</code> - List all rental equipment</p>
                                    <p><span class="badge bg-primary">GET</span> <code>/api/v1/rentals/{id}</code> - Get rental details</p>
                                    <p><span class="badge bg-primary">GET</span> <code>/api/v1/rentals/reservations</code> - List reservations</p>
                                    <p><span class="badge bg-success">POST</span> <code>/api/v1/rentals/reservations</code> - Create reservation</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Response Format</h4>
                </div>
                <div class="card-body">
                    <p>All responses are returned in JSON format:</p>
                    <pre class="bg-light p-3"><code>{
  "success": true,
  "data": { ... },
  "message": "Success message"
}</code></pre>
                    <p>Error responses:</p>
                    <pre class="bg-light p-3"><code>{
  "success": false,
  "error": "Error message",
  "code": 400
}</code></pre>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Rate Limiting</h4>
                </div>
                <div class="card-body">
                    <p>API requests are rate-limited to prevent abuse:</p>
                    <ul>
                        <li><strong>Rate Limit:</strong> 100 requests per minute per token</li>
                        <li><strong>Headers:</strong> Check <code>X-RateLimit-Remaining</code> header for remaining requests</li>
                        <li><strong>429 Response:</strong> Too many requests - wait before retrying</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Example Request</h4>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3"><code>curl -X GET "<?= htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']) ?>/api/v1/customers" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json"</code></pre>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
