<?php
require_once '../admin/includes/db_connect.php';
require_once '../admin/includes/functions.php';
require_once '../admin/includes/fpdf/fpdf.php';

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get sit-in statistics
$sit_in_stats = $conn->query("SELECT 
    COUNT(*) as total_sit_ins,
    AVG(TIMESTAMPDIFF(MINUTE, time_in, IFNULL(time_out, NOW()))) as avg_duration
    FROM sit_in_records 
    WHERE date BETWEEN '$start_date' AND '$end_date'")->fetch_assoc();

// Get lab usage statistics
$lab_stats = $conn->query("SELECT 
    lab,
    COUNT(*) as total_visits,
    AVG(TIMESTAMPDIFF(MINUTE, time_in, IFNULL(time_out, NOW()))) as avg_duration
    FROM sit_in_records 
    WHERE date BETWEEN '$start_date' AND '$end_date'
    GROUP BY lab
    ORDER BY total_visits DESC");

// Get peak hours
$peak_hours = $conn->query("SELECT 
    HOUR(time_in) as hour,
    COUNT(*) as total_visits
    FROM sit_in_records 
    WHERE date BETWEEN '$start_date' AND '$end_date'
    GROUP BY HOUR(time_in)
    ORDER BY total_visits DESC");

// Fetch per purpose statistics
$purpose_stats = $conn->query("SELECT purpose, COUNT(*) as total FROM sit_in_records WHERE date BETWEEN '$start_date' AND '$end_date' GROUP BY purpose ORDER BY total DESC");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Lab Sit-in Report',0,1,'C');
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,10,"Date Range: $start_date to $end_date",0,1,'C');
$pdf->Ln(5);

// Total Sit-ins and Average Duration
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'Summary',0,1);
$pdf->SetFont('Arial','',12);
$pdf->Cell(60,8,'Total Sit-ins:',0,0);
$pdf->Cell(40,8,$sit_in_stats['total_sit_ins'],0,1);
$pdf->Cell(60,8,'Average Duration:',0,0);
$pdf->Cell(40,8,round($sit_in_stats['avg_duration']/60,1).' hours',0,1);
$pdf->Ln(5);

// Lab Usage Statistics
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'Lab Usage Statistics',0,1);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(40,8,'Lab',1);
$pdf->Cell(40,8,'Total Visits',1);
$pdf->Cell(50,8,'Avg Duration',1);
$pdf->Ln();
$pdf->SetFont('Arial','',11);
while($lab = $lab_stats->fetch_assoc()) {
    $pdf->Cell(40,8,$lab['lab'],1);
    $pdf->Cell(40,8,$lab['total_visits'],1);
    $pdf->Cell(50,8,round($lab['avg_duration']/60,1).' hours',1);
    $pdf->Ln();
}
$pdf->Ln(5);

// Per Purpose Statistics
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'Per Purpose Statistics',0,1);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(60,8,'Purpose',1);
$pdf->Cell(40,8,'Total Sit-ins',1);
$pdf->Ln();
$pdf->SetFont('Arial','',11);
while($purpose = $purpose_stats->fetch_assoc()) {
    $pdf->Cell(60,8,$purpose['purpose'],1);
    $pdf->Cell(40,8,$purpose['total'],1);
    $pdf->Ln();
}
$pdf->Ln(5);

// Peak Hours
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'Peak Hours',0,1);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(40,8,'Hour',1);
$pdf->Cell(40,8,'Total Visits',1);
$pdf->Ln();
$pdf->SetFont('Arial','',11);
while($hour = $peak_hours->fetch_assoc()) {
    $pdf->Cell(40,8,date('h:i A', strtotime($hour['hour'].':00')),1);
    $pdf->Cell(40,8,$hour['total_visits'],1);
    $pdf->Ln();
}

$pdf->Output('D', 'lab_sitin_report.pdf');
exit; 