<?php
// Bootstrap app and guards
include 'config/connection.php';
include_once __DIR__ . '/includes/auth.php';
require_login();
require_roles(['student','customer']);

// Page meta and assets
$pageTitle = 'Invoice';
$bodyClass = 'student-page invoice-page';
$extraHead = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">';
include 'includes/header.php';

// Validate and read order id
$oid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($oid <= 0) {
        echo "<script>alert('Invalid order'); window.location.href='orders.php';</script>";
        include 'includes/scripts.php';
        exit;
}

$uid = $_SESSION['user']['id'];

// Fetch order and ensure it belongs to current user
$order_sql = "SELECT o.id, o.amt, o.status, o.date, u.firstname, u.lastname
                            FROM `order` o JOIN users u ON o.uid = u.id
                            WHERE o.id = ? AND o.uid = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $order_sql);
mysqli_stmt_bind_param($stmt, 'ii', $oid, $uid);
mysqli_stmt_execute($stmt);
$order_res = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($order_res);
if (!$order) {
        echo "<script>alert('Order not found'); window.location.href='orders.php';</script>";
        include 'includes/scripts.php';
        exit;
}

// Fetch order items
$items_sql = "SELECT i.name, i.image, oi.quantity, oi.price, (oi.quantity * oi.price) as total
                            FROM order_items oi JOIN items i ON oi.product_id = i.id
                            WHERE oi.order_id = ?";
$stmt2 = mysqli_prepare($conn, $items_sql);
mysqli_stmt_bind_param($stmt2, 'i', $oid);
mysqli_stmt_execute($stmt2);
$items_res = mysqli_stmt_get_result($stmt2);
$items = [];
while ($r = mysqli_fetch_assoc($items_res)) { $items[] = $r; }

// Totals
$subtotal = 0.0;
foreach ($items as $it) { $subtotal += (float)$it['total']; }
$gst = $subtotal * 0.05; // 5% GST
$grand = $subtotal + $gst;
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Invoice</h3>
                <div class="d-flex gap-2">
                    <button id="printInvoice" class="btn btn-secondary btn-print"><i class="fa fa-print me-1"></i> Print</button>
                    <button id="downloadInvoice" class="btn btn-success btn-download"><i class="fa fa-file-pdf me-1"></i> Download PDF</button>
                </div>
            </div>

            <div class="invoice-container" id="invoiceContainer">
                <div class="invoice-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">The Hunger Bar Café</h4>
                            <div class="text-muted">Order #<?php echo $order['id']; ?> · <?php echo date('d M Y, h:i A', strtotime($order['date'])); ?></div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div><strong>Invoice No:</strong> INV-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></div>
                            <div><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Billed To</h6>
                        <div><?php echo htmlspecialchars($order['firstname'] . ' ' . $order['lastname']); ?></div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6>Payment Date</h6>
                        <div id="paymentDate">—</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered invoice-table align-middle">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-center" style="width:120px;">Qty</th>
                                <th class="text-end" style="width:140px;">Price</th>
                                <th class="text-end" style="width:160px;">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $it): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($it['name']); ?></td>
                                    <td class="text-center"><?php echo (int)$it['quantity']; ?></td>
                                    <td class="text-end">₹<?php echo number_format((float)$it['price'], 2); ?></td>
                                    <td class="text-end">₹<?php echo number_format((float)$it['total'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6"></div>
                    <div class="col-md-6">
                        <table class="table table-sm invoice-total">
                            <tr>
                                <td>Subtotal</td>
                                <td class="text-end">₹<span id="invoiceSubtotal"><?php echo number_format($subtotal, 2); ?></span></td>
                            </tr>
                            <tr>
                                <td>GST (5%)</td>
                                <td class="text-end">₹<span id="invoiceGst"><?php echo number_format($gst, 2); ?></span></td>
                            </tr>
                            <tr>
                                <th>Total</th>
                                <th class="text-end">₹<span id="invoiceTotal"><?php echo number_format($grand, 2); ?></span></th>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="text-center text-muted">Thank you for your order!</div>
            </div>
        </div>
    </div>
</div>

<!-- Use compatible versions of jsPDF and html2canvas -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set payment date (today)
        const now = new Date();
        const pad = n => String(n).padStart(2, '0');
        const fmt = `${pad(now.getDate())}/${pad(now.getMonth()+1)}/${now.getFullYear()}`;
        const paymentDate = document.getElementById('paymentDate');
        if (paymentDate) paymentDate.textContent = fmt;

        // Print button
        const printBtn = document.getElementById('printInvoice');
        if (printBtn) printBtn.addEventListener('click', () => window.print());

        // PDF generation helper - Simplified version
        function renderPdf(openInNewTab = false) {
            const element = document.getElementById('invoiceContainer');
            
            // Simple html2canvas configuration
            html2canvas(element, {
                scale: 2,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                const imgData = canvas.toDataURL('image/jpeg', 1.0);
                
                // Calculate PDF dimensions
                const imgWidth = 210; // A4 width in mm
                const pageHeight = 295; // A4 height in mm
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                
                let heightLeft = imgHeight;
                let position = 0;
                
                // Create PDF
                const doc = new jsPDF('p', 'mm', 'a4');
                
                // Add first page
                doc.addImage(imgData, 'JPEG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
                
                // Add additional pages if content is too long
                while (heightLeft > 0) {
                    position = heightLeft - imgHeight;
                    doc.addPage();
                    doc.addImage(imgData, 'JPEG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }
                
                if (openInNewTab) {
                    // Open PDF in new tab
                    const pdfBlob = doc.output('blob');
                    const pdfUrl = URL.createObjectURL(pdfBlob);
                    window.open(pdfUrl, '_blank');
                    
                    // Clean up URL after some time
                    setTimeout(() => {
                        URL.revokeObjectURL(pdfUrl);
                    }, 1000);
                } else {
                    // Download PDF
                    doc.save('invoice-<?php echo (int)$order['id']; ?>.pdf');
                }
            }).catch(err => {
                console.error('PDF generation failed:', err);
                alert('Failed to generate PDF. Please try again or use the print function.');
            });
        }

        // Download PDF button
        const dlBtn = document.getElementById('downloadInvoice');
        if (dlBtn) dlBtn.addEventListener('click', () => renderPdf(false));

        // Auto action via URL: view=pdf or view=print
        const params = new URLSearchParams(window.location.search);
        const view = params.get('view');
        if (view === 'pdf') {
            renderPdf(true);
        } else if (view === 'print') {
            window.print();
        }
    });
</script>

<?php include 'includes/scripts.php'; ?>