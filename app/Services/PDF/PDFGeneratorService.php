<?php

namespace App\Services\PDF;

use TCPDF;

/**
 * Comprehensive PDF Generation Service
 * Generates PDFs for all Nautilus forms and documents
 */
class PDFGeneratorService
{
    private $uploadDir;
    private $logoPath;

    public function __construct()
    {
        $this->uploadDir = __DIR__ . '/../../../public/uploads/pdfs/';
        $this->logoPath = __DIR__ . '/../../../public/assets/images/logo.png';

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Generate PADI Medical Form PDF
     */
    public function generateMedicalFormPDF(array $formData, array $customer): string
    {
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8');

        // Document settings
        $pdf->SetCreator('Nautilus Dive Shop Management');
        $pdf->SetAuthor($customer['full_name']);
        $pdf->SetTitle('PADI Medical Statement - ' . $customer['full_name']);
        $pdf->SetSubject('PADI Medical Form');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);

        // Add page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 10);

        // Header
        if (file_exists($this->logoPath)) {
            $pdf->Image($this->logoPath, 15, 10, 40);
        }

        $pdf->SetXY(60, 15);
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'PADI Medical Statement', 0, 1, 'L');

        $pdf->SetXY(60, 22);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Form 10346 (Rev. 11/20)', 0, 1, 'L');

        $pdf->Ln(15);

        // Participant Information
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Participant Information', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);

        $html = '<table cellpadding="4" border="1">
            <tr>
                <td width="25%"><strong>Name:</strong></td>
                <td width="75%">' . htmlspecialchars($customer['full_name']) . '</td>
            </tr>
            <tr>
                <td><strong>Date of Birth:</strong></td>
                <td>' . ($customer['date_of_birth'] ?? 'N/A') . '</td>
            </tr>
            <tr>
                <td><strong>Email:</strong></td>
                <td>' . htmlspecialchars($customer['email'] ?? 'N/A') . '</td>
            </tr>
            <tr>
                <td><strong>Phone:</strong></td>
                <td>' . htmlspecialchars($customer['phone'] ?? 'N/A') . '</td>
            </tr>
            <tr>
                <td><strong>Submitted:</strong></td>
                <td>' . date('F j, Y g:i A', strtotime($formData['submitted_at'])) . '</td>
            </tr>
            <tr>
                <td><strong>Expires:</strong></td>
                <td>' . date('F j, Y', strtotime($formData['expiry_date'])) . '</td>
            </tr>
        </table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Ln(8);

        // Medical Questions
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Medical Questionnaire', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 9);

        $pdf->Cell(0, 5, 'Answer all questions honestly. Any "YES" answer requires physician approval before diving.', 0, 1, 'L');
        $pdf->Ln(3);

        $questions = $this->getMedicalQuestions();
        $hasYes = false;

        $html = '<table cellpadding="3" border="1" style="font-size: 9px;">
            <thead>
                <tr style="background-color: #0066cc; color: #ffffff;">
                    <th width="7%" align="center"><strong>#</strong></th>
                    <th width="73%"><strong>Question</strong></th>
                    <th width="10%" align="center"><strong>Answer</strong></th>
                </tr>
            </thead>
            <tbody>';

        foreach ($questions as $index => $question) {
            $questionId = $question['id'];
            $answer = strtoupper($formData[$questionId] ?? 'NO');
            if ($answer === 'YES') {
                $hasYes = true;
            }

            $bgColor = $answer === 'YES' ? '#ffe6e6' : '#e6ffe6';
            $textColor = $answer === 'YES' ? '#cc0000' : '#006600';

            $html .= '<tr>
                <td align="center">' . ($index + 1) . '</td>
                <td>' . htmlspecialchars($question['text']) . '</td>
                <td align="center" style="background-color: ' . $bgColor . '; color: ' . $textColor . ';"><strong>' . $answer . '</strong></td>
            </tr>';
        }

        $html .= '</tbody></table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        // Physician Clearance Section
        if ($hasYes || $formData['requires_physician_clearance']) {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, 'Physician Clearance Required', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 10);

            if ($formData['physician_clearance_obtained']) {
                $pdf->SetTextColor(0, 128, 0);
                $pdf->Cell(0, 6, 'Physician Clearance: OBTAINED', 0, 1, 'L');
                $pdf->SetTextColor(0, 0, 0);

                if (!empty($formData['physician_clearance_date'])) {
                    $pdf->Cell(0, 6, 'Clearance Date: ' . date('F j, Y', strtotime($formData['physician_clearance_date'])), 0, 1, 'L');
                }

                if (!empty($formData['physician_clearance_file'])) {
                    $pdf->Cell(0, 6, 'Clearance Document: ' . $formData['physician_clearance_file'], 0, 1, 'L');
                }
            } else {
                $pdf->SetTextColor(192, 0, 0);
                $pdf->Cell(0, 6, 'Physician Clearance: REQUIRED (Not Yet Obtained)', 0, 1, 'L');
                $pdf->SetTextColor(0, 0, 0);

                $pdf->Ln(4);
                $pdf->MultiCell(0, 5, 'This participant answered "YES" to one or more medical questions and MUST obtain physician clearance before participating in diving activities.', 0, 'L');
            }
        }

        // Signature Section
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Participant Signature', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);

        $pdf->MultiCell(0, 5, 'I confirm that the information provided above is accurate and complete to the best of my knowledge.', 0, 'L');
        $pdf->Ln(4);

        // Signature image
        if (!empty($formData['participant_signature_data'])) {
            // Convert base64 to image
            $signatureData = $formData['participant_signature_data'];
            if (strpos($signatureData, 'data:image') === 0) {
                $signatureData = preg_replace('/^data:image\/\w+;base64,/', '', $signatureData);
            }

            $signatureImage = base64_decode($signatureData);
            $tempFile = $this->uploadDir . 'temp_sig_' . uniqid() . '.png';
            file_put_contents($tempFile, $signatureImage);

            $pdf->Image($tempFile, 15, $pdf->GetY(), 80, 30);

            // Clean up temp file
            unlink($tempFile);

            $pdf->Ln(32);
        } else {
            $pdf->Ln(30);
        }

        $pdf->Cell(80, 5, '______________________________', 0, 0, 'L');
        $pdf->Cell(0, 5, date('F j, Y', strtotime($formData['participant_signature_date'] ?? $formData['submitted_at'])), 0, 1, 'L');
        $pdf->Cell(80, 5, 'Participant Signature', 0, 0, 'L');
        $pdf->Cell(0, 5, 'Date', 0, 1, 'L');

        // Footer
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(128, 128, 128);
        $pdf->MultiCell(0, 4, 'This form is valid for one year from the submission date. Generated by Nautilus Dive Shop Management System.', 0, 'C');

        // Save PDF
        $filename = 'medical_form_' . $customer['id'] . '_' . time() . '.pdf';
        $filepath = $this->uploadDir . $filename;
        $pdf->Output($filepath, 'F');

        return $filename;
    }

    /**
     * Generate Waiver PDF with Signature
     */
    public function generateWaiverPDF(array $waiverData, array $customer): string
    {
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8');

        $pdf->SetCreator('Nautilus Dive Shop Management');
        $pdf->SetAuthor($customer['full_name']);
        $pdf->SetTitle('Liability Release and Assumption of Risk - ' . $customer['full_name']);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);

        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 12, 'LIABILITY RELEASE AND ASSUMPTION OF RISK', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'RECREATIONAL SCUBA DIVING', 0, 1, 'C');
        $pdf->Ln(6);

        $pdf->SetFont('helvetica', '', 10);

        // Waiver content
        $content = $waiverData['content'] ?? $this->getDefaultWaiverContent();
        $pdf->MultiCell(0, 5, $content, 0, 'J');

        $pdf->AddPage();

        // Participant Information
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Participant Information', 0, 1, 'L');

        $html = '<table cellpadding="4" border="1">
            <tr>
                <td width="30%"><strong>Name:</strong></td>
                <td width="70%">' . htmlspecialchars($customer['full_name']) . '</td>
            </tr>
            <tr>
                <td><strong>Date of Birth:</strong></td>
                <td>' . ($customer['date_of_birth'] ?? 'N/A') . '</td>
            </tr>
            <tr>
                <td><strong>Address:</strong></td>
                <td>' . htmlspecialchars($customer['address'] ?? 'N/A') . '</td>
            </tr>
            <tr>
                <td><strong>Emergency Contact:</strong></td>
                <td>' . htmlspecialchars($waiverData['emergency_contact'] ?? 'N/A') . '</td>
            </tr>
            <tr>
                <td><strong>Emergency Phone:</strong></td>
                <td>' . htmlspecialchars($waiverData['emergency_phone'] ?? 'N/A') . '</td>
            </tr>
        </table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Ln(10);

        // Signature
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Participant Acknowledgment', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);

        if (!empty($waiverData['participant_signature_data'])) {
            $signatureData = $waiverData['participant_signature_data'];
            if (strpos($signatureData, 'data:image') === 0) {
                $signatureData = preg_replace('/^data:image\/\w+;base64,/', '', $signatureData);
            }

            $signatureImage = base64_decode($signatureData);
            $tempFile = $this->uploadDir . 'temp_waiver_sig_' . uniqid() . '.png';
            file_put_contents($tempFile, $signatureImage);

            $pdf->Image($tempFile, 15, $pdf->GetY(), 80, 30);
            unlink($tempFile);

            $pdf->Ln(32);
        } else {
            $pdf->Ln(30);
        }

        $pdf->Cell(80, 5, '______________________________', 0, 0, 'L');
        $pdf->Cell(0, 5, date('F j, Y', strtotime($waiverData['signed_at'])), 0, 1, 'L');
        $pdf->Cell(80, 5, 'Participant Signature', 0, 0, 'L');
        $pdf->Cell(0, 5, 'Date', 0, 1, 'L');

        // Parent/Guardian Signature if minor
        if (!empty($waiverData['is_minor']) && $waiverData['is_minor']) {
            $pdf->Ln(10);
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(0, 8, 'Parent/Guardian Signature (Required for Minors)', 0, 1, 'L');

            if (!empty($waiverData['parent_signature_data'])) {
                $signatureData = preg_replace('/^data:image\/\w+;base64,/', '', $waiverData['parent_signature_data']);
                $signatureImage = base64_decode($signatureData);
                $tempFile = $this->uploadDir . 'temp_parent_sig_' . uniqid() . '.png';
                file_put_contents($tempFile, $signatureImage);

                $pdf->Image($tempFile, 15, $pdf->GetY(), 80, 30);
                unlink($tempFile);

                $pdf->Ln(32);
            } else {
                $pdf->Ln(30);
            }

            $pdf->Cell(80, 5, '______________________________', 0, 0, 'L');
            $pdf->Cell(0, 5, date('F j, Y'), 0, 1, 'L');
            $pdf->Cell(80, 5, 'Parent/Guardian Signature', 0, 0, 'L');
            $pdf->Cell(0, 5, 'Date', 0, 1, 'L');
        }

        $filename = 'waiver_' . $customer['id'] . '_' . time() . '.pdf';
        $filepath = $this->uploadDir . $filename;
        $pdf->Output($filepath, 'F');

        return $filename;
    }

    /**
     * Generate PADI Training Completion Form 10234
     */
    public function generateTrainingCompletionPDF(array $completionData, array $student, array $course): string
    {
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8');

        $pdf->SetCreator('Nautilus Dive Shop Management');
        $pdf->SetTitle('PADI Training Record - ' . $student['full_name']);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);

        $pdf->AddPage();

        // Header
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'PADI TRAINING RECORD', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, 'Form 10234 (Rev. 01/22)', 0, 1, 'C');
        $pdf->Ln(8);

        // Student Information
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 7, 'Student Information', 0, 1, 'L');

        $html = '<table cellpadding="4" border="1">
            <tr>
                <td width="30%"><strong>Student Name:</strong></td>
                <td width="70%">' . htmlspecialchars($student['full_name']) . '</td>
            </tr>
            <tr>
                <td><strong>Date of Birth:</strong></td>
                <td>' . ($student['date_of_birth'] ?? 'N/A') . '</td>
            </tr>
            <tr>
                <td><strong>Email:</strong></td>
                <td>' . htmlspecialchars($student['email'] ?? 'N/A') . '</td>
            </tr>
        </table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Ln(8);

        // Course Information
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 7, 'Course Information', 0, 1, 'L');

        $html = '<table cellpadding="4" border="1">
            <tr>
                <td width="30%"><strong>Course:</strong></td>
                <td width="70%">' . htmlspecialchars($course['name']) . '</td>
            </tr>
            <tr>
                <td><strong>Start Date:</strong></td>
                <td>' . date('F j, Y', strtotime($completionData['start_date'])) . '</td>
            </tr>
            <tr>
                <td><strong>Completion Date:</strong></td>
                <td>' . date('F j, Y', strtotime($completionData['completion_date'])) . '</td>
            </tr>
            <tr>
                <td><strong>Certification Number:</strong></td>
                <td>' . htmlspecialchars($completionData['certification_number'] ?? 'Pending') . '</td>
            </tr>
        </table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Ln(8);

        // Skills Completed
        if (!empty($completionData['skills_completed'])) {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 7, 'Skills Completed', 0, 1, 'L');

            $skills = json_decode($completionData['skills_completed'], true);
            $html = '<table cellpadding="3" border="1">
                <thead>
                    <tr style="background-color: #0066cc; color: #ffffff;">
                        <th width="10%"><strong>#</strong></th>
                        <th width="70%"><strong>Skill</strong></th>
                        <th width="20%"><strong>Status</strong></th>
                    </tr>
                </thead>
                <tbody>';

            foreach ($skills as $index => $skill) {
                $html .= '<tr>
                    <td align="center">' . ($index + 1) . '</td>
                    <td>' . htmlspecialchars($skill['name']) . '</td>
                    <td align="center" style="background-color: #e6ffe6; color: #006600;"><strong>PASSED</strong></td>
                </tr>';
            }

            $html .= '</tbody></table>';
            $pdf->writeHTML($html, true, false, true, false, '');
        }

        // Instructor Certification
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 7, 'Instructor Certification', 0, 1, 'L');

        $html = '<table cellpadding="4" border="1">
            <tr>
                <td width="30%"><strong>Instructor Name:</strong></td>
                <td width="70%">' . htmlspecialchars($completionData['instructor_name'] ?? 'N/A') . '</td>
            </tr>
            <tr>
                <td><strong>Instructor Number:</strong></td>
                <td>' . htmlspecialchars($completionData['instructor_number'] ?? 'N/A') . '</td>
            </tr>
            <tr>
                <td><strong>Dive Center:</strong></td>
                <td>' . htmlspecialchars($completionData['dive_center_name'] ?? 'N/A') . '</td>
            </tr>
            <tr>
                <td><strong>Store Number:</strong></td>
                <td>' . htmlspecialchars($completionData['store_number'] ?? 'N/A') . '</td>
            </tr>
        </table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Ln(15);

        // Instructor Signature
        if (!empty($completionData['instructor_signature_data'])) {
            $signatureData = preg_replace('/^data:image\/\w+;base64,/', '', $completionData['instructor_signature_data']);
            $signatureImage = base64_decode($signatureData);
            $tempFile = $this->uploadDir . 'temp_instructor_sig_' . uniqid() . '.png';
            file_put_contents($tempFile, $signatureImage);

            $pdf->Image($tempFile, 15, $pdf->GetY(), 80, 30);
            unlink($tempFile);
            $pdf->Ln(32);
        } else {
            $pdf->Ln(30);
        }

        $pdf->Cell(80, 5, '______________________________', 0, 0, 'L');
        $pdf->Cell(0, 5, date('F j, Y'), 0, 1, 'L');
        $pdf->Cell(80, 5, 'Instructor Signature', 0, 0, 'L');
        $pdf->Cell(0, 5, 'Date', 0, 1, 'L');

        $filename = 'training_completion_' . $student['id'] . '_' . time() . '.pdf';
        $filepath = $this->uploadDir . $filename;
        $pdf->Output($filepath, 'F');

        return $filename;
    }

    /**
     * Get PADI medical questions
     */
    private function getMedicalQuestions(): array
    {
        return [
            ['id' => 'q1_asthma', 'text' => 'Asthma, or wheezing with breathing, or wheezing with exercise?'],
            ['id' => 'q2_heart_disease', 'text' => 'Frequent or severe suffering from hay fever or allergy attacks?'],
            ['id' => 'q3_ear_problems', 'text' => 'Frequent colds, sinusitis or bronchitis?'],
            ['id' => 'q4_sinus_problems', 'text' => 'Any form of lung disease?'],
            ['id' => 'q5_diabetes', 'text' => 'Pneumothorax (collapsed lung)?'],
            ['id' => 'q6_epilepsy', 'text' => 'Other chest disease or chest surgery?'],
            ['id' => 'q7_chest_pain', 'text' => 'Behavioral health, mental or psychological problems?'],
            ['id' => 'q8_behavioral_health', 'text' => 'Epilepsy, seizures, convulsions or take medications to prevent them?'],
            ['id' => 'q9_head_injury', 'text' => 'Recurring complicated migraine headaches?'],
            ['id' => 'q10_high_blood_pressure', 'text' => 'Blackouts or fainting (full/partial loss of consciousness)?'],
            ['id' => 'q11_lung_disease', 'text' => 'Frequent or severe suffering from motion sickness?'],
            ['id' => 'q12_nervous_system', 'text' => 'Dysentery or dehydration requiring medical intervention?'],
            ['id' => 'q13_back_problems', 'text' => 'Any dive accident or decompression sickness?'],
            ['id' => 'q14_hernia', 'text' => 'Inability to perform moderate exercise?'],
            ['id' => 'q15_ulcers', 'text' => 'Head injury with loss of consciousness within the past five years?'],
            ['id' => 'q16_pregnancy', 'text' => 'Persistent neurological injury or disease?'],
            ['id' => 'q17_smoking', 'text' => 'Recurrent back problems?'],
            ['id' => 'q18_age_over_45', 'text' => 'Back or spinal surgery?'],
            ['id' => 'q19_difficulty_exercising', 'text' => 'Diabetes?'],
            ['id' => 'q20_heart_surgery', 'text' => 'Back, arm or leg problems following surgery, injury or fracture?'],
            ['id' => 'q21_dive_accident', 'text' => 'High blood pressure or take medicine to control blood pressure?'],
            ['id' => 'q22_medications', 'text' => 'Heart disease?'],
            ['id' => 'q23_respiratory_problems', 'text' => 'Heart attack?'],
            ['id' => 'q24_behavioral_meds', 'text' => 'Angina, heart surgery or blood vessel surgery?'],
            ['id' => 'q25_motion_sickness', 'text' => 'Sinus surgery?'],
            ['id' => 'q26_dysentery', 'text' => 'Ear disease or surgery, hearing loss or problems with balance?'],
            ['id' => 'q27_dehydration', 'text' => 'Recurrent ear problems?'],
            ['id' => 'q28_family_history', 'text' => 'Bleeding or other blood disorders?'],
            ['id' => 'q29_fainting', 'text' => 'Hernia?'],
            ['id' => 'q30_insomnia', 'text' => 'Ulcers or ulcer surgery?'],
            ['id' => 'q31_menstrual_problems', 'text' => 'A colostomy or ileostomy?'],
            ['id' => 'q32_surgery_recent', 'text' => 'Recreational drug use or treatment for alcoholism within the past five years?'],
            ['id' => 'q33_blood_disorders', 'text' => 'Pregnant, or attempting to become pregnant?'],
            ['id' => 'q34_other_conditions', 'text' => 'Take prescription medications? (except birth control or anti-malarial)']
        ];
    }

    /**
     * Get default waiver content
     */
    private function getDefaultWaiverContent(): string
    {
        return "I understand and agree that scuba diving is a physically strenuous activity and that I will be exerting myself during this scuba diving experience, and that I am required to follow all instructions and directions given by my instructor.\n\n" .
               "I understand that scuba diving has inherent risks, which may result in serious injury or death. I assume all risks associated with participation in this activity.\n\n" .
               "I hereby release, waive, discharge, and covenant not to sue the dive shop, its owners, instructors, employees, and agents from any and all liability for any loss, damage, or injury, including death, that may be sustained by me arising out of or in connection with my participation in scuba diving activities.\n\n" .
               "I certify that I am in good physical health and have no medical conditions that would prevent me from safely participating in scuba diving activities. I have completed a medical questionnaire and, if required, obtained physician clearance.\n\n" .
               "I acknowledge that I have read this entire document, understand its terms, and agree to its provisions freely and voluntarily.";
    }
}
