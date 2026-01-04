<?php

namespace App\Services\Travel;

use App\Core\Logger;

/**
 * Travel Packet PDF Generation Service
 * Generates professional travel packets using TCPDF
 */
class TravelPacketPDFService extends \TCPDF
{
    private Logger $logger;
    private array $packetData;

    public function __construct()
    {
        parent::__construct('P', 'mm', 'LETTER', true, 'UTF-8', false);

        $this->logger = new Logger();

        // Document settings
        $this->SetCreator('Nautilus Dive Shop');
        $this->SetAuthor('Nautilus Dive Shop');
        $this->SetTitle('Travel Packet');

        // Margins
        $this->SetMargins(15, 15, 15);
        $this->SetHeaderMargin(10);
        $this->SetFooterMargin(10);

        // Auto page breaks
        $this->SetAutoPageBreak(TRUE, 15);

        // Font
        $this->SetFont('helvetica', '', 10);
    }

    /**
     * Generate complete travel packet PDF
     */
    public function generatePacket(array $packetData): string
    {
        $this->packetData = $packetData;

        try {
            // Add cover page
            $this->addCoverPage();

            // Add participant roster
            $this->addParticipantRoster();

            // Add individual participant pages
            foreach ($packetData['participants'] as $participant) {
                $this->addParticipantPage($participant);
            }

            // Generate PDF
            $filename = 'travel_packet_' . $packetData['packet_number'] . '.pdf';
            $filepath = BASE_PATH . '/storage/travel_packets/' . $filename;

            // Ensure directory exists
            $dir = dirname($filepath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Save PDF
            $this->Output($filepath, 'F');

            $this->logger->info('Travel packet PDF generated', [
                'packet_number' => $packetData['packet_number'],
                'filepath' => $filepath
            ]);

            return $filepath;

        } catch (\Exception $e) {
            $this->logger->error('PDF generation failed', [
                'packet_number' => $packetData['packet_number'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Custom header
     */
    public function Header()
    {
        // Logo (if available)
        $logoPath = BASE_PATH . '/public/assets/images/logo.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 15, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }

        // Company name
        $this->SetFont('helvetica', 'B', 16);
        $this->SetTextColor(0, 119, 190); // Ocean blue
        $this->SetXY(50, 12);
        $this->Cell(0, 10, $_ENV['APP_NAME'] ?? 'Nautilus Dive Shop', 0, false, 'L', 0, '', 0, false, 'M', 'M');

        // Packet number (if available)
        if (isset($this->packetData['packet_number'])) {
            $this->SetFont('helvetica', '', 10);
            $this->SetTextColor(100, 100, 100);
            $this->SetXY(140, 12);
            $this->Cell(0, 10, 'Packet #' . $this->packetData['packet_number'], 0, false, 'R', 0, '', 0, false, 'M', 'M');
        }

        // Line
        $this->SetLineStyle(array('width' => 0.5, 'color' => array(0, 119, 190)));
        $this->Line(15, 25, 195, 25);

        // Reset text color
        $this->SetTextColor(0, 0, 0);
    }

    /**
     * Custom footer
     */
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(100, 100, 100);

        // Line
        $this->SetLineStyle(array('width' => 0.5, 'color' => array(200, 200, 200)));
        $this->Line(15, $this->GetY() - 2, 195, $this->GetY() - 2);

        // Page number
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

    /**
     * Add cover page
     */
    private function addCoverPage(): void
    {
        $this->AddPage();

        // Title
        $this->SetFont('helvetica', 'B', 24);
        $this->SetTextColor(0, 119, 190);
        $this->Ln(20);
        $this->Cell(0, 15, 'TRAVEL PACKET', 0, 1, 'C');

        // Destination
        $this->SetFont('helvetica', 'B', 18);
        $this->SetTextColor(50, 50, 50);
        $this->Cell(0, 12, $this->packetData['destination']['name'], 0, 1, 'C');

        $this->Ln(10);

        // Travel dates box
        $this->SetFillColor(240, 248, 255);
        $this->SetDrawColor(0, 119, 190);
        $this->SetLineWidth(0.5);

        $this->SetFont('helvetica', 'B', 12);
        $this->SetTextColor(0, 0, 0);

        $boxY = $this->GetY();
        $this->RoundedRect(50, $boxY, 110, 30, 3, '1111', 'DF');

        $this->SetXY(50, $boxY + 5);
        $this->Cell(110, 8, 'Travel Dates', 0, 1, 'C');

        $this->SetFont('helvetica', '', 11);
        $this->SetXY(50, $boxY + 13);
        $this->Cell(110, 6, 'Departure: ' . date('F j, Y', strtotime($this->packetData['travel_dates']['departure'])), 0, 1, 'C');

        $this->SetXY(50, $boxY + 19);
        $this->Cell(110, 6, 'Return: ' . date('F j, Y', strtotime($this->packetData['travel_dates']['return'])), 0, 1, 'C');

        $this->Ln(15);

        // Destination contact info
        if (!empty($this->packetData['destination']['contact'])) {
            $this->SetFont('helvetica', 'B', 12);
            $this->Cell(0, 8, 'Destination Contact Information', 0, 1, 'L');

            $this->SetFont('helvetica', '', 10);
            $this->SetTextColor(60, 60, 60);

            $this->Cell(0, 6, 'Contact: ' . $this->packetData['destination']['contact'], 0, 1, 'L');

            if (!empty($this->packetData['destination']['email'])) {
                $this->Cell(0, 6, 'Email: ' . $this->packetData['destination']['email'], 0, 1, 'L');
            }

            if (!empty($this->packetData['destination']['phone'])) {
                $this->Cell(0, 6, 'Phone: ' . $this->packetData['destination']['phone'], 0, 1, 'L');
            }
        }

        $this->Ln(10);

        // Participant count
        $participantCount = count($this->packetData['participants']);
        $this->SetFont('helvetica', 'B', 12);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 8, 'Total Participants: ' . $participantCount, 0, 1, 'L');

        // Important notice
        $this->Ln(20);
        $this->SetFillColor(255, 250, 205);
        $this->SetDrawColor(255, 193, 7);
        $this->SetLineWidth(1);

        $this->SetFont('helvetica', 'B', 11);
        $this->SetTextColor(139, 69, 0);

        $noticeY = $this->GetY();
        $this->RoundedRect(15, $noticeY, 180, 40, 3, '1111', 'DF');

        $this->SetXY(20, $noticeY + 5);
        $this->Cell(170, 6, 'IMPORTANT NOTICE', 0, 1, 'C');

        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(60, 60, 60);
        $this->SetXY(20, $noticeY + 12);
        $this->MultiCell(170, 5,
            "Please review all participant information carefully. Ensure passport details, medical information, and certifications are accurate. Contact our office immediately if any corrections are needed.\n\nThis packet contains sensitive personal information and should be kept secure.",
            0, 'L', false, 1, '', '', true, 0, false, true, 0, 'T');
    }

    /**
     * Add participant roster
     */
    private function addParticipantRoster(): void
    {
        $this->AddPage();

        // Title
        $this->SetFont('helvetica', 'B', 16);
        $this->SetTextColor(0, 119, 190);
        $this->Cell(0, 10, 'Participant Roster', 0, 1, 'L');
        $this->Ln(5);

        // Table header
        $this->SetFont('helvetica', 'B', 10);
        $this->SetFillColor(0, 119, 190);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(0, 119, 190);

        $this->Cell(10, 8, '#', 1, 0, 'C', true);
        $this->Cell(50, 8, 'Name', 1, 0, 'L', true);
        $this->Cell(40, 8, 'Email', 1, 0, 'L', true);
        $this->Cell(30, 8, 'Phone', 1, 0, 'L', true);
        $this->Cell(50, 8, 'Highest Certification', 1, 1, 'L', true);

        // Table rows
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(245, 245, 245);

        $fill = false;
        $num = 1;

        foreach ($this->packetData['participants'] as $participant) {
            $this->Cell(10, 7, $num++, 1, 0, 'C', $fill);
            $this->Cell(50, 7, $participant['first_name'] . ' ' . $participant['last_name'], 1, 0, 'L', $fill);
            $this->Cell(40, 7, $participant['email'] ?? '', 1, 0, 'L', $fill);
            $this->Cell(30, 7, $participant['phone'] ?? '', 1, 0, 'L', $fill);
            $this->Cell(50, 7, $participant['highest_certification'] ?? 'N/A', 1, 1, 'L', $fill);

            $fill = !$fill;
        }
    }

    /**
     * Add individual participant page
     */
    private function addParticipantPage(array $participant): void
    {
        $this->AddPage();

        // Participant name header
        $this->SetFont('helvetica', 'B', 16);
        $this->SetTextColor(0, 119, 190);
        $this->Cell(0, 10, $participant['first_name'] . ' ' . $participant['last_name'], 0, 1, 'L');
        $this->Ln(3);

        // Photo (if available)
        if (!empty($participant['photo_path']) && file_exists(BASE_PATH . '/' . $participant['photo_path'])) {
            $this->Image(BASE_PATH . '/' . $participant['photo_path'], 150, 35, 40, 50, '', '', '', true, 150, '', false, false, 1, false, false, false);
        }

        // Personal Information
        $this->sectionHeader('Personal Information');
        $this->infoRow('Email', $participant['email'] ?? 'N/A');
        $this->infoRow('Phone', $participant['phone'] ?? 'N/A');
        $this->infoRow('Date of Birth', $participant['birth_date'] ? date('F j, Y', strtotime($participant['birth_date'])) : 'N/A');
        $this->Ln(3);

        // Passport Information (if included)
        if ($participant['include_passport'] && !empty($participant['passport_number'])) {
            $this->sectionHeader('Passport Information');
            $this->infoRow('Passport Number', $participant['passport_number']);
            $this->infoRow('Passport Country', $participant['passport_country'] ?? 'N/A');
            $this->infoRow('Passport Expiry', $participant['passport_expiry'] ? date('F j, Y', strtotime($participant['passport_expiry'])) : 'N/A');
            $this->Ln(3);
        }

        // Medical Information (if included)
        if ($participant['include_medical'] && !empty($participant['medical_info'])) {
            $this->sectionHeader('Medical Information');
            $this->infoRow('Blood Type', $participant['blood_type'] ?? 'N/A');
            $this->infoRow('Allergies', $participant['allergies'] ?? 'None reported');
            $this->infoRow('Medications', $participant['medications'] ?? 'None reported');
            $this->infoRow('Fit to Dive', $participant['fit_to_dive'] ? 'Yes' : 'No');
            $this->Ln(3);
        }

        // Flight Information (if available)
        if (!empty($participant['flight_number'])) {
            $this->sectionHeader('Flight Information');
            $this->infoRow('Arrival Flight', $participant['flight_number']);
            $this->infoRow('Arrival Time', $participant['arrival_time'] ? date('g:i A', strtotime($participant['arrival_time'])) : 'N/A');

            if (!empty($participant['departure_flight'])) {
                $this->infoRow('Departure Flight', $participant['departure_flight']);
                $this->infoRow('Departure Time', $participant['departure_time'] ? date('g:i A', strtotime($participant['departure_time'])) : 'N/A');
            }
            $this->Ln(3);
        }

        // Certifications (if included)
        if ($participant['include_certifications'] && !empty($participant['certifications'])) {
            $this->sectionHeader('Diving Certifications');

            foreach ($participant['certifications'] as $cert) {
                $certText = $cert['certification_name'] . ' (' . $cert['agency_name'] . ')';
                if ($cert['issue_date']) {
                    $certText .= ' - Issued: ' . date('M Y', strtotime($cert['issue_date']));
                }
                $this->infoRow('', $certText);
            }
            $this->Ln(3);
        }

        // Emergency Contact
        if (!empty($participant['emergency_contact_name'])) {
            $this->sectionHeader('Emergency Contact');
            $this->infoRow('Name', $participant['emergency_contact_name']);
            $this->infoRow('Relationship', $participant['emergency_contact_relationship'] ?? 'N/A');
            $this->infoRow('Phone', $participant['emergency_contact_phone'] ?? 'N/A');
            $this->Ln(3);
        }

        // Special Requests
        if (!empty($participant['special_requests'])) {
            $this->sectionHeader('Special Requests');
            $this->SetFont('helvetica', '', 9);
            $this->MultiCell(0, 5, $participant['special_requests'], 0, 'L', false, 1, '', '', true, 0, false, true, 0, 'T');
        }
    }

    /**
     * Helper: Section header
     */
    private function sectionHeader(string $title): void
    {
        $this->SetFont('helvetica', 'B', 12);
        $this->SetTextColor(0, 119, 190);
        $this->Cell(0, 8, $title, 0, 1, 'L');
        $this->SetDrawColor(0, 119, 190);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(3);
    }

    /**
     * Helper: Info row
     */
    private function infoRow(string $label, string $value): void
    {
        $this->SetFont('helvetica', 'B', 9);
        $this->SetTextColor(80, 80, 80);

        if ($label) {
            $this->Cell(50, 6, $label . ':', 0, 0, 'L');
        } else {
            $this->Cell(10, 6, '•', 0, 0, 'L');
        }

        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 6, $value, 0, 1, 'L');
    }
}
