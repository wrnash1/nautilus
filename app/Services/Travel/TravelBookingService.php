<?php

namespace App\Services\Travel;

use PDO;

/**
 * Travel Booking Service
 * Manage dive travel packages, liveaboards, resorts, and cruises
 */
class TravelBookingService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Search travel packages
     */
    public function searchPackages(array $searchParams): array
    {
        $sql = "
            SELECT
                tp.*,
                td.destination_name,
                td.country,
                td.region,
                dr.resort_name,
                lb.boat_name
            FROM travel_packages tp
            LEFT JOIN travel_destinations td ON tp.destination_id = td.id
            LEFT JOIN dive_resorts dr ON tp.resort_id = dr.id
            LEFT JOIN liveaboard_boats lb ON tp.liveaboard_id = lb.id
            WHERE tp.tenant_id = ?
              AND tp.is_active = TRUE
        ";

        $params = [$searchParams['tenant_id']];

        // Add filters
        if (!empty($searchParams['package_type'])) {
            $sql .= " AND tp.package_type = ?";
            $params[] = $searchParams['package_type'];
        }

        if (!empty($searchParams['destination_id'])) {
            $sql .= " AND tp.destination_id = ?";
            $params[] = $searchParams['destination_id'];
        }

        if (!empty($searchParams['max_price'])) {
            $sql .= " AND tp.price_per_person <= ?";
            $params[] = $searchParams['max_price'];
        }

        if (!empty($searchParams['min_days']) && !empty($searchParams['max_days'])) {
            $sql .= " AND tp.duration_days BETWEEN ? AND ?";
            $params[] = $searchParams['min_days'];
            $params[] = $searchParams['max_days'];
        }

        // Sorting
        $orderBy = $searchParams['sort_by'] ?? 'featured';
        switch ($orderBy) {
            case 'price_low':
                $sql .= " ORDER BY tp.price_per_person ASC";
                break;
            case 'price_high':
                $sql .= " ORDER BY tp.price_per_person DESC";
                break;
            case 'rating':
                $sql .= " ORDER BY tp.average_rating DESC";
                break;
            case 'featured':
            default:
                $sql .= " ORDER BY tp.is_featured DESC, tp.created_at DESC";
                break;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'packages' => $packages,
            'count' => count($packages)
        ];
    }

    /**
     * Get package details
     */
    public function getPackageDetails(int $packageId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                tp.*,
                td.destination_name,
                td.country,
                td.region,
                td.description as destination_description,
                td.avg_water_temp_f,
                td.avg_visibility_ft,
                dr.resort_name,
                dr.amenities as resort_amenities,
                dr.star_rating,
                lb.boat_name,
                lb.boat_type,
                lb.passenger_capacity,
                lb.amenities as boat_amenities
            FROM travel_packages tp
            LEFT JOIN travel_destinations td ON tp.destination_id = td.id
            LEFT JOIN dive_resorts dr ON tp.resort_id = dr.id
            LEFT JOIN liveaboard_boats lb ON tp.liveaboard_id = lb.id
            WHERE tp.id = ?
        ");

        $stmt->execute([$packageId]);
        $package = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$package) {
            return [
                'success' => false,
                'error' => 'Package not found'
            ];
        }

        // Get reviews
        $reviews = $this->getPackageReviews($packageId);

        return [
            'success' => true,
            'package' => $package,
            'reviews' => $reviews['reviews']
        ];
    }

    /**
     * Create travel booking
     */
    public function createBooking(array $bookingData): array
    {
        // Generate unique booking reference
        $bookingReference = $this->generateBookingReference();

        // Get package details for pricing
        $package = $this->getPackageDetails($bookingData['package_id']);
        if (!$package['success']) {
            return $package;
        }

        $packageDetails = $package['package'];

        // Calculate pricing
        $numberOfTravelers = $bookingData['number_of_travelers'];
        $basePrice = $packageDetails['price_per_person'] * $numberOfTravelers;

        // Apply deposit
        $depositPercentage = $packageDetails['deposit_percentage'] ?? 30;
        $depositAmount = $packageDetails['deposit_required'] ?? ($basePrice * ($depositPercentage / 100));

        // Calculate total
        $additionalCharges = $bookingData['additional_charges'] ?? 0;
        $discounts = $bookingData['discounts'] ?? 0;
        $taxesFees = $this->calculateTravelTaxes($basePrice);
        $totalPrice = $basePrice + $additionalCharges - $discounts + $taxesFees;
        $balanceDue = $totalPrice - $depositAmount;

        // Calculate balance due date (typically 60 days before departure)
        $balanceDueDate = date('Y-m-d', strtotime($bookingData['departure_date'] . ' -60 days'));

        // Create booking
        $stmt = $this->db->prepare("
            INSERT INTO travel_bookings (
                tenant_id, booking_reference, package_id, customer_id,
                departure_date, return_date, number_of_travelers,
                primary_traveler, additional_travelers,
                base_price, additional_charges, discounts, taxes_fees, total_price,
                deposit_amount, balance_due, balance_due_date,
                booking_status, payment_status, booked_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', ?)
        ");

        $stmt->execute([
            $bookingData['tenant_id'],
            $bookingReference,
            $bookingData['package_id'],
            $bookingData['customer_id'],
            $bookingData['departure_date'],
            $bookingData['return_date'],
            $numberOfTravelers,
            json_encode($bookingData['primary_traveler']),
            json_encode($bookingData['additional_travelers'] ?? []),
            $basePrice,
            $additionalCharges,
            $discounts,
            $taxesFees,
            $totalPrice,
            $depositAmount,
            $balanceDue,
            $balanceDueDate,
            $bookingData['booked_by'] ?? null
        ]);

        $bookingId = $this->db->lastInsertId();

        // Update package booking count
        $this->db->prepare("
            UPDATE travel_packages
            SET total_bookings = total_bookings + 1
            WHERE id = ?
        ")->execute([$bookingData['package_id']]);

        // Send confirmation email (would integrate with EmailQueueService)
        // $this->sendBookingConfirmation($bookingId);

        return [
            'success' => true,
            'booking_id' => $bookingId,
            'booking_reference' => $bookingReference,
            'total_price' => $totalPrice,
            'deposit_amount' => $depositAmount,
            'balance_due' => $balanceDue,
            'balance_due_date' => $balanceDueDate
        ];
    }

    /**
     * Confirm booking
     */
    public function confirmBooking(int $bookingId): array
    {
        $stmt = $this->db->prepare("
            UPDATE travel_bookings
            SET booking_status = 'confirmed',
                confirmed_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([$bookingId]);

        return [
            'success' => true,
            'message' => 'Booking confirmed'
        ];
    }

    /**
     * Record payment
     */
    public function recordPayment(int $bookingId, float $amount, string $paymentType = 'deposit'): array
    {
        // Get current booking
        $stmt = $this->db->prepare("
            SELECT * FROM travel_bookings WHERE id = ?
        ");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            return [
                'success' => false,
                'error' => 'Booking not found'
            ];
        }

        $newTotalPaid = $booking['total_paid'] + $amount;
        $newBalanceDue = $booking['total_price'] - $newTotalPaid;

        // Determine payment status
        $paymentStatus = 'pending';
        $depositPaid = $booking['deposit_paid'];
        $depositPaidAt = $booking['deposit_paid_at'];

        if ($paymentType === 'deposit' && $amount >= $booking['deposit_amount']) {
            $depositPaid = true;
            $depositPaidAt = date('Y-m-d H:i:s');
            $paymentStatus = 'deposit_paid';
        }

        if ($newTotalPaid >= $booking['total_price']) {
            $paymentStatus = 'paid_in_full';
        }

        // Update booking
        $this->db->prepare("
            UPDATE travel_bookings
            SET total_paid = ?,
                balance_due = ?,
                payment_status = ?,
                deposit_paid = ?,
                deposit_paid_at = ?
            WHERE id = ?
        ")->execute([
            $newTotalPaid,
            $newBalanceDue,
            $paymentStatus,
            $depositPaid,
            $depositPaidAt,
            $bookingId
        ]);

        return [
            'success' => true,
            'payment_recorded' => $amount,
            'total_paid' => $newTotalPaid,
            'balance_due' => $newBalanceDue,
            'payment_status' => $paymentStatus
        ];
    }

    /**
     * Cancel booking
     */
    public function cancelBooking(int $bookingId, string $reason): array
    {
        $stmt = $this->db->prepare("
            UPDATE travel_bookings
            SET booking_status = 'cancelled',
                cancelled_at = NOW(),
                cancellation_reason = ?
            WHERE id = ?
        ");

        $stmt->execute([$reason, $bookingId]);

        return [
            'success' => true,
            'message' => 'Booking cancelled'
        ];
    }

    /**
     * Submit review
     */
    public function submitReview(array $reviewData): array
    {
        $stmt = $this->db->prepare("
            INSERT INTO travel_reviews (
                tenant_id, booking_id, customer_id, review_type,
                package_id, destination_id, resort_id, liveaboard_id,
                overall_rating, dive_sites_rating, accommodation_rating,
                food_rating, staff_rating, value_rating,
                title, review_text, pros, cons,
                travel_date, traveled_with, verified_booking
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $reviewData['tenant_id'],
            $reviewData['booking_id'] ?? null,
            $reviewData['customer_id'],
            $reviewData['review_type'],
            $reviewData['package_id'] ?? null,
            $reviewData['destination_id'] ?? null,
            $reviewData['resort_id'] ?? null,
            $reviewData['liveaboard_id'] ?? null,
            $reviewData['overall_rating'],
            $reviewData['dive_sites_rating'] ?? null,
            $reviewData['accommodation_rating'] ?? null,
            $reviewData['food_rating'] ?? null,
            $reviewData['staff_rating'] ?? null,
            $reviewData['value_rating'] ?? null,
            $reviewData['title'] ?? null,
            $reviewData['review_text'],
            $reviewData['pros'] ?? null,
            $reviewData['cons'] ?? null,
            $reviewData['travel_date'] ?? null,
            $reviewData['traveled_with'] ?? null,
            !empty($reviewData['booking_id'])
        ]);

        // Update average ratings
        if (!empty($reviewData['package_id'])) {
            $this->updatePackageRating($reviewData['package_id']);
        }

        return [
            'success' => true,
            'review_id' => $this->db->lastInsertId()
        ];
    }

    /**
     * Get package reviews
     */
    private function getPackageReviews(int $packageId, int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT
                tr.*,
                c.first_name,
                c.last_name
            FROM travel_reviews tr
            JOIN customers c ON tr.customer_id = c.id
            WHERE tr.package_id = ?
              AND tr.status = 'approved'
            ORDER BY tr.created_at DESC
            LIMIT ?
        ");

        $stmt->execute([$packageId, $limit]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'reviews' => $reviews
        ];
    }

    /**
     * Update package average rating
     */
    private function updatePackageRating(int $packageId): void
    {
        $this->db->prepare("
            UPDATE travel_packages
            SET average_rating = (
                    SELECT AVG(overall_rating)
                    FROM travel_reviews
                    WHERE package_id = ? AND status = 'approved'
                ),
                review_count = (
                    SELECT COUNT(*)
                    FROM travel_reviews
                    WHERE package_id = ? AND status = 'approved'
                )
            WHERE id = ?
        ")->execute([$packageId, $packageId, $packageId]);
    }

    /**
     * Generate unique booking reference
     */
    private function generateBookingReference(): string
    {
        return 'TRV-' . strtoupper(substr(uniqid(), -8));
    }

    /**
     * Calculate travel taxes (simplified)
     */
    private function calculateTravelTaxes(float $basePrice): float
    {
        // Simplified - would use TaxReportingService in production
        $taxRate = 0.08; // 8% travel tax
        return round($basePrice * $taxRate, 2);
    }

    /**
     * Get customer's travel history
     */
    public function getCustomerTravelHistory(int $customerId, int $tenantId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                tb.*,
                tp.package_name,
                tp.package_type,
                td.destination_name,
                td.country
            FROM travel_bookings tb
            JOIN travel_packages tp ON tb.package_id = tp.id
            LEFT JOIN travel_destinations td ON tp.destination_id = td.id
            WHERE tb.customer_id = ?
              AND tb.tenant_id = ?
            ORDER BY tb.departure_date DESC
        ");

        $stmt->execute([$customerId, $tenantId]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'travel_history' => $history,
            'total_trips' => count($history)
        ];
    }

    /**
     * Integrate with PADI Travel
     */
    public function searchPADITravel(array $searchParams): array
    {
        // Get PADI Travel API credentials
        $api = $this->db->prepare("
            SELECT * FROM travel_partner_apis
            WHERE tenant_id = ? AND partner_name = 'padi_travel' AND is_active = TRUE
        ");
        $api->execute([$searchParams['tenant_id']]);
        $padiAPI = $api->fetch(PDO::FETCH_ASSOC);

        if (!$padiAPI) {
            return [
                'success' => false,
                'error' => 'PADI Travel integration not configured'
            ];
        }

        // In production, make actual API call to PADI Travel
        // $response = $this->callPADITravelAPI($padiAPI, $searchParams);

        // Mock response for demonstration
        return [
            'success' => true,
            'padi_packages' => [
                [
                    'id' => 'PADI-12345',
                    'name' => 'Cozumel PADI Adventure',
                    'destination' => 'Cozumel, Mexico',
                    'duration_days' => 7,
                    'price_per_person' => 1899.00,
                    'external_url' => 'https://travel.padi.com/package/12345'
                ]
            ],
            'source' => 'PADI Travel API'
        ];
    }

    /**
     * Get upcoming departures
     */
    public function getUpcomingDepartures(int $tenantId, int $daysAhead = 90): array
    {
        $stmt = $this->db->prepare("
            SELECT
                tb.*,
                tp.package_name,
                tp.package_type,
                td.destination_name,
                c.first_name,
                c.last_name,
                c.email,
                c.phone
            FROM travel_bookings tb
            JOIN travel_packages tp ON tb.package_id = tp.id
            LEFT JOIN travel_destinations td ON tp.destination_id = td.id
            JOIN customers c ON tb.customer_id = c.id
            WHERE tb.tenant_id = ?
              AND tb.booking_status = 'confirmed'
              AND tb.departure_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY tb.departure_date ASC
        ");

        $stmt->execute([$tenantId, $daysAhead]);
        $departures = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'upcoming_departures' => $departures,
            'count' => count($departures)
        ];
    }
}
