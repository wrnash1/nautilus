<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class HelpController extends Controller
{
    /**
     * Show help center homepage
     */
    public function index()
    {
        $db = Database::getInstance()->getConnection();
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        // Get popular help articles
        $stmt = $db->prepare("
            SELECT id, title, slug, category, excerpt, views
            FROM help_articles
            WHERE tenant_id = ? AND is_published = 1
            ORDER BY views DESC
            LIMIT 10
        ");
        $stmt->execute([$tenantId]);
        $popularArticles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get categories
        $stmt = $db->prepare("
            SELECT DISTINCT category
            FROM help_articles
            WHERE tenant_id = ? AND is_published = 1
            ORDER BY category
        ");
        $stmt->execute([$tenantId]);
        $categories = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $this->view('help/index', [
            'popularArticles' => $popularArticles,
            'categories' => $categories,
            'pageTitle' => 'Help Center'
        ]);
    }

    /**
     * Show help article
     */
    public function article($slug)
    {
        $db = Database::getInstance()->getConnection();
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        $stmt = $db->prepare("
            SELECT * FROM help_articles
            WHERE tenant_id = ? AND slug = ? AND is_published = 1
            LIMIT 1
        ");
        $stmt->execute([$tenantId, $slug]);
        $article = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$article) {
            $_SESSION['error'] = 'Help article not found';
            redirect('/help');
            return;
        }

        // Increment view count
        $stmt = $db->prepare("
            UPDATE help_articles
            SET views = views + 1
            WHERE id = ?
        ");
        $stmt->execute([$article['id']]);

        // Get related articles
        $stmt = $db->prepare("
            SELECT id, title, slug, excerpt
            FROM help_articles
            WHERE tenant_id = ? AND category = ? AND id != ? AND is_published = 1
            ORDER BY views DESC
            LIMIT 5
        ");
        $stmt->execute([$tenantId, $article['category'], $article['id']]);
        $relatedArticles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('help/article', [
            'article' => $article,
            'relatedArticles' => $relatedArticles,
            'pageTitle' => $article['title']
        ]);
    }

    /**
     * Show FAQ page
     */
    public function faq()
    {
        $faqs = [
            [
                'category' => 'Getting Started',
                'questions' => [
                    [
                        'question' => 'How do I create my first product?',
                        'answer' => 'Go to Store → Products → Add New Product. Fill in the product details including name, price, and description. You can also add images and set inventory levels.'
                    ],
                    [
                        'question' => 'How do I process a sale?',
                        'answer' => 'Navigate to Store → POS. Search for products or scan barcodes, add them to the cart, and process payment. You can accept cash, credit cards, or other payment methods.'
                    ],
                    [
                        'question' => 'How do I add staff members?',
                        'answer' => 'Go to Store → Admin → Users → Add New User. Enter their information, assign a role (Manager, Instructor, Sales, etc.), and they will receive login credentials.'
                    ]
                ]
            ],
            [
                'category' => 'Courses & Training',
                'questions' => [
                    [
                        'question' => 'How do I create a dive course?',
                        'answer' => 'Navigate to Store → Courses → Add Course. Enter course details like name, price, duration, and prerequisites. Then create schedules for specific dates and times.'
                    ],
                    [
                        'question' => 'How do I enroll students in a course?',
                        'answer' => 'Go to the course schedule and click "Enroll Student". Search for the customer or create a new customer profile, then complete the enrollment.'
                    ],
                    [
                        'question' => 'How do I track student certifications?',
                        'answer' => 'Student certifications are automatically tracked when you mark a course as completed. View them in the customer profile under the Certifications tab.'
                    ]
                ]
            ],
            [
                'category' => 'Inventory Management',
                'questions' => [
                    [
                        'question' => 'How do I track low stock items?',
                        'answer' => 'Go to Store → Reports → Low Stock. This shows all products below their reorder point. You can set reorder points when editing each product.'
                    ],
                    [
                        'question' => 'How do I adjust inventory levels?',
                        'answer' => 'In Store → Products, click on a product and use the "Adjust Stock" button. Enter the quantity adjustment and reason (received, damaged, sold, etc.).'
                    ],
                    [
                        'question' => 'Can I track serial numbers?',
                        'answer' => 'Yes! Go to Store → Serial Numbers to add and track serial numbers for equipment. You can link serial numbers to sales, rentals, and service records.'
                    ]
                ]
            ],
            [
                'category' => 'Rentals & Equipment',
                'questions' => [
                    [
                        'question' => 'How do I rent equipment to customers?',
                        'answer' => 'Navigate to Store → Rentals → Create Reservation. Select the customer, choose equipment, set rental dates, and process payment.'
                    ],
                    [
                        'question' => 'How do I track equipment maintenance?',
                        'answer' => 'Go to Store → Maintenance. You can schedule maintenance, record service history, and get alerts when equipment is due for inspection.'
                    ]
                ]
            ],
            [
                'category' => 'Payments & Finances',
                'questions' => [
                    [
                        'question' => 'What payment methods are supported?',
                        'answer' => 'Nautilus supports cash, credit cards (via Stripe/Square/PayPal), checks, and store credit. Configure payment gateways in Store → Admin → Settings → Payment.'
                    ],
                    [
                        'question' => 'How do I generate financial reports?',
                        'answer' => 'Go to Store → Reports to access sales reports, payment reports, product performance, and customer analytics. All reports can be exported to CSV or PDF.'
                    ],
                    [
                        'question' => 'Can I export to accounting software?',
                        'answer' => 'Yes! Navigate to Store → Integrations → QuickBooks or Wave Apps to export transactions to your accounting software.'
                    ]
                ]
            ],
            [
                'category' => 'Customer Portal',
                'questions' => [
                    [
                        'question' => 'How do customers access their portal?',
                        'answer' => 'Customers can register at yourwebsite.com/account/register or you can create accounts for them. They can then login to view orders, certifications, and rental history.'
                    ],
                    [
                        'question' => 'What can customers see in their portal?',
                        'answer' => 'Customers can view order history, download invoices, see course enrollments, track certifications, view rental history, and update their profile information.'
                    ]
                ]
            ],
            [
                'category' => 'Support & Troubleshooting',
                'questions' => [
                    [
                        'question' => 'How do I get technical support?',
                        'answer' => 'You can submit feedback at /feedback/create, email support@nautilus.com, or check our documentation in the /docs folder.'
                    ],
                    [
                        'question' => 'How do I backup my data?',
                        'answer' => 'Go to Store → Admin → Backups. You can create manual backups or schedule automatic daily/weekly backups. Always keep backups in a secure off-site location.'
                    ],
                    [
                        'question' => 'Can I customize the storefront colors and logo?',
                        'answer' => 'Yes! Go to Store → Storefront Settings to upload your logo, set brand colors, and customize the appearance of your online store.'
                    ]
                ]
            ]
        ];

        $this->view('help/faq', [
            'faqs' => $faqs,
            'pageTitle' => 'Frequently Asked Questions'
        ]);
    }

    /**
     * Search help articles
     */
    public function search()
    {
        $query = $_GET['q'] ?? '';

        if (empty($query)) {
            redirect('/help');
            return;
        }

        $db = Database::getInstance()->getConnection();
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        $searchTerm = "%{$query}%";

        $stmt = $db->prepare("
            SELECT id, title, slug, category, excerpt, views
            FROM help_articles
            WHERE tenant_id = ? AND is_published = 1
            AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ?)
            ORDER BY views DESC
            LIMIT 20
        ");
        $stmt->execute([$tenantId, $searchTerm, $searchTerm, $searchTerm]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('help/search', [
            'query' => $query,
            'results' => $results,
            'pageTitle' => 'Search Results for: ' . htmlspecialchars($query)
        ]);
    }

    /**
     * Contact support form
     */
    public function contact()
    {
        $this->view('help/contact', [
            'pageTitle' => 'Contact Support'
        ]);
    }

    /**
     * Submit support ticket
     */
    public function submitTicket()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/help/contact');
            return;
        }

        // Redirect to feedback system
        $_SESSION['feedback_data'] = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'subject' => $_POST['subject'] ?? '',
            'message' => $_POST['message'] ?? '',
            'category' => 'support'
        ];

        redirect('/feedback/create');
    }

    /**
     * Admin: Manage help articles
     */
    public function admin()
    {
        $this->requireAuth();
        $this->requirePermission('admin.settings');

        $db = Database::getInstance()->getConnection();
        $tenantId = $_SESSION['tenant_id'];

        $stmt = $db->prepare("
            SELECT * FROM help_articles
            WHERE tenant_id = ?
            ORDER BY category, title
        ");
        $stmt->execute([$tenantId]);
        $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('admin/help/index', [
            'articles' => $articles,
            'pageTitle' => 'Manage Help Articles'
        ]);
    }
}
