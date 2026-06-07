<?php
error_reporting(0);
date_default_timezone_set('America/Sao_Paulo');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include 'auth_check.php'; // valida sessão e define $CURRENT_USER_ID
include 'db.php';

// ─────────────────────────────────────────────
// Busca transações APENAS do usuário logado
// ─────────────────────────────────────────────
$stmtT = $conn->prepare(
    "SELECT codigo, data_reg, descricao, categoria, tipo, valor
     FROM transacoes
     WHERE usuario_id = ?
     ORDER BY data_reg DESC"
);
$stmtT->bind_param('i', $CURRENT_USER_ID);
$stmtT->execute();
$resT = $stmtT->get_result();

$transacoes    = [];
$totalReceitas = 0;
$totalDespesas = 0;

while ($row = $resT->fetch_assoc()) {
    $tipoRaw = strtolower(trim($row['tipo']));
    $tipo    = ($tipoRaw === 'renda' || $tipoRaw === 'receita') ? 'Receita' : 'Despesa';
    $valor   = (float) $row['valor'];

    if ($tipo === 'Receita') $totalReceitas += $valor;
    else                     $totalDespesas += $valor;

    $transacoes[] = [
        'codigo'    => $row['codigo'],
        'data'      => date('d/m/Y', strtotime($row['data_reg'])),
        'descricao' => $row['descricao'],
        'categoria' => $row['categoria'],
        'tipo'      => $tipo,
        'valor'     => $valor,
    ];
}

// ─────────────────────────────────────────────
// Busca metas APENAS do usuário logado
// ─────────────────────────────────────────────
$stmtM = $conn->prepare(
    "SELECT nome_meta, valor_total, valor_guardado, descricao
     FROM metas
     WHERE usuario_id = ?
     ORDER BY id DESC"
);
$stmtM->bind_param('i', $CURRENT_USER_ID);
$stmtM->execute();
$resM = $stmtM->get_result();

$metas = [];
while ($row = $resM->fetch_assoc()) {
    $prog    = $row['valor_total'] > 0
        ? min(round($row['valor_guardado'] / $row['valor_total'] * 100, 1), 100)
        : 0;
    $metas[] = [
        'nome'          => $row['nome_meta'],
        'valorTotal'    => (float) $row['valor_total'],
        'valorGuardado' => (float) $row['valor_guardado'],
        'descricao'     => isset($row['descricao']) ? $row['descricao'] : '',
        'progresso'     => $prog,
    ];
}

$saldo = $totalReceitas - $totalDespesas;

// ─────────────────────────────────────────────
// Roteamento
// ─────────────────────────────────────────────
$formato = isset($_GET['formato']) ? strtolower(trim($_GET['formato'])) : '';

if ($formato === 'excel') {
    exportarExcel($transacoes, $metas, $saldo, $totalReceitas, $totalDespesas);
} elseif ($formato === 'pdf') {
    exportarPDF($transacoes, $metas, $saldo, $totalReceitas, $totalDespesas);
} else {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error'   => 'Use: ?formato=excel  ou  ?formato=pdf',
        'links'   => [
            'excel' => 'exportar.php?formato=excel',
            'pdf'   => 'exportar.php?formato=pdf',
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

// ═════════════════════════════════════════════
// EXCEL — CSV com BOM (abre no Excel)
// ═════════════════════════════════════════════
function exportarExcel($transacoes, $metas, $saldo, $receitas, $despesas)
{
    $arquivo = 'FinanceEasy_' . date('d-m-Y') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $arquivo . '"');
    header('Pragma: no-cache');

    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); // BOM UTF-8 (necessário para Excel)

    fputcsv($out, ['=== RESUMO DO DASHBOARD ==='], ';');
    fputcsv($out, ['Data de exportacao', date('d/m/Y H:i:s')], ';');
    fputcsv($out, ['Saldo Atual',        'R$ ' . number_format($saldo,    2, ',', '.')], ';');
    fputcsv($out, ['Total de Receitas',  'R$ ' . number_format($receitas, 2, ',', '.')], ';');
    fputcsv($out, ['Total de Despesas',  'R$ ' . number_format($despesas, 2, ',', '.')], ';');
    fputcsv($out, ['Total de Transacoes', count($transacoes)], ';');
    fputcsv($out, [''], ';');

    fputcsv($out, ['=== TRANSACOES ==='], ';');
    fputcsv($out, ['Codigo', 'Data', 'Descricao', 'Categoria', 'Tipo', 'Valor (R$)'], ';');
    foreach ($transacoes as $t) {
        fputcsv($out, [
            $t['codigo'],
            $t['data'],
            $t['descricao'],
            $t['categoria'],
            $t['tipo'],
            number_format($t['valor'], 2, ',', '.'),
        ], ';');
    }
    fputcsv($out, [''], ';');

    fputcsv($out, ['=== METAS FINANCEIRAS ==='], ';');
    fputcsv($out, ['Nome', 'Descricao', 'Valor Total (R$)', 'Valor Guardado (R$)', 'Progresso (%)'], ';');
    foreach ($metas as $m) {
        fputcsv($out, [
            $m['nome'],
            $m['descricao'],
            number_format($m['valorTotal'],    2, ',', '.'),
            number_format($m['valorGuardado'], 2, ',', '.'),
            $m['progresso'] . '%',
        ], ';');
    }

    fclose($out);
    exit;
}

// ═════════════════════════════════════════════
// PDF — FPDF (baixe em fpdf.org, pasta fpdf/)
// ═════════════════════════════════════════════
function exportarPDF($transacoes, $metas, $saldo, $receitas, $despesas)
{
    $fpdfPath = __DIR__ . '/fpdf/fpdf.php';

    if (!file_exists($fpdfPath)) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'error'   => 'FPDF nao encontrado em /codigos/fpdf/fpdf.php',
            'solucao' => 'Baixe em https://www.fpdf.org e coloque a pasta fpdf/ dentro de /codigos/'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    require $fpdfPath;

    function u($str) { return utf8_decode($str); }

    class FinancePDF extends FPDF
    {
        function Footer()
        {
            $this->SetY(-12);
            $this->SetFont('Arial', 'I', 8);
            $this->SetTextColor(160, 160, 160);
            $this->Cell(0, 10, 'Finance Easy  |  Pagina ' . $this->PageNo() . '  |  ' . date('d/m/Y H:i'), 0, 0, 'C');
        }
    }

    $pdf = new FinancePDF('P', 'mm', 'A4');
    $pdf->SetMargins(14, 14, 14);
    $pdf->SetAutoPageBreak(true, 18);
    $pdf->AddPage();

    $pdf->SetFillColor(32, 178, 140);
    $pdf->Rect(0, 0, 210, 22, 'F');
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetY(5);
    $pdf->Cell(0, 12, 'Finance Easy - Relatorio Financeiro', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 0, 'Gerado em: ' . date('d/m/Y H:i'), 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(50, 50, 50);
    $pdf->Cell(0, 7, 'Resumo do Dashboard', 0, 1);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line(14, $pdf->GetY(), 196, $pdf->GetY());
    $pdf->Ln(3);

    $yBoxes = $pdf->GetY();
    $boxes  = [
        ['Saldo Atual',     'R$ ' . number_format($saldo,    2, ',', '.'), [32,178,140]],
        ['Total Receitas',  'R$ ' . number_format($receitas, 2, ',', '.'), [32,178,140]],
        ['Total Despesas',  'R$ ' . number_format($despesas, 2, ',', '.'), [210,80,60] ],
        ['Qtd. Transacoes', count($transacoes),                             [80,80,160] ],
    ];
    $bw = 44;

    foreach ($boxes as $i => $b) {
        $x = 14 + ($bw + 2) * $i;
        $pdf->SetFillColor(245, 245, 245);
        $pdf->Rect($x, $yBoxes, $bw, 16, 'F');
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY($x, $yBoxes + 2);
        $pdf->Cell($bw, 4, $b[0], 0, 0, 'C');
        $pdf->SetXY($x, $yBoxes + 8);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor($b[2][0], $b[2][1], $b[2][2]);
        $pdf->Cell($bw, 6, $b[1], 0, 0, 'C');
    }
    $pdf->SetY($yBoxes + 20);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(50, 50, 50);
    $pdf->Cell(0, 7, 'Transacoes', 0, 1);
    $pdf->Line(14, $pdf->GetY(), 196, $pdf->GetY());
    $pdf->Ln(2);

    $colT = [['Data', 22], ['Descricao', 52], ['Categoria', 30], ['Tipo', 24], ['Valor', 28]];

    $pdf->SetFillColor(32, 178, 140);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 8);
    foreach ($colT as $c) {
        $pdf->Cell($c[1], 7, $c[0], 0, 0, 'C', true);
    }
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 8);
    $alt = false;
    foreach ($transacoes as $t) {
        $pdf->SetFillColor(242, 248, 246);
        $isR = ($t['tipo'] === 'Receita');
        $pdf->SetTextColor(60, 60, 60);
        $pdf->Cell(22, 6, u($t['data']),      0, 0, 'C', $alt);
        $pdf->Cell(52, 6, u($t['descricao']), 0, 0, 'L', $alt);
        $pdf->Cell(30, 6, u($t['categoria']), 0, 0, 'C', $alt);
        $pdf->SetTextColor($isR ? 32 : 210, $isR ? 140 : 80, $isR ? 80 : 60);
        $pdf->Cell(24, 6, u($t['tipo']),      0, 0, 'C', $alt);
        $pdf->Cell(28, 6, ($isR ? '+ ' : '- ') . 'R$ ' . number_format($t['valor'], 2, ',', '.'), 0, 0, 'R', $alt);
        $pdf->Ln();
        $alt = !$alt;
        $pdf->SetTextColor(60, 60, 60);
    }

    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(50, 50, 50);
    $pdf->Cell(0, 7, 'Resumo por Categoria', 0, 1);
    $pdf->Line(14, $pdf->GetY(), 196, $pdf->GetY());
    $pdf->Ln(2);

    $porCategoria = array();
    foreach ($transacoes as $t) {
        $cat = $t['categoria'];
        if (!isset($porCategoria[$cat])) {
            $porCategoria[$cat] = array('receitas' => 0, 'despesas' => 0);
        }
        if ($t['tipo'] === 'Receita') {
            $porCategoria[$cat]['receitas'] += $t['valor'];
        } else {
            $porCategoria[$cat]['despesas'] += $t['valor'];
        }
    }

    $colCat = array(array('Categoria', 60), array('Receitas (R$)', 44), array('Despesas (R$)', 44), array('Saldo (R$)', 34));
    $pdf->SetFillColor(32, 178, 140);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 8);
    foreach ($colCat as $c) {
        $pdf->Cell($c[1], 7, $c[0], 0, 0, 'C', true);
    }
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 8);
    $alt = false;
    foreach ($porCategoria as $cat => $vals) {
        $saldoCat = $vals['receitas'] - $vals['despesas'];
        $pdf->SetFillColor(242, 248, 246);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->Cell(60, 6, u($cat), 0, 0, 'L', $alt);
        $pdf->SetTextColor(32, 140, 80);
        $pdf->Cell(44, 6, $vals['receitas'] > 0 ? 'R$ ' . number_format($vals['receitas'], 2, ',', '.') : '-', 0, 0, 'R', $alt);
        $pdf->SetTextColor(210, 80, 60);
        $pdf->Cell(44, 6, $vals['despesas'] > 0 ? 'R$ ' . number_format($vals['despesas'], 2, ',', '.') : '-', 0, 0, 'R', $alt);
        $pdf->SetTextColor($saldoCat >= 0 ? 32 : 210, $saldoCat >= 0 ? 140 : 80, $saldoCat >= 0 ? 80 : 60);
        $pdf->Cell(34, 6, 'R$ ' . number_format($saldoCat, 2, ',', '.'), 0, 0, 'R', $alt);
        $pdf->Ln();
        $alt = !$alt;
    }

    if (!empty($metas)) {
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(50, 50, 50);
        $pdf->Cell(0, 7, 'Metas Financeiras', 0, 1);
        $pdf->Line(14, $pdf->GetY(), 196, $pdf->GetY());
        $pdf->Ln(2);

        $colM = [['Nome', 46], ['Descricao', 56], ['Total', 28], ['Guardado', 28], ['%', 18]];
        $pdf->SetFillColor(32, 178, 140);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 8);
        foreach ($colM as $c) {
            $pdf->Cell($c[1], 7, $c[0], 0, 0, 'C', true);
        }
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 8);
        $alt = false;
        foreach ($metas as $m) {
            $pdf->SetFillColor(242, 248, 246);
            $pdf->SetTextColor(60, 60, 60);
            $pdf->Cell(46, 6, u($m['nome']),                                              0, 0, 'L', $alt);
            $pdf->Cell(56, 6, u($m['descricao']),                                         0, 0, 'L', $alt);
            $pdf->Cell(28, 6, 'R$ ' . number_format($m['valorTotal'],    2, ',', '.'), 0, 0, 'R', $alt);
            $pdf->Cell(28, 6, 'R$ ' . number_format($m['valorGuardado'], 2, ',', '.'), 0, 0, 'R', $alt);
            $pdf->Cell(18, 6, u($m['progresso']) . '%',                                   0, 0, 'C', $alt);
            $pdf->Ln();
            $alt = !$alt;
        }
    }

    $pdf->Output('D', 'FinanceEasy_' . date('d-m-Y') . '.pdf');
    exit;
}
?>
